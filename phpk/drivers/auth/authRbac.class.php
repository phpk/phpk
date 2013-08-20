<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 +------------------------------------------------------------------------------
 * 基于角色的数据库方式验证类
 +------------------------------------------------------------------------------
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 *
 +------------------------------------------------------------------------------
 */
// 配置文件增加设置
// auth.auth_on 是否需要认证
// auth.auth_type 认证类型
// auth.auth_key 认证识别号
// auth.require_auth_module  需要认证模块
// auth.not_auth_module 无需认证模块
// auth.require_auth_action 需要认证操作
// auth.not_auth_action 无需认证操作
// auth.guest_auth_on 是否开启游客验证
// auth.guest_auth_id 默认游客角色id
// auth.auth_gateway 认证网关
// auth.rbac_db_dns  数据库连接DSN
// auth.role_table 角色表名称
// auth.user_table 用户表名称
// auth.access_table 权限表名称
// auth.node_table 节点表名称
/*
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tb_auth_access` (
  `role_id` smallint(6) unsigned NOT NULL,
  `node_id` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) NOT NULL,
  `module` varchar(50) DEFAULT NULL,
  KEY `groupId` (`role_id`),
  KEY `nodeId` (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
//节点表
CREATE TABLE IF NOT EXISTS `tb_auth_node` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `title` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `sort` smallint(6) unsigned DEFAULT NULL,
  `pid` smallint(6) unsigned NOT NULL,
  `level` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `pid` (`pid`),
  KEY `status` (`status`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
//角色表名
CREATE TABLE IF NOT EXISTS `tb_auth_role` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `pid` smallint(6) DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT NULL,
  `remark` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;
//用户表
CREATE TABLE IF NOT EXISTS `tb_auth_user` (
  `role_id` mediumint(9) unsigned DEFAULT NULL,
  `user_id` char(32) DEFAULT NULL,
  KEY `group_id` (`role_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
*/
class AuthRbac extends Base
{
    // 认证方法
    static public function authenticate($map,$model='') {
        if(empty($model)) $model =  C('auth.user_auth_model');
        //使用给定的Map进行认证
        return M($model)->where($map)->find();
    }

    //用于检测用户权限的方法,并保存到Session中
    static function saveAccessList($authId=null) {
        if(null===$authId)   $authId = $_SESSION[C('auth.auth_key')];
        // 如果使用普通权限模式，保存当前用户的访问权限列表
        // 对管理员开放所有权限
        if(C('auth.auth_type') !=2 && !$_SESSION[C('auth.admin_auth_key')] )
            $_SESSION['_ACCESS_LIST']	=	AuthRbac::getAccessList($authId);
        return ;
    }

	// 取得模块的所属记录访问权限列表 返回有权限的记录ID数组
	static function getRecordAccessList($authId=null,$module='') {
        if(null===$authId)   $authId = $_SESSION[C('auth.auth_key')];
        if(empty($module))  $module	=	MODULE_NAME;
        //获取权限访问列表
        $accessList = AuthRbac::getModuleAccessList($authId,$module);
        return $accessList;
	}

    //检查当前操作是否需要认证
    static function checkAccess() {
        //如果项目要求认证，并且当前模块需要认证，则进行权限认证
        if( C('auth.auth_on') ){
			$_module	=	array();
			$_action	=	array();
            if("" != C('auth.require_auth_module')) {
                //需要认证的模块
                $_module['yes'] = explode(',',strtoupper(C('auth.require_auth_module')));
            }else {
                //无需认证的模块
                $_module['no'] = explode(',',strtoupper(C('auth.not_auth_module')));
            }
            //检查当前模块是否需要认证
            if((!empty($_module['no']) && !in_array(strtoupper(APP_CONTROL),$_module['no'])) || (!empty($_module['yes']) && in_array(strtoupper(APP_CONTROL),$_module['yes']))) {
				if("" != C('auth.require_auth_action')) {
					//需要认证的操作
					$_action['yes'] = explode(',',strtoupper(C('auth.require_auth_action')));
				}else {
					//无需认证的操作
					$_action['no'] = explode(',',strtoupper(C('auth.not_auth_action')));
				}
				//检查当前操作是否需要认证
				if((!empty($_action['no']) && !in_array(strtoupper(APP_ACTION),$_action['no'])) || (!empty($_action['yes']) && in_array(strtoupper(APP_ACTION),$_action['yes']))) {
					return true;
				}else {
					return false;
				}
            }else {
                return false;
            }
        }
        return false;
    }

	// 登录检查
	static public function checkLogin() {
        //检查当前操作是否需要认证
        if(AuthRbac::checkAccess()) {
            //检查认证识别号
            if(!$_SESSION[C('auth.auth_key')]) {
                if(C('auth.guest_auth_on')) {
                    // 开启游客授权访问
                    if(!isset($_SESSION['_ACCESS_LIST']))
                        // 保存游客权限
                        AuthRbac::saveAccessList(C('auth.guest_auth_id'));
                }else{
                    // 禁止游客访问跳转到认证网关
                    U(C('auth.auth_gateway'),1);
                }
            }
        }
        return true;
	}

