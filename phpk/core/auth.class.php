<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * PHPK
 *
 * Auth权限控制类
 * 
* @author		LiuRuiTao xpbb@qq.com
* @version		v1.0
* @copyright	Copyright (c) 2013,phpk.cn
* @todo   可继承可扩展
* @filesource
*/
class Auth extends Base
{
    /**
     * 用户id
     * @var int
     */
    protected $uid;
    /**
     * 用户所在的角色id
     * @var int
     */
    protected $gid;
    /**
     * 默认操作类型
     *
     * @var string $default_type
     */
    protected static $default_type = 'file';
    /**
     * 当前使用权限驱动类型
     *
     * @var string
     */
    protected $type;
    
    /**
     * 系统初始化
     */
    function __construct($type = null){
        if ($type)
        {
            $this->type = $type;
        }
        else
        {
            $this->type = self::$default_type;
        }
        $this->driver = L($this->type,'auth');
        //self::getUserId();
    }
    
    /**
     * 获取用户id
     * @return boolean
     */
    public function getUserId(){
        if(cookie('__phpk_user_id__') && cookie('__phpk_group_id__')){
            $this->uid = cookie('__phpk_user_id__');
            $this->gid = cookie('__phpk_group_id__');
        }
        //elseif(session('__phpk_user_id__')){
        //    $this->uid = cookie('__phpk_user_id__');
        //}
    }
    
    
    /**
     * 权限初始化操作
     * @param unknown_type $className
     */
    public static function init($className,$action,$classContent){
        if(C('auth.auth_on') === 1){
            //self::setAuthCache($className,$classContent);
        }
    }
    /**
     * 用户登录
     * @param int $uid 用户id
     * @param int $gid 用户组id
     */
    public static function loginIn($uid,$gid=1){
        if(isset($uid) && isset($gid)){
            cookie('__phpk_user_id__',$uid);
            cookie('__phpk_group_id__',$gid);
        }
    }
    /**
     * 用户退出
     */
    public static function loginOut(){
        cookie('__phpk_user_id__',null);
        cookie('__phpk_group_id__',null);
    }
    
    
}