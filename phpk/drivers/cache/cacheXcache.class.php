<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * Xcache缓存驱动
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */
class CacheXcache extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        if ( !function_exists('xcache_info') ) {
            halt('��������չXcache');
        }
        $this->options['expire']    =   isset($options['expire'])?$options['expire']:C('cache.time');
        $this->options['prefix']    =   isset($options['prefix'])?$options['prefix']:C('cache.time');
        $this->options['length']    =   isset($options['length'])?$options['length']:0;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        N('cache_read',1);
        $name   =   $this->options['prefix'].$name;
        if (xcache_isset($name)) {
            return xcache_get($name);
        }
        return false;
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolen
     */
    public function set($name, $value,$expire=null) {
        N('cache_write',1);
        if(is_null($expire)) {
            $expire = $this->options['expire'] ;
        }
        $name   =   $this->options['prefix'].$name;
        if(xcache_set($name, $value, $expire)) {
            if($this->options['length']>0) {
                // 记录缓存队列
                $this->queue($name);
            }
            return true;
        }
        return false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name) {
        return xcache_unset($this->options['prefix'].$name);
    }
}