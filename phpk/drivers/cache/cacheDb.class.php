<?php
/**
 * CacheDb class file
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 */
defined('IN_ROOT') || exit('Access Denied');
/**
 * 数据库方式缓存驱动
 *    CREATE TABLE tb_cache (
 *      cachekey varchar(255) NOT NULL,
 *      expire int(11) NOT NULL,
 *      data blob,
 *      datacrc varchar(32),
 *      UNIQUE KEY `cachekey` (`cachekey`)
 *    );
 */
class CacheDb extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        if(empty($options)) {
            $options = array (
                'table'     =>  C('cache.table'),
            );
        }
        $this->options  =   $options;   
        $this->options['prefix']    =   isset($options['prefix'])?  $options['prefix']  :   C('cache.prefix');
        $this->options['length']    =   isset($options['length'])?  $options['length']  :   0;        
        $this->options['expire']    =   isset($options['expire'])?  $options['expire']  :   C('cache.time');
        //import('Db');
        $this->handler   = Model::getInstance();
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        $name       =  $this->options['prefix'].addslashes($name);
        N('cache_read',1);
        $result     =  $this->handler->select('SELECT `data`,`datacrc` FROM `'.$this->options['table'].'` WHERE `cachekey`=\''.$name.'\' AND (`expire` =0 OR `expire`>'.time().') LIMIT 0,1',1);
        if(false !== $result ) {
            //$result   =  $result[0];
            if(C('cache.check')) {//开启数据校验
                if($result['datacrc'] != md5($result['data'])) {//校验错误
                    return false;
                }
            }
            $content   =  $result['data'];
            if(C('cache.compress') && function_exists('gzcompress')) {
                //启用数据压缩
                $content   =   gzuncompress($content);
            }
            $content    =   unserialize($content);
            return $content;
        }
        else {
            return false;
        }
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
        $data   =  serialize($value);
        $name   =  $this->options['prefix'].addslashes($name);
        N('cache_write',1);
        if( C('cache.compress') && function_exists('gzcompress')) {
            //数据压缩
            $data   =   gzcompress($data,3);
        }
        if(C('cache.check')) {//开启数据校验
            $crc  =  md5($data);
        }else {
            $crc  =  '';
        }
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        $expire	    =   ($expire==0)?0: (time()+$expire) ;//缓存有效期为0表示永久缓存
        $updata = array('cachekey'=>$name,'data'=>$data,'expire'=>$expire,'datacrc'=>$crc);
        $this->handler->update($this->options['table'],$updata,null,1);
        if($result) {
            if($this->options['length']>0) {
                // 记录缓存队列
                $this->queue($name);
            }
            return true;
        }else {
            return false;
        }
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolen
     */
    public function rm($name) {
        $name  =  $this->options['prefix'].addslashes($name);
        return $this->handler->execute('DELETE FROM `'.$this->options['table'].'` WHERE `cachekey`=\''.$name.'\'');
    }

    /**
     * 清除缓存
     * @access public
     * @return boolen
     */
    public function clear() {
        return $this->handler->execute('TRUNCATE TABLE `'.$this->options['table'].'`');
    }

}