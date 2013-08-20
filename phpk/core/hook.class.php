<?php
/**
 * Hook基础类
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 *
 */
abstract class Hook extends Response
{
    /**
     * 模版变量
     *
     * @var array
     */
    public $var = array();
    /**
     * 系统参数
     * @var array|string
     */
    public $options;
    /**
     * 构造方法
     */
    public function __construct (&$param = null)
    {
        $this->options = $param;
    }
    
    /**
     * 定义模版变量
     *
     * @param string $k
     * @param string|array $v
     */
    public function assign ($k, $v)
    {
        $this->var[$k] = $v;
    }
    /**
     * 模板输出
     * @param string $tpl
     */
    public function display($tpl)
    {
        $view = new View();
        $view->displayHook($tpl,$this->var);
    }
    /**
     * run方法是Hook唯一的接口,用于系统内核类扩展
     * @access public
     * @return void
     */
    abstract public function run();

}