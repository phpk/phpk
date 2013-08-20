<?php
/**
 * PHPK
 *
 * Curl操作类
 * 
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 */
defined('IN_ROOT') || exit('Access Denied');
class Curl extends Base
{

	/**
	 * HTTP头信息
	 * 
	 * @var array
	 */
	private $headers = array();

	/**
	 * UA信息
	 * 
	 * @var string
	 */
	private $user_agent = '';

	/**
	 * 压缩信息 默认Gzip
	 * 
	 * @var string
	 */
	private $compression = 'gzip';

	/**
	 * cookie信息
	 * 
	 * @var boolen
	 */
	private $cookies = false;

	/**
	 * cookie文件
	 * 
	 * @var string
	 */
	private $cookie_file = 'cookies.txt';

	/**
	 * 开启代理模式 默认关闭
	 * 
	 * @var boolen
	 */
	private $isProxy = false;

	/**
	 * 代理IP
	 * 
	 * @var string
	 */
	private $proxy = '199.201.124.49:8080';

	/**
	 * 构造方法  设置基本参数信息
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->headers[] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
		$this->headers[] = 'Connection: Keep-Alive';
		$this->headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
		$this->user_agent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.1 (KHTML, like Gecko) Chrome/21.' . rand( 1 , 10 ) . '.1180.89 Safari/537.1';
	}

	/**
	 * 设置cookie
	 * 
	 * @param string $cookie_file
	 * @return void
	 */
	public function cookie( $cookie_file )
	{
		if (file_exists( $cookie_file ))
		{
			$this->cookie_file = $cookie_file;
		}
		else
		{
			fopen( $cookie_file , 'w' ) or trigger_error( 'The cookie file could not be opened. Make sure this directory has the correct permissions' , E_USER_ERROR );
			$this->cookie_file = $cookie_file;
			fclose( $this->cookie_file );
		}
	}

	/**
	 * GET方式获取数据
	 * 
	 * @param string $url
	 * @return string
	 */
	public function get( $url )
	{
		$process = curl_init( $url );
		curl_setopt( $process , CURLOPT_HTTPHEADER , $this->headers );
		curl_setopt( $process , CURLOPT_HEADER , 0 );
		curl_setopt( $process , CURLOPT_USERAGENT , $this->user_agent );
		if ($this->cookies == TRUE)
		{
			$this->cookie( $this->cookie_file );
			curl_setopt( $process , CURLOPT_COOKIEFILE , $this->cookie_file );
		}
		if ($this->cookies == TRUE)
		{
			$this->cookie( $this->cookie_file );
			curl_setopt( $process , CURLOPT_COOKIEJAR , $this->cookie_file );
		}
		curl_setopt( $process , CURLOPT_ENCODING , $this->compression );
		curl_setopt( $process , CURLOPT_TIMEOUT , 5 );
		if ($this->isProxy)
		{
			curl_setopt( $process , CURLOPT_PROXY , $this->proxy );
		}
		curl_setopt( $process , CURLOPT_RETURNTRANSFER , 1 );
		curl_setopt( $process , CURLOPT_FOLLOWLOCATION , 1 );
		$return = curl_exec( $process );
		curl_close( $process );
		return $return;
	}

	/**
	 * POST方式获取数据
	 * 
	 * @param string $url
	 * @param array $data
	 * @return string
	 */
	public function post( $url , $data )
	{
		$process = curl_init( $url );
		curl_setopt( $process , CURLOPT_HTTPHEADER , $this->headers );
		curl_setopt( $process , CURLOPT_HEADER , 1 );
		curl_setopt( $process , CURLOPT_USERAGENT , $this->user_agent );
		if ($this->cookies == TRUE)
		{
			$this->cookie( $this->cookie_file );
			curl_setopt( $process , CURLOPT_COOKIEFILE , $this->cookie_file );
		}
		if ($this->cookies == TRUE)
		{
			$this->cookie( $this->cookie_file );
			curl_setopt( $process , CURLOPT_COOKIEJAR , $this->cookie_file );
		}
		curl_setopt( $process , CURLOPT_ENCODING , $this->compression );
		curl_setopt( $process , CURLOPT_TIMEOUT , 30 );
		if ($this->isProxy)
		{
			curl_setopt( $process , CURLOPT_PROXY , $this->proxy );
		}
		curl_setopt( $process , CURLOPT_POSTFIELDS , $data );
		curl_setopt( $process , CURLOPT_RETURNTRANSFER , 1 );
		curl_setopt( $process , CURLOPT_FOLLOWLOCATION , 1 );
		curl_setopt( $process , CURLOPT_POST , 1 );
		$return = curl_exec( $process );
		curl_close( $process );
		return $return;
	}
}