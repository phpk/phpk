<?php
/**
 * PHPK
 *
 * CSV操作类
 * 
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
*/
defined('IN_ROOT') || exit('Access Denied');

class Csv extends Base
{

	/**
	 * 读取CSV
	 *
	 * @param string $fileName
	 * @param string $delimiter
	 * @return array
	 */
	public function read( $fileName , $delimiter = ',' )
	{
		if (empty( $fileName ))
		{
			trigger_error( 'CSV文件名称为空' , E_USER_WARNING );
			return false;
		}
		
		setlocale( LC_ALL , 'en_US.UTF-8' );
		
		//读取csv文件内容
		$handle = fopen( $fileName , 'r' );
		$csvData = array();
		$row = 0;
		while ($data = fgetcsv( $handle , 1000 , $delimiter ))
		{
			$num = count( $data );
			for ($i = 0; $i < $num; $i ++)
			{
				$csvData[$row][$i] = $data[$i];
			}
			$row ++;
		}
		fclose( $handle );
		
		return $csvData;
	}

	/**
	 * 生成CSV文件 
	 *
	 * @param string $fileName
	 * @param array $data
	 * @return void
	 */
	public function write( $fileName , $data )
	{
		//参数分析
		if (empty( $fileName ))
		{
			trigger_error( 'CSV文件名称为空' , E_USER_WARNING );
			return false;
		}
		
		if (empty( $data ) || ! is_array( $data ))
		{
			trigger_error( 'CSV文件数据不正确' , E_USER_WARNING );
			return false;
		}
		
		//判断文件名称是否含有csv的扩展名
		if (stripos( $fileName , '.csv' ) === false)
		{
			$fileName .= '.csv';
		}
		
		//分析$data内容
		$content = '';
		foreach ($data as $lines)
		{
			if ($lines && is_array( $lines ))
			{
				foreach ($lines as $key => $value)
				{
					if (is_string( $value ))
					{
						$lines[$key] = '"' . $value . '"';
					}
				}
				$content .= implode( "," , $lines ) . "\n";
			}
		}
		
		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header( 'Expires:0' );
		header( 'Pragma:public' );
		header( "Cache-Control: public" );
		header( "Content-type:text/csv" );
		header( "Content-Disposition:attachment;filename=" . $fileName );
		
		echo $content;
	}
}