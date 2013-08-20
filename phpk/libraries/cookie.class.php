<?php
/**
 * PHPK
 *
 * Cookie操作类
 * 
* @author		LiuRuiTao xpbb@qq.com
* @version		v1.0
* @copyright	Copyright (c) 2013,phpk.cn
* @filesource
*/
defined('IN_ROOT') || exit('Access Denied');

class Cookie extends Base
{

	/**
	 * cookie前缀
	 * 
	 * @var string
	 */
	private static $prefix = 'rc_';

	/**
	 * cookie有效时间
	 * 
	 * @var string
	 */
	private static $expire = 3600;

	/**
	 * cookie有效路径
	 * 
	 * @var string
	 */
	private static $path = "/";

	/**
	 * cookie有效域名
	 * 
	 * @var string
	 */
	private static $domain = "";

	/**
	 * 设置cookie
	 * 
	 * @param string $name
	 * @param string $val
	 * @param string $expire
	 * @param string $path
	 * @param string $domain
	 * @return void
	 */
	public static function set( $name , $val , $expire = '' , $path = '' , $domain = '' )
	{
		$expire = (empty( $expire )) ? time() + self::$expire : $expire; // cookie时间
		$path = (empty( $path )) ? self::$path : $path; // cookie路径
		$domain = (empty( $domain )) ? self::$domain : $domain; // 主机名称
		if (empty( $domain ))
		{
			setcookie( self::$prefix . $name , $val , $expire , $path );
		}
		else
		{
			setcookie( self::$prefix . $name , $val , $expire , $path , $domain );
		}
		$_COOKIE[self::$prefix . $name] = $val;
	}

	/**
	 * 获取cookie
	 * 
	 * @param string $name
	 * @return string
	 */
	public static function get( $name )
	{
		return $_COOKIE[self::$prefix . $name];
	}

	/**
	 * 删除cookie
	 * 
	 * @param string $name
	 * @param string $path
	 * @return void
	 */
	public static function delete( $name , $path = '/' )
	{
		self::set( $name , '' , time() - 3600 , empty( $path ) ? self::$path : $path );
		$_COOKIE[self::$prefix . $name] = '';
		unset( $_COOKIE[self::$prefix . $name] );
	}

	/**
	 * 清空cookie
	 * 
	 * @return void
	 */
	public static function clear()
	{
		unset( $_COOKIE );
	}

	/**
	 * 判断cookie存在
	 * 
	 * @param string $name
	 * @return boolen
	 */
	public static function is_set( $name )
	{
		return isset( $_COOKIE[self::$prefix . $name] );
	}
}