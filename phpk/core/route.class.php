<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * PHPK
 *
 * 路由分发类
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */
final class Route
{
    /**
     * 路由配置
     *
     * @var array
     */
    public static $config = array();
    
    public static $control;
    public static $action;
    /**
     * 路由解析核心方法
     *
     * @param array $config            
     */
    public static function parseUrl ($config)
    {
        self::$config = $config;
        self::$control = isset($config['default_controller']) ? $config['default_controller'] : 'index';
        self::$action = isset($config['default_action']) ? $config['default_action'] : 'index';
        if (PHP_SAPI !== 'cli') {
            self::makeUrl();
        }else{
            self::makeCliUrl();
        }
        
    }
    
    /**
     * 调用路由主方法
     *
     * @return void
     */
    public static function makeUrl ()
    {
        $urlType = intval(self::$config['url_type']);
        switch ($urlType) {
            // 字符串模式http://www.phpk.cn/index.php?c=index&a=test&page=2&id=1
            case 1:
                self::querystring();
                break;
            // path模式http://www.phpk.cn/index.php/c/index/a/test/page/2/id/1
            case 2:
                self::pathinfo();
                break;
            // 简单的rewrite模式http://www.phpk.cn/c/index/a/index/test/page/2/id/1
            case 3:
                self::pathinfo ();
                break;
            // 简单的rewrite模式http://www.phpk.cn/c_index_a_index_page_2_id_1
            case 4:
                self::simpleRewrite();
                break;
            // 复杂的rewrite模式http://www.phpk.cn/index/index/2/1
            case 5:
                self::complexRewrite();
                break;
            // html静态页模式http://www.phpk.cn/index/html/index/index/page_2_id_1.html
            case 6:
                self::htmlRewrite();
                break;
        }
    }
    /**
     * 设置cli模式下的路由
     */
    public static function makeCliUrl(){
        $c = (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == true) ? strtolower($_SERVER['argv'][1]) : self::$control;
        $a = (isset($_SERVER['argv'][2]) && $_SERVER['argv'][2] == true) ? strtolower($_SERVER['argv'][2]) : self::$action;
        //分析并获取参数, 参数格式如: --param_name=param_value
        if (($totalNum = count($_SERVER['argv'])) > 3) {
            for ($i = 3; $i < $totalNum; $i ++) {
                //CLI运行环境下参数模式:如 --debug=true, 不支持 -h -r等模式
                if (substr($_SERVER['argv'][$i], 0, 2) == '--') {
                    $pos = strpos($_SERVER['argv'][$i], '=');
                    if ($pos !== false) {
                        $key = substr($_SERVER['argv'][$i], 2, $pos - 2);
                        $_SERVER['argv'][$key] = substr($_SERVER['argv'][$i], $pos + 1);
                        unset($_SERVER['argv'][$i]);
                    }
                }
            }
        }
        self::defineControl($c);
        self::defineAction($a);
    }
    /**
     * 普通GET访问路由
     *
     * @return void
     */
    public static function querystring ()
    {
        $parse_url = parse_url($_SERVER['REQUEST_URI']);
        if (isset($parse_url['query'])) {
            $queryString = $parse_url['query'];
            $queryArray = explode("&", $queryString);
            $tmp = $array = array();
            if (count($queryArray) > 0) {
                foreach ($queryArray as $item) {
                    $tmp = explode('=', $item);
                    $array[$tmp[0]] = $tmp[1];
                }
                
                if ( !isset($array['c']) || !isset($_GET['c']) ) {
                    self::defineControl(self::$control);
                }
                if (!isset($array['a']) || !isset($_GET['a']) ) {
                    self::defineAction(self::$action);
                }
            }
            else {
                self::defineControl(self::$control);
                self::defineAction(self::$action);
            }
        }
        else {
            self::defineControl(self::$control);
            self::defineAction(self::$action);
        }
        
    }
    /**
     * 定义control常量
     * @param string $c
     */
    private static function defineControl($c){
        defined("APP_CONTROL") || define("APP_CONTROL", strtolower($c));
    }
    /**
     * 定义action常量
     * @param string $a
     */
    private static function defineAction($a){
        defined("APP_ACTION") || define("APP_ACTION", strtolower($a));
    }
    /**
     * 获取pathinfo
     * @return string
     */
    private static function getPathInfo() {
        $str = '';
        if(isset($_SERVER['PATH_INFO'])){
            if (! strpos($_SERVER['PATH_INFO'], '.')) {
                $str = $_SERVER['PATH_INFO'];
            }
            else {
                $str = substr($_SERVER['PATH_INFO'], 0, strpos($_SERVER['PATH_INFO'], '.'));
            }
        }
        return explode('/', trim($str, '/'));
    }
    /**
     * PATHINFO模式访问路由
     *
     * @return void
     */
    public static function pathinfo ()
    {
        // 获取路径信息(pathinfo)
        $pathinfo = self::getPathInfo();
        
        $num = count($pathinfo);
        for ($i = 0; $i < $num; $i += 2) {
            if(isset($pathinfo[$i]) && isset($pathinfo[$i + 1])){
                $_GET[addslashes($pathinfo[$i])] = addslashes($pathinfo[$i + 1]);
            }
        }
        
        $c = (!empty($_GET['c']) ? $_GET['c'] : self::$control);
        $a = (!empty($_GET['a']) ? $_GET['a'] : self::$action);
        self::defineControl($c);
        self::defineAction($a);
    }
    /**
     * 简单rewrite路由
     */
    public static function simpleRewrite ()
    {
        // 获取路径信息(pathinfo)
        $info = end(self::getPathInfo());
        $pathinfo = explode("_", $info);
        $num = count($pathinfo);
        for ($i = 0; $i < $num; $i += 2) {
            if(isset($pathinfo[$i]) && isset($pathinfo[$i + 1])){
                $_GET[addslashes($pathinfo[$i])] = addslashes($pathinfo[$i + 1]);
            }
        }
        
        $c = (!empty($_GET['c']) ? $_GET['c'] : self::$control);
        $a = (!empty($_GET['a']) ? $_GET['a'] : self::$action);
        self::defineControl($c);
        self::defineAction($a);
    }
    public static function complexRewrite ()
    {
    }
    public static function htmlRewrite ()
    {
    }
}
?>