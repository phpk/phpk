<?php
/**
 * queue class file
 *
 * 队列操作
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 */
defined('IN_ROOT') || exit('Access Denied');

class queue extends Base {

    /**
     * 存放队列数据
     *
     * @var array
     */
    private static $queue = array();

    /**
     * 队列-设置值
     *
     * @param string    $value    加入队列的值
     * @return string
     */
    public static function set($value) {

        array_unshift(self::$queue, $value);
        return true;
    }

    /**
     * 队列-从队列中获取一个最早放进队列的值
     *
     * @return string
     */
    public static function get() {

        return array_pop(self::$queue);
    }

    /**
     * 队列-队列中总共有多少值
     *
     * @return string
     */
    public function count() {

        return count(self::$queue);
    }

    /**
     * 队列-清空队列数据
     *
     * @return string
     */
    public function clear() {

        self::$queue = array();
    }
}