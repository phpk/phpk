<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * PHPK
 *
 * 日志操作类
 * 
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 */
class Log extends Base
{

	/**
	 * 写入日志
	 *
	 * @param string $message
	 * @param string $level
	 * @param string $logFileName
	 * @return boolean
	 */
	public static function write( $message , $level = 'Error' , $fileName = null )
	{
		
		//参数分析
		if (! $message)
		{
			return false;
		}
		
		//当日志写入功能关闭时
		if (PHPK_LOG == false)
		{
			return true;
		}
		
		$fileName = self::getLogFile( $fileName );
		
		//判断日志目录
		$logDir = dirname( $fileName );
		if (! is_dir( $logDir ))
		{
			mkdir( $logDir , 0777 , true );
		}
		elseif (! is_writable( $logDir ))
		{
			chmod( $logDir , 0777 );
		}
		
		error_log( date( '[Y-m-d H:i:s]' , time() ) . " " . $level . ": " . $message . " IP: " . getIp() . "\r\n" , 3 , $fileName );
	}

	/**
	 * 显示日志内容
	 *
	 * @param string $fileName
	 * @return void
	 */
	public static function show( $fileName = null )
	{
		
		//参数分析
		$fileName = self::getLogFile( $fileName );
		
		$content = is_file( $fileName ) ? file_get_contents( $fileName ) : '';
		
		$listStrArray = explode( "\r\n" , $content );
		
		unset( $content );
		
		$totalLines = sizeof( $listStrArray );
		
		//输出日志内容
		echo '<table width="85%" border="0" cellpadding="0" cellspacing="1" style="background:#0478CB; font-size:12px; line-height:25px;">';
		
		foreach ($listStrArray as $key => $linesStr)
		{
			
			if ($key == $totalLines - 1)
			{
				continue;
			}
			
			$bgColor = ($key % 2 == 0) ? '#FFFFFF' : '#C6E7FF';
			
			echo '<tr><td height="25" align="left" bgcolor="' . $bgColor . '">&nbsp;' . $linesStr . '</td></tr>';
		}
		
		echo '</table>';
	}

	/**
	 * 获取当前日志文件名
	 *
	 * @param string $fileName
	 * @return string
	 */
	protected static function getLogFile( $fileName = null )
	{
		return LOG_PATH . date( 'Y-m' ) . '/' . (is_null( $fileName ) ? date( 'Y-m-d' ) : $fileName) . '.log';
	}
}
?>