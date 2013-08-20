<?php
/**
 * 插件基础类
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 *
 */
defined('IN_ROOT') || exit('Access Denied');
class Plugin extends Base
{
    /**
     * 获取当前扩展Plugin目录的路径
     *
     * @access public
     * @return string    目录的路径
     */
    public function getPluginRoot() {
    
        return PLUGIN_PATH . $this->getPluginName() . DS;
    }
    
    /**
     * 获取当前Module目录的URL
     *
     * @access public
     * @return string    当前Moudle目录的URL
     */
    public function getPluginUrl() {
    
        return WEB_PLUGIN . $this->getPluginName() . '/';
    }
    
    /**
     * 获取当前Plugin的名称
     *
     * @access protected
     * @return string    Plugin名称
     */
    protected function getPluginName() {
    
        return substr(strtolower(get_class($this)), 0, -6);
    }
    
    /**
     * 分析Plugin的视图文件
     *
     * @access protected
     * @param stirng $fileName 视图文件名称
     * @return string
     */
    protected function getViewFile($fileName = null) {
    
        //获取当前Plugin文件路径
        $pluginName = $this->getPluginName();
    
        //参数分析
        if (!$fileName) {
            $fileName = $pluginName;
        }
    
        return PLUGIN_PATH . $pluginName . '/views/' . $fileName . '.php';
    }
    
    /**
     * 加载并显示视图片段文件内容
     *
     * @access public
     * @param string  $fileName 视图片段文件名称
     * @param array   $_data     视图模板变量，注：数组型
     * @param boolean $return    视图内容是否为返回，当为true时为返回，为false时则为显示. 默认为:false
     * @return void
     */
    public function display($fileName, $_data = array(), $return = false){
    
        //参数分析
        if (!$fileName) {
            return false;
        }
    
        //分析视图文件的路径
        $viewFile = $this->getViewFile($fileName);
    
        //模板变量赋值
        if (!empty($_data) && is_array($_data)) {
            extract($_data, EXTR_PREFIX_SAME, 'data');
            unset($_data);
        }
    
        //获取$fileName所对应的视图片段内容
        ob_start();
        include $viewFile;
        $content = ob_get_clean();
    
        if (!$return) {
            echo $content;
        } else {
            return $content;
        }
    }
}