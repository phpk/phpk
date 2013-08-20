<?php
/**
 * PHPK
 *
 * mysqli数据库驱动
 * 
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 */
defined('IN_ROOT') || exit('Access Denied');
class DbMysqli extends Base
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
			halt( "数据库连接配置错误" , E_USER_ERROR );
			return false;
		}
		
		$this->dbLink = (isset( $config['port'] ) && !empty( $config['port'] )) ? new mysqli( $config['host'] , $config['user'] , $config['password'] , $config['database'] , $config['port'] ) : new mysqli( $config['host'] , $config['user'] , $config['password'] , $config['database'] );
		
		if (mysqli_connect_errno())
		{
			halt( 'Mysql连接失败 错误编号为：' . mysqli_connect_errno() . ' 错误信息为：' . mysqli_connect_error() , E_USER_WARNING );
		}
		else
		{
			$this->dbLink->query( 'SET NAMES ' . $config['charset'] );
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
		
		$result = $this->dbLink->query( $sql );
		
		if (! $result)
		{
			halt( 'SQL查询失败 错误编号为：' . $this->errno() . '错误信息为：' . $this->error() , E_USER_WARNING );
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
		
		return $this->dbLink->error;
	}

	/**
	 * 返回错误代码
	 * 
	 * @return int
	 */
	public function errno()
	{
		return $this->dbLink->errno;
	}

	/**
	 * 返回最新插入数据的ID
	 * 
	 * @return int
	 */
	public function insertId()
	{
		return ($id = $this->dbLink->insert_id) >= 0 ? $id : $this->query( "SELECT last_insert_id()" )->fetch_row();
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
			halt( 'SQL查询失败 错误编号为：' . $this->errno() . '错误信息为：' . $this->error() , E_USER_WARNING );
			return false;
		}
		
		$rows = array();
		while ($row = $result->fetch_assoc())
		{
			$rows[] = $row;
		}
		
		$result->free();
		
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
			halt( 'SQL查询失败 错误编号为：' . $this->errno() . '错误信息为：' . $this->error() , E_USER_WARNING );
			return false;
		}
		
		$row = $result->fetch_assoc();
		
		$result->free();
		
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
			halt( 'SQL查询失败 错误编号为：' . $this->errno() . '错误信息为：' . $this->error() , E_USER_WARNING );
			return false;
		}
		
		$row = $result->fetch_assoc();
		
		$result->free();
		
		if(isset($row[0])){
		    return $row[0];
		}
		if(count($row) == 1){
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
			$this->dbLink->autocommit( false );
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
			$result = $this->dbLink->commit();
			
			if ($result)
			{
				$this->dbLink->autocommit( true );
				debug( "事务处理已提交" );
				$this->Transactions = false;
				debug( "事务处理已结束" );
			}
			else
			{
				halt( '事务提交异常 错误编号为：' . $this->errno() . '错误信息为：' . $this->error() , E_USER_WARNING );
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
			$result = $this->dbLink->rollback();
			
			if ($result)
			{
				$this->dbLink->autocommit( true );
				debug( "事务处理已回滚" );
				$this->Transactions = false;
				debug( "事务处理已结束" );
			}
			else
			{
				halt( '事务回滚异常 错误编号为：' . $this->errno() . '错误信息为：' . $this->error() , E_USER_WARNING );
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
			$this->dbLink->close();
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