    //权限认证的过滤器方法
    static public function AccessDecision($appName=APP_NAME) {
        //检查是否需要认证
        if(AuthRbac::checkAccess()) {
            //存在认证识别号，则进行进一步的访问决策
            $accessGuid   =   md5($appName.APP_CONTROL.APP_ACTION);
            if(empty($_SESSION[C('auth.admin_auth_key')])) {
                if(C('auth.auth_type')==2) {
                    //加强验证和即时验证模式 更加安全 后台权限修改可以即时生效
                    //通过数据库进行访问检查
                    $accessList = AuthRbac::getAccessList($_SESSION[C('auth.auth_key')]);
                }else {
                    // 如果是管理员或者当前操作已经认证过，无需再次认证
                    if( $_SESSION[$accessGuid]) {
                        return true;
                    }
                    //登录验证模式，比较登录后保存的权限访问列表
                    $accessList = $_SESSION['_ACCESS_LIST'];
                }
                //判断是否为组件化模式，如果是，验证其全模块名
                if(!isset($accessList[strtoupper($appName)][strtoupper($module)][strtoupper(APP_ACTION)])) {
                    $_SESSION[$accessGuid]  =   false;
                    return false;
                }
                else {
                    $_SESSION[$accessGuid]	=	true;
                }
            }else{
                //管理员无需认证
				return true;
			}
        }
        return true;
    }

    /**
     +----------------------------------------------------------
     * 取得当前认证号的所有权限列表
     +----------------------------------------------------------
     * @param integer $authId 用户ID
     +----------------------------------------------------------
     * @access public
     +----------------------------------------------------------
     */
    static public function getAccessList($authId) {
        // Db方式权限数据
        $db     =   Db::getInstance(C('auth.rbac_db_dns'));
        $table = array('role'=>C('auth.role_table'),'user'=>C('auth.user_table'),'access'=>C('auth.access_table'),'node'=>C('auth.node_table'));
        $sql    =   "select node.id,node.name from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=1 and node.status=1";
        $apps =   $db->query($sql);
        $access =  array();
        foreach($apps as $key=>$app) {
            $appId	=	$app['id'];
            $appName	 =	 $app['name'];
            // 读取项目的模块权限
            $access[strtoupper($appName)]   =  array();
            $sql    =   "select node.id,node.name from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=2 and node.pid={$appId} and node.status=1";
            $modules =   $db->query($sql);
            // 判断是否存在公共模块的权限
            $publicAction  = array();
            foreach($modules as $key=>$module) {
                $moduleId	 =	 $module['id'];
                $moduleName = $module['name'];
                if('PUBLIC'== strtoupper($moduleName)) {
                $sql    =   "select node.id,node.name from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=3 and node.pid={$moduleId} and node.status=1";
                    $rs =   $db->query($sql);
                    foreach ($rs as $a){
                        $publicAction[$a['name']]	 =	 $a['id'];
                    }
                    unset($modules[$key]);
                    break;
                }
            }
            // 依次读取模块的操作权限
            foreach($modules as $key=>$module) {
                $moduleId	 =	 $module['id'];
                $moduleName = $module['name'];
                $sql    =   "select node.id,node.name from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ,".
                    $table['node']." as node ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and access.node_id=node.id and node.level=3 and node.pid={$moduleId} and node.status=1";
                $rs =   $db->query($sql);
                $action = array();
                foreach ($rs as $a){
                    $action[$a['name']]	 =	 $a['id'];
                }
                // 和公共模块的操作权限合并
                $action += $publicAction;
                $access[strtoupper($appName)][strtoupper($moduleName)]   =  array_change_key_case($action,CASE_UPPER);
            }
        }
        return $access;
    }

	// 读取模块所属的记录访问权限
	static public function getModuleAccessList($authId,$module) {
        // Db方式
        $db     =   Db::getInstance(C('auth.rbac_db_dns'));
        $table = array('role'=>C('auth.role_table'),'user'=>C('auth.user_table'),'access'=>C('auth.access_table'));
        $sql    =   "select access.node_id from ".
                    $table['role']." as role,".
                    $table['user']." as user,".
                    $table['access']." as access ".
                    "where user.user_id='{$authId}' and user.role_id=role.id and ( access.role_id=role.id  or (access.role_id=role.pid and role.pid!=0 ) ) and role.status=1 and  access.module='{$module}' and access.status=1";
        $rs =   $db->query($sql);
        $access	=	array();
        foreach ($rs as $node){
            $access[]	=	$node['node_id'];
        }
		return $access;
	}
}