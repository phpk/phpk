<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * PHPK
 *
 * 基于文件的权限认证
 * 
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
*/
class AuthFile extends Base
{
    /**
     * 得到权限列表
     */
    public function getAuthList(){
        $authFile = DATA_PATH . 'auth' . DS . APP_NAME . '_auth.php';
    
    }
    /**
     * 设置权限列表
     * @param string $className
     * @param string $classContent
     * @return array
     */
    static public function setAuthCache($className,$classContent){
        $_systemAuthAllList = F("auth_cache_list");
        if(!$_systemAuthAllList){
            $_systemAuthAllList=array();
        }
        if(isset($_systemAuthAllList[$className])){
            unset($_systemAuthAllList[$className]);
        }
        //取出类名
        $superReg = "/@class(.*)\s*\*/";
        preg_match_all($superReg, $classContent, $super);
        if(isset($super[1][0])){
            $_systemAuthAllList[$className]['name'] = $super[1][0];
        }
        //取出权限
        $regex = "/@#(.*)\s*\*/";
        preg_match_all($regex, $classContent, $matches);
        $classArr = $matches[1];
        if(count($classArr) > 0){
            $authArr = array();
            foreach ($classArr as $v){
                $varr = explode("|", $v);
                $v0 = trim($varr[0]);
                $v1 = trim($varr[1]);
                if(!empty($v1) && !empty($v0)){
                    $authArr[$v0] = $v1;
                }
            }
            $_systemAuthAllList[$className]['val'] = $authArr;
            F("auth_cache_list",$_systemAuthAllList,0);
        }
        //var_dump($matches);
        return $_systemAuthAllList;
    }
}
