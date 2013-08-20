<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * 基于MEMCACHE的SESSION处理引擎
 * 
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */
class SessionMemcache extends SessionAbstract {

    /**
     * Memcache连接对象
     * @access private
     * @var Object
     */
    private $memcache;

    function __construct() {
        $config = C("session.memcache");
        $this->memcache = new Memcache();
        $this->memcache->connect($config['host'],$config['port'],2.5);
    }

    function open() {
        return true;
    }

    /**
     * 获得缓存数据
     * @param string $sid
     * @return void
     */
    function read($sid) {
        $m_data =  $this->memcache->get($sid);
        if(!isset($m_data['card']))return array();
        return $m_data['card']===$this->card?$m_data['Data']:array();
    }

    /**
     * 写入SESSION
     * @param string $sid
     * @param string $data
     * @return void
     */
    function write($sid, $data) {
        $m_data=array();
        $m_data['card']=$this->card;
        $m_data['Data']=$data;
        return $this->memcache->set($sid, $m_data);
    }

    /**
     * 删除SESSION
     * @param string $sid  SESSION_id
     * @return boolean
     */
    function destroy($sid) {
        return $this->memcache->delete($sid);
    }

    /**
     * 垃圾回收
     * @return boolean
     */
    function gc() {
        return true;
    }
    /**
     * 打开Session
     * @access public
     */
    public function run() {
        session_set_save_handler(array(&$this,"open"),
        array(&$this,"close"),
        array(&$this,"read"),
        array(&$this,"write"),
        array(&$this,"destroy"),
        array(&$this,"gc"));
    }
}

?>
