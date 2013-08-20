<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * PHPK
 *
 * 调试类文件
 * 
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 */
class Debug
{

	/**
	 * 程序所引用文件
	 *
	 * @var array
	 */
	public static $includeFile = array();

	/**
	 * 调试信息
	 *
	 * @var array
	 */
	public static $info = array();

	/**
	 * 程序所使用的SQL语句
	 *
	 * @var array
	 */
	public static $sqls = array();

	/**
	 * 错误信息对应提示
	 *
	 * @var array
	 */
	public static $errMsg = array(
		E_WARNING => '运行时警告',
		E_NOTICE => '运行时提醒',
		E_STRICT => '编码标准化警告',
		E_USER_ERROR => '自定义错误',
		E_USER_WARNING => '自定义警告',
		E_USER_NOTICE => '自定义提醒',
		'Unkown' => '未知错误'
	);

	/**
	 * 程序开始时调用记录时间
	 * 
	 * @return void
	 */
	public static function start()
	{
		G('begin');
	}

	/**
	 * 程序结束时调用记录时间
	 * 
	 * @return void
	 */
	public static function stop()
	{
		G('end');
	}


	/**
	 * 捕捉错误信息
	 * 
	 * @param number $errno
	 * @param string $errstr
	 * @param string $errfile
	 * @param number $errline
	 */
	public static function catcher( $errno , $errstr , $errfile , $errline )
	{
		if (! isset( self::$errMsg[$errno] ))
			$errno = 'Unkown';
		
		$color = 'red';
		
		if ($errno == E_NOTICE || $errno == E_USER_NOTICE)
		{
			$color = "#000088";
		}
		
		$message = '<font color=' . $color . '>';
		$message .= '<b>' . self::$errMsg[$errno] . "</b>[在文件 {$errfile} 中,第 $errline 行]:";
		$message .= $errstr;
		$message .= '</font>';
		self::addMessage( $message );
	}

	/**
	 * 添加错误信息
	 * 
	 * @param string $message 错误信息
	 * @param int $type 错误类型
	 */
	public static function addMessage( $message , $type = 0 )
	{
		//判断是否开启调试
		if (defined("DEBUG") && DEBUG > 0)
		{
			switch ($type)
			{
				case 0:
					self::$info[] = $message;
					break;
				case 1:
					self::$includeFile[] = $message;
					break;
				case 2:
					self::$sqls[] = $message;
					break;
			}
		}
	}

	/**
	 * 输出调试信息
	 * 
	 * @return void
	 */
	public static function output()
	{
		//ob_start();
	    $memory = (MEMORY_LOG === true) ? "内存使用<font color=\"red\">".G('begin','end','m')."</font>K" : "";
		echo '<div style="float:left;clear:both;text-align:left;font-size:11px;color:#888;width:95%;margin:10px;padding:10px;background:#F5F5F5;border:1px dotted #778855;z-index:100">';
		echo '<div style="float:left;width:100%;"><span style="float:left;width:200px;"><b>运行信息</b>( <font color="red">' . G('begin','end',6) . ' </font>秒 '.$memory.' ):</span><span onclick="this.parentNode.parentNode.style.display=\'none\'" style="cursor:pointer;float:right;width:35px;background:#500;border:1px solid #555;color:white">关闭X</span></div><br>';
		echo '<ul style="margin:0px;padding:0 10px 0 10px;list-style:none">';
		if (count( self::$includeFile ) > 0)
		{
			echo '［自动包含］';
			foreach (self::$includeFile as $file)
			{
				echo '<li>&nbsp;&nbsp;&nbsp;&nbsp;' . $file . '</li>';
			}
		}
		if (count( self::$info ) > 0)
		{
			echo '<br>［系统信息］';
			foreach (self::$info as $info)
			{
				echo '<li>&nbsp;&nbsp;&nbsp;&nbsp;' . $info . '</li>';
			}
		}
		
		if (count( self::$sqls ) > 0)
		{
			echo '<br>［SQL语句］';
			foreach (self::$sqls as $sql)
			{
				echo '<li>&nbsp;&nbsp;&nbsp;&nbsp;' . $sql . '</li>';
			}
		}
		echo '</ul>';
		echo '</div>';
		//ob_end_clean();
	}

}
?>