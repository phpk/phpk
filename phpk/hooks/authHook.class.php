<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * PHPK
 *
 * 系统权限控制钩子，用在控制器初始化之后
 * 
 * $CONFIG['loader']['hooks']     		    = array(
 *           'pre_controller' => 'auth'
 *        );	
 * 
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @todo   可继承可扩展
 * @filesource
*/
class AuthHook extends Base
{
    /**
     * 用户id
     * @var int
     */
    public $uid;
    /**
     * 用户所在的角色id
     * @var int
     */
    public $gid;
    
    /**
     * 系统初始化
     */
    
    
    function __construct(){
        self::getUserId();
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
 
    
    
}