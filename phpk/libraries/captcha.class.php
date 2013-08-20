<?php
/**
 * PHPK
 *
 * 验证码类
 * 
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 */
defined('IN_ROOT') || exit('Access Denied');

class Captcha extends Base
{

	/**
	 * 随机因子
	 * 
	 * @var string
	 */
	private $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ234567890';

	/**
	 * 验证码
	 * 
	 * @var string
	 */
	private $code;

	/**
	 * 验证码长度
	 * 
	 * @var int
	 */
	private $codelen = 4;

	/**
	 * 宽度
	 * 
	 * @var int
	 */
	private $width = 80;

	/**
	 * 高度
	 * 
	 * @var int
	 */
	private $height = 30;

	/**
	 * 图形资源句柄
	 * 
	 * @var object
	 */
	private $img;

	/**
	 * 指定的字体
	 * 
	 * @var string
	 */
	private $font;

	/**
	 * 指定字体大小
	 * 
	 * @var int
	 */
	private $fontsize = 20;

	/**
	 * 指定字体颜色
	 * 
	 * @var string
	 */
	private $fontcolor;

	/**
	 * 构造方法
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->font = PHPK_PATH . 'sources/font/elephant.ttf';
	}

	/**
	 * 生成随机码
	 * 
	 * @return void
	 */
	private function createCode()
	{
		$_len = strlen( $this->charset ) - 1;
		for ($i = 0; $i < $this->codelen; $i ++)
		{
			$this->code .= $this->charset[mt_rand( 0 , $_len )];
		}
	}

	/**
	 * 生成背景
	 * 
	 * @return void
	 */
	private function createBg()
	{
		$this->img = imagecreatetruecolor( $this->width , $this->height );
		$color = imagecolorallocate( $this->img , mt_rand( 157 , 255 ) , mt_rand( 157 , 255 ) , mt_rand( 157 , 255 ) );
		imagefilledrectangle( $this->img , 0 , $this->height , $this->width , 0 , $color );
	}

	/**
	 * 生成文字
	 * 
	 * @return void
	 */
	private function createFont()
	{
		$_x = $this->width / $this->codelen;
		for ($i = 0; $i < $this->codelen; $i ++)
		{
			$this->fontcolor = imagecolorallocate( $this->img , mt_rand( 0 , 156 ) , mt_rand( 0 , 156 ) , mt_rand( 0 , 156 ) );
			imagettftext( $this->img , $this->fontsize , mt_rand( - 30 , 30 ) , $_x * $i + mt_rand( 1 , 5 ) , $this->height / 1.4 , $this->fontcolor , $this->font , $this->code[$i] );
		}
	}

	/**
	 * 生成线条、雪花
	 * 
	 * @return void
	 */
	private function createLine()
	{
		for ($i = 0; $i < 6; $i ++)
		{
			$color = imagecolorallocate( $this->img , mt_rand( 0 , 156 ) , mt_rand( 0 , 156 ) , mt_rand( 0 , 156 ) );
			imageline( $this->img , mt_rand( 0 , $this->width ) , mt_rand( 0 , $this->height ) , mt_rand( 0 , $this->width ) , mt_rand( 0 , $this->height ) , $color );
		}
		for ($i = 0; $i < 100; $i ++)
		{
			$color = imagecolorallocate( $this->img , mt_rand( 200 , 255 ) , mt_rand( 200 , 255 ) , mt_rand( 200 , 255 ) );
			imagestring( $this->img , mt_rand( 1 , 5 ) , mt_rand( 0 , $this->width ) , mt_rand( 0 , $this->height ) , '*' , $color );
		}
	}

	/**
	 * 输出
	 * 
	 * @return void
	 */
	private function outPut()
	{
		header( 'Content-type:image/png' );
		header( "Expires: -1" );
		header( "Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0" );
		header( "Pragma: no-cache" );
		imagepng( $this->img );
		imagedestroy( $this->img );
		debug('生成验证码');
	}

	/**
	 * 对外生成
	 * 
	 * @return void
	 */
	public function doimg()
	{
		$this->createBg();
		$this->createCode();
		$this->createLine();
		$this->createFont();
		$this->outPut();
	}

	/**
	 * 获取验证码
	 * 
	 * @return string
	 */
	public function getCode()
	{
		$code = strtolower( $this->code );
		return $code;
	}
}