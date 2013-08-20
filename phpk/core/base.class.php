<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * PHPK
 *
 * 基类 用于被继承 不可实例化
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */
abstract class Base
{
    
    /**
     * 自动变量设置
     *
     * 程序运行时自动完成类中作用域为protected及private的变量的赋值 。
     *
     * @access public
     * @param string $name
     *            属性名
     * @param string $value
     *            属性值
     * @return void
     */
    public function __set ($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }
    
    /**
     * 自动变量获取
     *
     * 程序运行时自动完成类中作用域为protected及private的变量的获取。
     *
     * @access public
     * @param string $name
     *            属性名
     * @return mixed
     */
    public function __get ($name)
    {
        return isset($this->$name) ? $this->$name : false;
    }
    /**
     * 函数: __call()
     *
     * 用于处理类外调用本类不存在的方法时的信息提示
     *
     * @access public
     * @param string $method
     *            方法名称
     * @param string $args
     *            参数名称
     * @return string
     */
    public function __call ($method, array $args)
    {
        echo 'Method:' . $method . '() is not exists in Class:' .
                 get_class($this) . '!<br/>The args is:<br/>';
        foreach ($args as $value) {
            echo $value, '<br/>';
        }
    }
    
    /**
     * 输出类的实例化对象
     *
     * 直接调用函数，输出内容。
     *
     * @access public
     * @return string
     */
    public function __toString ()
    {
        return (string) 'This is ' . get_class($this) . ' Class!';
    }
}
?>