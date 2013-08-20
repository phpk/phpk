<?php
defined('IN_ROOT') || exit('Access Denied');

/**
 * Redis缓存驱动 
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */
class CacheRedis extends Cache {
	 /**
	 * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        if ( !extension_loaded('redis') ) {
            halt('请增加扩展redis');
        }
        if(empty($options)) {
            $options = array (
                'host'          => C('cache.redis_host') ? C('cache.redis_host') : '127.0.0.1',
                'port'          => C('cache.redis_port') ? C('cache.redis_port') : 6379,
                'timeout'       => C('cache.timeout') ? C('cache.timeout') : false,
                'persistent'    => false,
            );
        }
        $this->options =  $options;
        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   C('cache.time');
        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   C('cache.prefix');        
        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;        
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler  = new Redis;
        $options['timeout'] === false ?
            $this->handler->$func($options['host'], $options['port']) :
            $this->handler->$func($options['host'], $options['port'], $options['timeout']);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        N('cache_read',1);
        $value = $this->handler->get($this->options['prefix'].$name);
        $jsonData  = json_decode( $value, true );
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolen
     */
    public function set($name, $value, $expire = null) {
        N('cache_write',1);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $name   =   $this->options['prefix'].$name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if(is_int($expire)) {
            $result = $this->handler->setex($name, $expire, $value);
        }else{
            $result = $this->handler->set($name, $value);
        }
        if($result && $this->options['length']>0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name) {
        return $this->handler->delete($this->options['prefix'].$name);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolen
     */
    public function clear() {
        return $this->handler->flushDB();
    }

}
