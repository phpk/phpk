<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * PHPK
 *
 * 框架主控制器
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */
class Controller extends Response
{
    /**
     * 模版变量
     * 
     * @var array
     */
    public $vars = array();
    /**
     * 模型初始化
     * 
     * @var array
     */
    public $model;
    /**
     * 插件初始化
     *
     * @var array
     */
    public $plugin;
    /**
     * 输出类型
     * @var array
     */
    protected $_types = array();
    
    /**
     * 构造方法
     */
    public function __construct ()
    {
        $this->model = M();
        $this->_types = array('json','xml','php','html','htm','txt');
    }
    
    /**
     * 定义模版变量
     * 
     * @param string $k            
     * @param string|array $v            
     */
    public function assign ($k, $v)
    {
        $this->vars[$k] = $v;
    }
    /**
     * 模板输出
     *
     * @param string $tpl            
     * @param array $var            
     * @param boolen $out            
     */
    public function display ($tpl, $layoutName = '')
    {
        $view = new View();
        $view->init($tpl,$this->vars,$layoutName);
    }
    
}

?>