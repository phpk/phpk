<?php
/**
 * 缓存管理类
 * 
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */
defined('IN_ROOT') || exit('Access Denied');
class Cache extends Base 
{

    /**
     * 操作句柄
     * @var string
     * @access protected
     */
    protected $handler;

    /**
     * 缓存连接参数
     * @var integer
     * @access protected
     */
    protected $options = array();
    /**
     * 单列化入口
     * @var object
     */
    static $instance = NULL;
    /**
     * 连接缓存
     * @access public
     * @param string $type 缓存类型
     * @param array $options  配置数组
     * @return object
     */
    public function connect($type='',$options=array()) {
        if(empty($type))  $type = C('cache.type');
        if(empty($type)){
            return;
        }
        $type  = strtolower(trim($type));
        $class = 'Cache'.ucfirst($type);
        //var_dump($type);
        if(class_exists($class)){
            $cache = new $class($options);
        }else{
            halt("调用的缓存类".$class."不存在");
        } 
        return $cache;
    }

    public function __get($name) {
        return $this->get($name);
    }

    public function __set($name,$value) {
        return $this->set($name,$value);
    }

    public function __unset($name) {
        $this->rm($name);
    }
    public function setOptions($name,$value) {
        $this->options[$name]   =   $value;
    }

    public function getOptions($name) {
        return $this->options[$name];
    }

    /**
     * 取得缓存类实例
     * @static
     * @access public
     * @return mixed
     */
    static function getInstance($type='',$options=array()) {
       if(self::$instance == NULL){
           $cache = new Cache();
           self::$instance = $cache->connect($type,$options);
       } 
       return self::$instance;
    }

    /**
     * 队列缓存
     * @access protected
     * @param string $key 队列名
     * @return mixed
     */
    // 
    protected function queue($key) {
        static $_handler = array(
            'file'  =>  array('F','F'),
            'xcache'=>  array('xcache_get','xcache_set'),
            'apc'   =>  array('apc_fetch','apc_store'),
        );
        $queue  =  isset($this->options['queue'])?$this->options['queue']:'file';
        $fun    =  isset($_handler[$queue])?$_handler[$queue]:$_handler['file'];
        $queue_name=isset($this->options['queue_name'])?$this->options['queue_name']:'think_queue';
        $value  =  $fun[0]($queue_name);
        if(!$value) {
            $value   =  array();
        }
        // 进列
        if(false===array_search($key, $value))  array_push($value,$key);
        if(count($value) > $this->options['length']) {
            // 出列
            $key =  array_shift($value);
            // 删除缓存
            $this->rm($key);
             if(DEUBG){
                //调试模式下，记录出列次数
                N($queue_name.'_out_times',1,true);
            }
        }
        return $fun[1]($queue_name,$value);
    }
    
    public function __call($method, array $args){
        //调用缓存类型自己的方法
        if(method_exists($this->handler, $method)){
           return call_user_func_array(array($this->handler,$method), $args);
        }else{
            halt(__CLASS__.':'.$method."调用的该方法不存在");
            return;
        }
    }
    
}