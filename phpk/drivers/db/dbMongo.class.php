<?php
/**
 * db_mongodb class file
 *
 * 用于mongodb数据库的操作(no sql)
 * 
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 */

defined('IN_ROOT') || exit('Access Denied');

class DbMongo extends Base {

    /**
     * 单例模式实例化对象
     *
     * @var object
     */
    protected static $_instance;

    /**
     * 数据库连接ID
     *
     * @var object
     */
    protected $dbLink;

    /**
     * mongo实例化对象
     *
     * @var object
     */
    protected $_mongo;

    /**
     * 构造函数
     *
     * @access public
     * @param array $params 数据库连接参数,如主机名,数据库用户名,密码等
     * @return boolean
     */
    public function __construct(array $params = null) {

        if (!extension_loaded('mongo')) {
            halt('The mongo extension must be loaded!');
        }

        //参数分析
        if (empty($params) || !is_array($params)) {
            //加载数据库配置文件.
            $params = Controller::getConfig('mongo');
        }

        if (empty($params)) {
            $params['dsn']        = 'mongodb://localhost:27017';
            $params['option']     = array('connect' => true);
        } else {
            //分析dsn信息
            if (!$params['dsn']) {
                $params['dsn'] = 'mongodb://'. trim($params['host']) . ':' . ($params['port'] ? $params['port'] : '27017');
            }
            $params['option']  = (!$params['option']) ? array('connect' => true) : trim($params['option']);
        }

        //实例化mongo
        $this->_mongo = new Mongo($params['dsn'], $params['option']);
        if ($params['dbname']) {
            $this->dbLink = $this->_mongo->selectDB($params['dbname']);
        }

        //用户登录
        if ($params['username'] && $params['password']) {
            $result = $this->dbLink->authenticate($params['username'], $params['password']);
            if (!$result) {
                halt('Mongo Auth Failed: bad user or password.');
            }
        }

        return true;
    }

    /**
     * Select Collection
     *
     * @author ColaPHP
     * @param string $collection
     * @return MogoCollection
     */
    public function collection($collection) {

        return $this->dbLink->selectCollection($collection);
    }

    /**
     * 查询一条记录
     *
     * @access public
     * @param string $collnections    集合名称(相当于关系数据库中的表)
     * @param array     $query            查询的条件array(key=>value) 相当于key=value
     * @param array  $filed            需要列表的字段信息array(filed1,filed2)
     * @return array
     */
    public function fetchRow($collnections, $query, $filed=array()) {
        return $this->collection($collnections)->findOne($query, $filed);
    }
    /**
     * 获取单列数据信息（字段型）
     *
     * @param string $sql
     * @return array
     */
    public function fetchColumn( $collnections, $query, $filed=array() )
    {
        $row = $this->fetchRow($collnections, $query, $filed=array());
        if(isset($row[0])){
            return $row[0];
        }
        if(count($row) == 1){
            return current($row);
        }
        return false;
    }
    /**
     * 查询多条记录
     *
     * @access public
     * @param string $collnections    集合名称(相当于关系数据库中的表)
     * @param array     $query            查询的条件array(key=>value) 相当于key=value
     * @param array  $filed            需要列表的字段信息array(filed1,filed2)
     * @return array
     */
    public function fetchAll($collnections, $query, $filed=array()) {

        $result = array();
        $cursor = $this->collection($collnections)->find($query, $filed);
        while ($cursor->hasNext()) {
            $result[] = $cursor->getNext();
        }

        return $result;
    }

    /**
     * 插入数据
     *
     * @access public
     * @param string    $collnections    集合名称(相当于关系数据库中的表)
     * @param array        $data_array
     * @return boolean
     */
    public function insert($collnections, $data_array) {

        return $this->collection($collnections)->insert($data_array);
    }

    /**
     * 更改数据
     *
     * @access public
     * @param string    $collnections    集合名称(相当于关系数据库中的表)
     * @param array        $query
     * @param array        $update_data
     * @param array     $options
     * @return boolean
     */
    public function update($collection, $query, $update_data, $options=array('safe'=>true,'multiple'=>true)) {

        return $this->collection($collection)->update($query, $update_data, $options);
    }

    /**
     * 删除数据
     *
     * @access public
     * @param string    $collnections    集合名称(相当于关系数据库中的表)
     * @param array        $query
     * @param array     $option
     * @return unknow
     */
    public function delete($collection, $query, $option=array("justOne"=>false)) {

        return $this->collection($collection)->remove($query, $option);
    }

     /**
     * MongoId
     *
     * @author ColaPHP
     * @param string $id
     * @return MongoId
     */
    public static function id($id = null)
    {
        return new MongoId($id);
    }

    /**
     * MongoTimestamp
     *
     * @author ColaPHP
     * @param int $sec
     * @param int $inc
     * @return MongoTimestamp
     */
    public static function Timestamp($sec = null, $inc = 0)
    {
        if (!$sec) $sec = time();
        return new MongoTimestamp($sec, $inc);
    }

    /**
     * GridFS
     *
     * @author ColaPHP
     * @return MongoGridFS
     */
    public function gridFS($prefix = 'fs')
    {
        return $this->dbLink->getGridFS($prefix);
    }

    /**
     * 析构函数
     *
     * @access public
     * @return void
     */
    public function __destruct() {

        if ($this->_mongo) {
            $this->_mongo->close();
        }
    }

    /**
     * 本类单例实例化函数
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