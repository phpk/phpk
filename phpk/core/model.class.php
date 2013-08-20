<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * PHPK
 *
 * 框架主模型
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 *
 */
class Model extends Base
{
    
    /**
     * 数据库连接配置
     *
     * @var array
     */
    protected $_config = array();
    
    /**
     * SQL语句容器 存放SQL语句片段
     *
     * @var array
     */
    protected $_params = array();
    
    /**
     * 主数据库实例化对象
     *
     * @var object
     */
    protected $_master = null;
    
    /**
     * 从数据库实例化对象
     *
     * @var object
     */
    protected $_slave = null;
    
    /**
     * 数据库实例化是否为单例模式
     *
     * @var boolen
     */
    protected $_singleton = false;
    
    /**
     * 数据表前缀
     *
     * @var stirng
     */
    protected $_prefix = null;
    
    /**
     * 构造函数
     *
     * 用于初始化程序运行环境，或对基本变量进行赋值
     *
     * @return void
     */
    public function __construct ()
    {
        // 获取数据库连接参数
        $this->_config = $this->config();
    }
    
    /**
     * 更换表的前缀
     *
     * @param string $sql            
     * @return string
     */
    public function repalceTableName ($sql)
    {
        if (! empty($this->_prefix)) {
            $sql = str_replace(' tb_', ' ' . $this->_prefix, $sql);
        }
        return $sql;
    }
    /**
     * 获取表的名称
     * 
     * @param string $name            
     * @return string
     */
    public function getTableName ($name)
    {
        if (! empty($this->_prefix)) {
            if (false !== strpos($name, 'tb_')) {
                $name = str_replace('tb_', $this->_prefix, $name);
            }
            elseif (false === strpos($name, $this->_prefix)) {
                $name = $this->_prefix . $name;
            }
        }
        return $name;
    }
    /**
     * 查询语句
     *
     * @param string $sql            
     * @param int $sType
     *            0为查询所有，1为查询一行，2为查询一列
     * @param string $cacheName
     *            如果不为空，则缓存数据
     * @return array string
     */
    function select ($sql, $sType = 0, $cacheName = '')
    {
        $sql = $this->repalceTableName($sql);
        if ($sType == 0) {
            $data = $this->slave()->fetchAll($sql);
        }
        elseif ($sType == 1) {
            $data = $this->slave()->fetchRow($sql);
        }
        elseif ($sType == 2) {
            $data = $this->slave()->fetchColumn($sql);
        }
        if ($cacheName !== '') {
            F($data, $cacheName);
        }
        //F('sql',$sql);
        return $data;
    }
    /**
     * 组装LIMIT语句
     *
     * @param int $offset
     * @param int $count
     * @return object
     */
    public function limit( $offset , $count = null )
    {
    
        $offset = intval( $offset );
        $count = intval( $count );
        $limitStr = ($count > 0) ? $offset . ', ' . $count : $offset;
        return ' LIMIT ' . $limitStr;
    }
    /**
     * 获取表的总数
     * @param unknown_type $tableName
     * @param unknown_type $where
     * @return multitype:
     */
    public function count($tableName,$where = null){
        $tableName = $this->getTableName($tableName);
        if (! is_null($where)) {  
            if (is_array($where)) {
                $where = " where " . implode(" AND ", $where);
            }else{
                $where = " where " . $where;
            }
        }
        $sql = "select count(*) as sum from $tableName $where";
        //var_dump($sql);
        return $this->select($sql,2);
    }
    
