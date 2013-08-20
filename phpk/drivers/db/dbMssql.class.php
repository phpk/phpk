<?php
/**
 * mssql.class.php
 *
 * mssql数据库驱动
 * 
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 */

defined('IN_ROOT') || exit('Access Denied');

class DbMssql extends Base {

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
    public $Transactions;


    /**
     * 构造函数
     *
     * @access public
     * @param array $params 数据库连接参数,如主机名,数据库用户名,密码等
     * @return boolean
     */
    public function __construct($params = array()) {

        //检测数据库连接参数是否完整
        if (!$params['host'] || !$params['username'] || !$params['password'] || !$params['dbname']) {
            halt('Database Server HostName or UserName or Password or DatabaseName error in the config file!');
        }

        $portDelimiter = (defined('PHP_OS') && substr(PHP_OS, 0, 3) === 'WIN') ? ',' :':';
        $params['host'] = $params['host'] . ($params['port'] ? $portDelimiter . $params['port'] :'');

        @ini_set('mssql.charset', 'UTF-8');
        @ini_set('mssql.textlimit', 2147483647);
        @ini_set('mssql.textsize', 2147483647);

        $this->dbLink = @mssql_connect($params['host'], $params['username'], $params['password']);

        if ($this->dbLink && $params['dbname'] != '') {
            if (!mssql_select_db($params['dbname'], $this->dbLink)) {
                mssql_close($this->dbLink);
                return false;
            }
        }

        if (!$this->dbLink) {
            halt($params['driver'] . ' Server connect fail! <br/>Error Message:' . $this->error());
        }

        return true;
    }

    /**
     * 执行SQL语句
     *
     * SQL语句执行函数
     * @access public
     * @param string $sql SQL语句内容
     * @return mixed
     */
    public function query($sql) {

        //参数分析
        if (!$sql) {
            return false;
        }

        $result = mssql_query($sql, $this->dbLink);

        //日志操作,当调试模式开启时将所执行过的SQL写入SQL跟踪日志文件,便于DBA进行数据库优化.若调试模式关闭,当SQL语句执行错误时写入日志文件
        if (DEBUG === 0) {
            if ($result == false) {
                //获取当前运行的controller及action名称
                $controllerId        = APP_CONTROL;
                $actionId            = APP_ACTION;

                Log::write('[' . $controllerId . '][' . $actionId . '] SQL execute failed :' . $sql . ' Error Message:' . $this->error());
                Controller::showMessage('Database SQL execute failed!');
            }
        } else {
            //获取当前运行的controller及action名称
            $controllerId        = APP_CONTROL;
            $actionId            = APP_ACTION;
            $sqlLogFile          = 'trace/sql_' . date('Y_m_d', $_SERVER['REQUEST_TIME']) . ' . log';

            if ($result == true) {
                Log::write('[' . $controllerId . '][' . $actionId . ']:' . $sql, 'Normal', $sqlLogFile);
            } else {
                halt('[' . $controllerId . '][' . $actionId . '] SQL execute failed :' . $sql . '<br/>Error Message:' . $this->error() . '<br/>Error SQL:' . $sql);
            }
        }

        return $result;
    }

    /**
     * 获取数据库错误描述信息
     *
     * @access public
     * @return string
     */
    public function error() {

        return mssql_get_last_message();
    }

    /**
     * 通过一个SQL语句获取一行信息(字段型)
     *
     * @access public
     * @param string $sql SQL语句内容
     * @return mixed
     */
    public function fetchRow($sql) {

        //参数分析
        if (!$sql) {
            return false;
        }

        $result = $this->query($sql);

        if (!$result) {
            return false;
        }

        $myrow     = mssql_fetch_assoc($result);
        mssql_free_result($result);

        return $myrow;
    }
    /**
     * 获取单列数据信息（字段型）
     *
     * @param string $sql
     * @return array
     */
    public function fetchColumn( $sql )
    {
        $row = $this->fetchRow($sql);
        if(isset($row[0])){
            return $row[0];
        }
        if(count($row) == 1){
            return current($row);
        }
        return false;
    }
    /**
     * 通过一个SQL语句获取全部信息(字段型)
     *
     * @access public
     * @param string $sql SQL语句
     * @return array
     */
    public function fetchAll($sql) {

        //参数分析
        if (!$sql) {
            return false;
        }

        $result = $this->query($sql);

        if (!$result) {
            return false;
        }

        $myrow      = array();
        while ($row = mssql_fetch_assoc($result)) {
            $myrow[] = $row;
        }
        mssql_free_result($result);

        return $myrow;
    }

    /**
     * 获取insert_id
     *
     * @access public
     * @return int
     */
    public function insertId(){

        $result_id = mssql_query('SELECT SCOPE_IDENTITY()', $this->dbLink);

        if ($result_id) {
            if ($row = mssql_fetch_assoc($result_id)) {
                mssql_free_result($result_id);
                return $row['computed'];
            }
            mssql_free_result($result_id);
        }

        return false;
    }

    /**
     * 开启事务处理
     *
     * @access public
     * @return boolean
     */
    public function startTrans() {

        if($this->Transactions == false){
            if ($this->query('BEGIN TRANSACTION')) {
                $this->Transactions = true;
            }
        }

        return true;
    }

    /**
     * 提交事务处理
     *
     * @access public
     * @return boolean
     */
    public function commit() {

        if ($this->Transactions == true) {
            if ($this->query('COMMIT TRANSACTION')) {
                $this->Transactions = false;
            } else {
                //获取当前运行的controller及action名称
                $controllerId        = APP_CONTROL;
                $actionId            = APP_ACTION;

                if (DEBUG === 1) {
                    halt('[' . $controllerId . '][' . $actionId . '] SQL Commit failed <br/>Error Message:' . $this->error());
                } else {
                    Log::write('[' . $controllerId . '][' . $actionId . '] SQL Commit failed. Error Message:' . $this->error());
                    Controller::showMessage('Database SQL execute failed!');
                }
            }
        }

        return true;
    }

    /**
     * 事务回滚
     *
     * @access public
     * @return boolean
     */
    public function rollback() {

        if ($this->Transactions == true) {
            if ($this->query('ROLLBACK TRANSACTION')) {
                $this->Transactions = false;
            } else {
                //获取当前运行的controller及action名称
                $controllerId        = APP_CONTROL;
                $actionId            = APP_ACTION;

                if (DEBUG === 1) {
                    halt('[' . $controllerId . '][' . $actionId . '] SQL RollBack failed <br/>Error Message:' . $this->error());
                } else {
                    Log::write('[' . $controllerId . '][' . $actionId . '] SQL RollBack failed. Error Message:' . $this->error());
                    Controller::showMessage('Database SQL execute failed!');
                }
            }
        }

        return true;
    }

    /**
     * 转义字符
     *
     * @access public
     * @param string $string 待转义的字符串
     * @return string
     */
    public function escapeString($string = null) {

        //参数分析
        if (is_null($string)) {
            return  false;
        }

        return addslashes($string);
    }

    /**
     * 析构函数
     *
     * @access public
     * @return void
     */
    public function __destruct() {

        if($this->dbLink == true) {
            @mssql_close($this->dbLink);
        }
    }

    /**
     * 单例模式
     *
     * @access public
     * @param array $params 数据库连接参数,如数据库服务器名,用户名,密码等
     * @return object
     */
    public static function getInstance($params) {

        if (!self::$_instance) {
            self::$_instance = new self($params);
        }

        return self::$_instance;
    }
}