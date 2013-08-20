<?php
/**
 * PHPK
 *
 * pdo数据库驱动
 * 
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 */
defined('IN_ROOT') || exit('Access Denied');
class DbPdo extends Base
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
	 * 构造方法 初始化连接数据库
	 *
	 * @param array $config
	 * @return boolen
	 */
	public function __construct( $config = array() )
	{
		if (empty( $config['dsn'] ))
		{
			trigger_error( "数据库连接配置错误" , E_USER_ERROR );
			return false;
		}
		
		$this->dbLink = new PDO( $config['dsn'] , $config['user'] , $config['password'] );
		
		if (! $this->dbLink)
		{
			trigger_error( 'Mysql连接失败 错误编号为：' . $this->errno() . '错误信息为：' . $this->error() , E_USER_WARNING );
		}
		
		if ($config['driver'] == 'pdo_mysql')
		{
			$this->dbLink->setAttribute( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY , true );
			$this->dbLink->setAttribute( PDO::ATTR_EMULATE_PREPARES , true );
			$this->dbLink->exec( 'SET NAMES ' . $config['charset'] );
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
			trigger_error( 'SQL语句为空' , E_USER_ERROR );
		}
		
		$result = $this->dbLink->query( $sql );
		
		if (! $result)
		{
			trigger_error( 'SQL查询失败 错误编号为：' . $this->errno() . '错误信息为：' . $this->error() , E_USER_WARNING );
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
		$errorInfo = $this->dbLink->errorInfo();
		
		return $errorInfo[2];
	}

	/**
	 * 返回错误代码
	 * 
	 * @return int
	 */
	public function errno()
	{
		return $this->dbLink->errorCode();
	}

	/**
	 * 返回最新插入数据的ID
	 * 
	 * @return int
	 */
	public function insertId()
	{
		return $this->dbLink->lastInsertId();
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
			trigger_error( 'SQL语句为空' , E_USER_ERROR );
		}
		
		$result = $this->query( $sql );
		
		if (! $result)
		{
			trigger_error( 'SQL查询失败 错误编号为：' . $this->errno() . '错误信息为：' . $this->error() , E_USER_WARNING );
			return false;
		}
		
		$rows = $result->fetchAll( PDO::FETCH_ASSOC );
		
		unset( $result );
		
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
			trigger_error( 'SQL语句为空' , E_USER_ERROR );
		}
		
		$result = $this->query( $sql );
		
		if (! $result)
		{
			trigger_error( 'SQL查询失败 错误编号为：' . $this->errno() . '错误信息为：' . $this->error() , E_USER_WARNING );
			return false;
		}
		
		$row = $result->fetch( PDO::FETCH_ASSOC );
		
		unset( $result );
		
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
			trigger_error( 'SQL语句为空' , E_USER_ERROR );
		}
		
		$result = $this->query( $sql );
		
		if (! $result)
		{
			trigger_error( 'SQL查询失败 错误编号为：' . $this->errno() . '错误信息为：' . $this->error() , E_USER_WARNING );
			return false;
		}
		
		$column = $result->fetchColumn( PDO::FETCH_ASSOC );
		
		unset( $result );
		
		return $column;
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
			$this->dbLink->beginTransaction();
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
			if ($this->dbLink->commit())
			{
				debug( "事务处理已提交" );
				$this->Transactions = false;
				debug( "事务处理已结束" );
			}
			else
			{
				trigger_error( '事务提交异常 错误编号为：' . $this->errno() . '错误信息为：' . $this->error() , E_USER_WARNING );
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
			if ($this->dbLink->rollBack())
			{
				debug( "事务处理已回滚" );
				$this->Transactions = false;
				debug( "事务处理已结束" );
			}
			else
			{
				trigger_error( '事务回滚异常 错误编号为：' . $this->errno() . '错误信息为：' . $this->error() , E_USER_WARNING );
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
			$this->dbLink = null;
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