    /**
     * 分页使用的LIMIT语句
     *
     * @param int $page
     * @param int $count
     * @return object
     */
    public function page( $page , $count )
    {
        $startId = intval( $count ) * (intval( $page ) - 1);
        return $this->limit( $startId , $count );
    }
    /**
     * 插入新数据
     *
     * @example 
     * <code>
     *     //单一插入
     *     $data = array('name'=>'skyboy', 'age'=>23, 'address'=>'山东');
     *     $this->insert('user',$data);
     *     
     *     //批量插入
     *     $data = array('name', 'age', 'address');
     *     $insertId = array(
     *         array('skyboy', '23', 'shandong'),
     *         array('ruitao', '31', 'changsha')
     *     );
     *     $this->insert('user', $data, $insertId);
     *     
     *     //批量插入更新
     *     $data = array('name', 'age', 'address');
     *     $update = array('age', 'address');
     *     $insertId = array(
     *         array('skyboy', '23', 'shandong'),
     *         array('ruitao', '31', 'changsha')
     *     );
     *     $this->insert('user', $data, $insertId, $update);
     *     
     * </code>        
     * @param string $tableName            
     * @param array $data            
     * @param boolen|array $insertId 
     * @param array $update           
     * @return mixed
     */
    public function insert ($tableName, $data, $insertId = true, $update = array())
    {
        // 对函数的参数进行判断
        if (empty($tableName)) {
            halt('数据表不能为空');
        }
        
        if (empty($data) || ! is_array($data)) {
            halt('插入数据不正确');
        }
        
        // 组装SQL语句
        $fieldsArray = array();
        $valuesArray = array();
        $tableName = $this->getTableName($tableName);
        
        if(!is_array($insertId)){
            foreach ($data as $key => $value) {
                $fieldsArray[] = '`' . trim($key) . '`';
                $valuesArray[] = "'" . $this->quote(trim($value)) . "'";
            }
            
            $fieldString = implode(',', $fieldsArray);
            $valueString = implode(',', $valuesArray);
            // dump($this->_prefix);
            
            $sqlString = 'INSERT INTO `' . $tableName . '`(' . $fieldString .
            ') VALUES (' . $valueString . ')';
        }else{
            foreach ($data as $value) {
                $fieldsArray[] = '`' . trim($value) . '`';
            }
            
            $fieldString = implode(',', $fieldsArray);
            $valuesString = '';
            
            foreach ($insertId as $val){
                //数据过滤
                $v = array();
                foreach ($val as $vv){
                    $v[] = "'" . $this->quote(trim($vv)) . "'";
                }
                $valuesArray[] = implode(',', $v);
            }
            
            $valuesString = '(' . implode('),(',$valuesArray).')';
            //批量更新
            if(count($update) > 0){
                $valuesString .= ' ON DUPLICATE KEY UPDATE ';
                foreach ($update as $uv) {
                    $uv = $this->quote(trim($uv));
                    $valuesString .= $uv . '= VALUES('. $uv .'),'; 
                }
                $valuesString = substr($valuesString, 0, -1);
            }
            //var_dump($valuesString);
            // dump($this->_prefix);
            
            $sqlString = 'INSERT INTO `' . $tableName . '`(' . $fieldString .
            ') VALUES ' . $valuesString;
        }
        
        //var_dump($sqlString);
        //exit;
        $result = $this->master()->query($sqlString);
        
        if ($result && $insertId) {
            return $this->getInsertId();
        }
        
        return $result;
    }
    
    /**
     * 更新数据
     *
     * @param string $tableName            
     * @param array $data            
     * @param string|array $where 
     * @param int $upType 0为更新，1为插入或者更新   
     * @see 当$upType等于1时插入数据的表必须有主键或者是唯一索引！    
     * @return object
     */
    public function update ($tableName, $data, $where = null, $upType=0)
    {
        if (empty($tableName)) {
            halt('更新数据数据表名称为空');
        }
        
        if (empty($data)) {
            halt('更新数据为空');
        }
        
        $values = array();
        foreach ($data as $key => $value) {
            $v = $this->quote(trim($value));
            $k = '`' . trim($key) . '`';
            if (false !== strpos($v,$key)) {
                $values[] = $k . '=' . $v;
            }
            else {
                $values[] = $k . '=\'' . $v . '\'';
            }
        }
        $valueStr = implode(',', $values);
        unset($values);
        
        if (! is_null($where)) {
            if (is_array($where)) {
                $where = implode(" AND ", $where);
            }
        }
        
        $tableName = $this->getTableName($tableName);
        if($upType == 0 ){
            $upsql = 'UPDATE ';
        }else{
            $upsql = 'REPLACE INTO ';
        }
        if (! is_null($where)) {
            $sqlStr = $upsql . $tableName . ' SET ' . $valueStr . ' WHERE ' .
                     $where;
        }
        else {
            $sqlStr = $upsql . $tableName . ' SET ' . $valueStr;
        }
        return $this->master()->query($sqlStr);
    }
    
    /**
     * 删除数据
     *
     * @param string $tableName            
     * @param string|array $where            
     * @return object
     */
    public function delete ($tableName, $where = null)
    {
        if (empty($tableName)) {
            halt('删除数据数据表名称为空');
        }
        
        if (! is_null($where)) {
            if (is_array($where)) {
                $where = implode(" AND ", $where);
            }
        }
        
        $tableName = $this->getTableName($tableName);
        
        if (! is_null($where)) {
            $sqlStr = 'DELETE FROM ' . $tableName . ' WHERE ' . $where;
        }
        else {
            $sqlStr = 'DELETE FROM ' . $tableName;
        }
        
        return $this->master()->query($sqlStr);
    }
    
    /**
     * 执行其他类型SQL语句
     *
     * @param string $sql            
     * @param int $sType            
     * @param string $cacheName            
     * @return array | object
     */
    public function execute ($sql, $sType = 3, $cacheName = '')
    {
        if (empty($sql)) {
            halt('SQL为空');
        }
        
        $sql = $this->repalceTableName($sql);
        
        if ($sType < 3) {
            return $this->select($sql, $sType, $cacheName);
        }
        else {
            return $this->master()->query($sql);
        }
    }
    
    /**
     * 获取最新插入的最后ID
     *
     * @return int
     */
    public function getInsertId ()
    {
        return $this->master()->insertId();
    }
    
    /**
     * 字符串的转义 防止SQL注入
     *
     * @return string
     */
    public function quote ($string)
    {
        return addslashes($string);
    }
    
