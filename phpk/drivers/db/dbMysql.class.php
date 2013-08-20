<?php
/**
 * PHPK
 *
 * mysql数据库驱动
 * 
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 */
defined('IN_ROOT') || exit('Access Denied');
class DbMysql extends Base
{

	/**
	 * 单例模式实例化对象
	 *
	 * @var object
	 */
	public static $_instance;

	/**
	 * 数据库连接ID
	 *
	 * @var object
	 */
	public $dbLink;

	/**
	 * 事务处理开启状态
	 *
	 * @var boolean
	 */
	public $Transactions = false;

	/**
	 * 构造函数
	 * 
	 * @return void
	 */
	public function __construct( $config = array() )
	{
		if (empty( $config['host'] ) || empty( $config['user'] ) || empty( $config['password'] ))
		{
			halt( "数据库连接配置错误,请检查数据库连接配置");
			return false;
		}
		
		if (isset( $config['port'] ) && !empty( $config['port'] ))
		{
			$config['host'] .= ":" . $config['port'];
		}
		//halt($config['host']);
		$this->dbLink = mysql_connect( $config['host'] , $config['user'] , $config['password'] );
		
		if (! $this->dbLink)
		{
			halt( 'Mysql连接失败 错误编号为：' . mysql_errno() . ' 错误信息为：' . mysql_error());
		}
		else
		{
			if (mysql_select_db( $config['database'] , $this->dbLink ))
			{
				mysql_query( 'SET NAMES ' . $config['charset'] );
			}
			else
			{
				halt( 'Mysql数据库连接失败 错误编号为：' . mysql_errno() . ' 错误信息为：' . mysql_error());
			}
		}
		
		debug( '数据库已连接，驱动为' . $config['driver'] );
		
		return true;
	}

	/**
	 * 处理SQL语句
	 *
	 * @param string $sql
	 * @return object
	 */
	public function query( $sql )
	{
		$flag = false;
		
		//判断参数
		if (empty( $sql ))
		{
			halt( 'SQL语句为空' , E_USER_ERROR );
		}
		
		$result = mysql_query( $sql , $this->dbLink );
		
		if (! $result)
		{
			halt( 'SQL查询失败 错误编号为：' . $this->errno() . '错误信息为：' . $this->error());
			$flag = true;
		}
		
		//DEBUG调试信息
		debug( $flag == true ? "<font color='red'>" . $sql . "</font>" : $sql , 2 );
		
		return $result;
	}

	/**
	 * 返回错误信息
	 *
	 * @return string
	 */
	public function error()
	{
		
		return ($this->dbLink) ? mysql_error( $this->dbLink ) : mysql_error();
	}

	/**
	 * 返回错误代码
	 * 
	 * @return int
	 */
	public function errno()
	{
		return ($this->dbLink) ? mysql_errno( $this->dbLink ) : mysql_errno();
	}

	/**
	 * 返回最新插入数据的ID
	 * 
	 * @return int
	 */
	public function insertId()
	{
		return ($id = mysql_insert_id( $this->dbLink )) >= 0 ? $id : mysql_result( $this->query( "SELECT last_insert_id()" ) );
	}

	/**
	 * 获取全部数据信息（字段型）
	 *
	 * @param string $sql
	 * @return array
	 */
	public function fetchAll( $sql )
	{
		//参数判断
		if (empty( $sql ))
		{
			halt( 'SQL语句为空' , E_USER_ERROR );
		}
		
		$result = $this->query( $sql );
		
		if (! $result)
		{
			halt( 'SQL查询失败 错误编号为：' . $this->errno() . '错误信息为：' . $this->error());
			return false;
		}
		
		$rows = array();
		
		while ($row = mysql_fetch_assoc( $result ))
		{
			$rows[] = $row;
		}
		
		mysql_free_result( $result );
		
		return $rows;
	}

	/**
	 * 获取单行数据信息（字段型）
	 *
	 * @param string $sql
	 * @return array
	 */
	public function fetchRow( $sql )
	{
		//参数判断
		if (empty( $sql ))
		{
			halt( 'SQL语句为空' , E_USER_ERROR );
		}
		
		$result = $this->query( $sql );
		
		if (! $result)
		{
			halt( 'SQL查询失败 错误编号为：' . $this->errno() . '错误信息为：' . $this->error());
			return false;
		}
		
		$row = mysql_fetch_assoc( $result );
		
		mysql_free_result( $result );
		
		return $row;
	}

	/**
	 * 获取单列数据信息（字段型）
	 *
	 * @param string $sql
	 * @return array
	 */
	public function fetchColumn( $sql )
	{
		//参数判断
		if (empty( $sql ))
		{
			halt( 'SQL语句为空' , E_USER_ERROR );
		}
		
		$result = $this->query( $sql );
		
		if (! $result)
		{
			halt( 'SQL查询失败 错误编号为：' . $this->errno() . '错误信息为：' . $this->error());
			return false;
		}
		
		$row = mysql_fetch_assoc( $result );
		
		mysql_free_result( $result );
		if(isset($row[0])){
		    return $row[0];
		}
		if(count($row) == 1){
		    //return implode("", $row);
		    return current($row);
		}
		return false;
	}

	/**
	 * 开启事务处理
	 * 
	 * @return boolen
	 */
	public function trans()
	{
		if ($this->Transactions == false)
		{
			mysql_query( "BEGIN" );
			$this->Transactions = true;
			debug( "开启事务处理" );
		}
		
		return true;
	}

	/**
	 * 事务提交
	 *
	 * @return true
	 */
	public function commit()
	{
		if ($this->Transactions == true)
		{
			if ($this->query( 'COMMIT' ))
			{
				debug( "事务处理已提交" );
				$this->Transactions = false;
				debug( "事务处理已结束" );
			}
			else
			
			{
				halt( '事务提交异常 错误编号为：' . $this->errno() . '错误信息为：' . $this->error());
			}
		}
		else
		{
			$this->trans();
			$this->commit();
		}
		
		return true;
	}

	/**
	 * 事务回滚
	 *
	 * @return true
	 */
	public function rollback()
	{
		if ($this->Transactions == true)
		{
			if ($this->query( 'ROLLBACK' ))
			{
				debug( "事务处理已回滚" );
				$this->Transactions = false;
				debug( "事务处理已结束" );
			}
			else
			{
				halt( '事务回滚异常 错误编号为：' . $this->errno() . '错误信息为：' . $this->error());
			}
		}
		else
		{
			$this->trans();
			$this->rollback();
		}
		
		return true;
	}

	/**
	 * 销毁数据库连接
	 * 
	 * @return boolen
	 */
	public function close()
	{
		if ($this->dbLink)
		{
			debug( '关闭数据库连接' );
			mysql_close( $this->dbLink );
		}
		
		return true;
	}

	/**
	 * 析构函数 销毁数据库连接
	 * 
	 * @return boolen
	 */
	public function __destruct()
	{
		$this->close();
	}

	/**
	 * 单例模式
	 *
	 * @param array $params
	 * @return object
	 */
	public static function getInstance( $params )
	{
		if (! self::$_instance)
		{
			self::$_instance = new self( $params );
		}
		
		return self::$_instance;
	}
}