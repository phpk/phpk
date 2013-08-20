<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * SESSION文件驱动
 * 
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */

class SessionFile
{
    //初始
    public function run()
    {
        if (C("session.savepath")) {
            session_save_path(C("session.savepath"));
        }
        session_name(C("session.name"));
        session_set_save_handler(
            array(&$this, "open"),
            array(&$this, "close"),
            array(&$this, "read"),
            array(&$this, "write"),
            array(&$this, "destroy"),
            array(&$this, "gc")
        );
    }

    //打开
    public function open()
    {
        return true;
    }

    //读取
    public function read($sid)
    {
        $session = session_save_path() . '/phpksess_' . $sid;
        if (!is_file($session)) {
            return false;
        }
        return file_get_contents($session);
    }

    //写入
    public function write($sid, $data)
    {
        $session = session_save_path() . '/phpksess_' . $sid;
        return file_put_contents($session, $data) ? true : false;
    }

    //卸载
    public function destroy($sid)
    {
        $session = session_save_path() . '/phpksess_' . $sid;
        if (is_file($session)) {
            unlink($session);
        }
    }

    //垃圾回收
    public function gc()
    {
        $path = session_save_path();
        foreach (glob($path . "/*") as $file) {
            if (strpos($file, "phpksess_") === false) continue;
            if (filemtime($file) + C("session.lifetime") < time()) {
                unlink($file);
            }
        }
        return true;
    }

    //关闭
    public function close()
    {
        //关闭SESSION
        if (mt_rand(1, C("session.gc_divisor")) == 1) {
            $this->gc();
        }
        return true;
    }

}

?>