    /**
     * 开启事务
     *
     * @return void
     */
    public function trans ()
    {
        $this->master()->trans();
    }
    
    /**
     * 提交事务
     *
     * @return void
     */
    public function commit ()
    {
        $this->master()->commit();
    }
    
    /**
     * 回滚事务
     *
     * @return void
     */
    public function rollback ()
    {
        $this->master()->rollback();
    }
    
    /**
     * 解析数据库配置
     *
     * 分析数据库主从信息
     *
     * @return array
     */
    protected function config ()
    {
        $params = $this->getConfig();
        // var_dump($params);
        if (! is_array($params)) {
            halt('数据库配置加载错误，配置必须为数组');
        }
        
        // 数据库表前缀
        $this->_prefix = (isset($params['prefix']) && ! empty($params['prefix'])) ? trim(
                $params['prefix']) : '';
        
        // 数据库默认编码 默认编码utf8
        $params['charset'] = (isset($params['charset']) &&
                 ! empty($params['charset'])) ? trim($params['charset']) : 'utf8';
        
        $params['driver'] = isset($params['driver']) ? $params['driver'] : "mysql";
        
        $configs = array();
        
        // 分析主库连接配置
        if (isset($params['master']) && ! empty($params['master'])) {
            $configs['master'] = $params['master'];
            $configs['master']['charset'] = $params['charset'];
            $configs['master']['driver'] = $params['driver'];
        }
        else {
            $configs['master'] = $params;
        }
        
        // 分析从库连接配置
        if (isset($params['slave']) && ! empty($params['slave']) &&
                 is_array($params['slave'])) {
            foreach ($params['slave'] as $slave) {
                $slave['charset'] = $params['charset'];
                $slave['driver'] = $params['driver'];
                
                $configs['slave'][] = $slave;
            }
        }
        else {
            // 当没有从库连接参数时,开启单例模式
            $this->_singleton = true;
            $configs['slave'][] = $configs['master'];
        }
        
        unset($params);
        
        return $configs;
    }
    
    /**
     * 实例化主数据库
     *
     * @return object
     */
    protected function master ()
    {
        if ($this->_master) {
            return $this->_master;
        }
        
        $this->_master = $this->factory($this->_config['master']);
        
        if ($this->_singleton) {
            $this->_slave = $this->_master;
        }
        
        return $this->_master;
    }
    
    /**
     * 实例化从数据库
     *
     * @return object
     */
    public function slave ()
    {
        if ($this->_slave) {
            return $this->_slave;
        }
        
        // 获得从数据库配置的索引
        $length = count($this->_config['slave']);
        $index = $length == 1 ? 0 : array_rand($this->_slave['slave']);
        $this->_slave = $this->factory($this->_config['slave'][$index]);
        if ($this->_singleton) {
            $this->_master = $this->_slave;
        }
        
        return $this->_slave;
    }
    
    /**
     * 获取数据库连接配置
     *
     * @return array
     */
    protected function getConfig ()
    {
        return C('system.db');
    }
    
    /**
     * 工厂模式实例化数据库驱动操作
     *
     * @param array $config            
     * @return object
     */
    public function factory ($config=array())
    {
        if(empty($config)){
            $configs = self::config();
            $config = $configs['master'];
        }
        //var_dump($config);
        $driver = $config['driver'];
        if(strpos($driver,":") !== false){
            $driver = substr(strstr($driver, ':'), 1);
        }
        if ($driver == 'mysql') {
            return factory('DbMysql', $config);
        }
        elseif ($driver == 'mysqli') {
            return factory('DbMysqli', $config);
        }
        elseif ($driver == 'pdo') {
            // 组合dsn信息
            if (! isset($config['dsn'])) {
                $dsnArray = array();
                $dsnArray['host'] = $config['host'];
                $dsnArray['dbname'] = $config['database'];
                
                if (! empty($config['port'])) {
                    $dsnArray['port'] = $config['port'];
                }
                $config['dsn'] = sprintf('%s:%s', 'mysql', 
                        http_build_query($dsnArray, '', ';'));
            }
            return factory('DbPdo', $config);
        }
        elseif ($driver == 'mssql') {
            return factory('Dbmssql', $config);
        }
        elseif ($driver == 'oracle') {
            return factory('DbOracle', $config);
        }
        elseif ($driver == 'mongo') {
            return factory('DbMongo', $config);
        }
        elseif ($driver == 'sqlite') {
            return factory('DbSqlite', $config);
        }
        elseif ($driver == 'postgresql') {
            return factory('DbPostgres', $config);
        }
        else {
            return factory('DbMysql', $config);
        }
    }
    /**
     * 取得数据库类实例
     * @static
     * @access public
     * @return mixed 返回数据库驱动类
     */
    static public function getInstance(){
        //$args = func_get_args();
        //return getClass(__CLASS__,'factory',$args);
        return factory('Model');
    }
}
?>
