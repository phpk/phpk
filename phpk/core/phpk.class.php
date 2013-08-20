<?php

/**
 * PHPK
 *
 * 结构创建类
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 *
 */
defined('IN_ROOT') || exit('Access Denied');
class Phpk
{
    /**
     * 构造方法
     */
    public function __construct ()
    {
        if (is_array($_SERVER)) {
            foreach ($_SERVER as $k => $v) {
                if (isset($_SERVER[$k])) {
                    $_SERVER[$k] = str_replace(array('<','>','"',"'",'%3C','%3E','%22','%27','%3c','%3e'), '', $v);
                }
            }
        }
        unset($_ENV, $HTTP_GET_VARS, $HTTP_POST_VARS, $HTTP_COOKIE_VARS, $HTTP_SERVER_VARS, $HTTP_ENV_VARS);
        // 对变量的转义处理
        if (! MAGIC_QUOTES_GPC) {
            $_GET = $_GET ? addslashes_d($_GET) : array();
            $_POST = $_POST ? addslashes_d($_POST) : array();
            $_COOKIE = $_COOKIE ? addslashes_d($_COOKIE) : array();
            $_SESSION = $_SESSION ? addslashes_d($_SESSION) : array();
            $_FILES = $_FILES ? addslashes_d($_FILES) : array();
        }
    }
 
    /**
     * 初始化系统结构
     */
    public static function structure ()
    {
        //项目的公用数据目录
        if(!is_dir(DATA_PATH)) {
            $dirs = array(
                        PRO_PATH,//项目地址
                        PLUGIN_PATH,//插件地址
                        DATA_PATH, // 系统的数据目录
                        DATA_PATH . "cache/", // 数据缓存
                        DATA_PATH . "tplc/", // 模板的动态缓存
                        DATA_PATH . "logs/", // 错误日志
                        DATA_PATH . "db/", // 数据库缓存
                        //DATA_PATH . "auth/", // 数据库缓存
                        DATA_PATH . "upload/", // 用户上传的数据
                    );
            self::mkdir($dirs);
            foreach ($dirs as $d) {
                copy(PHPK_PATH . "sources/html/index.html", $d . 'index.html');
            }
            
        }
        
        //var_dump(APP_PATH);
        // 当前的应用目录
        if (! is_dir(APP_PATH)) {
            $dirs = array(
                    APP_PATH,
                    APP_PATH . "models/", // 当前应用的模型目录
                    APP_PATH . "controllers/", // 当前应用的控制器目录
                    APP_PATH . "hooks/", // 项目的钩子
                    APP_PATH . "views/", // 当前应用的视图目录
                    APP_PATH . "views/errors/", // 当前应用的视图错误页面
                    APP_PATH . "views/hooks/", // 当前应用的视图钩子
                    APP_PATH . "views/ads/", // 当前应用的视图的广告目录或者块目录
                    APP_PATH . "views/layouts/", // 当前应用的视图的广告目录
                    APP_PATH . "views/default/", // 当前应用的默认模版路径
                    APP_PATH . "extensions/", // 项目的扩展
                    APP_PATH . "extensions/classes/", // 项目的通用类
                    APP_PATH . "extensions/functions/", // 项目的通用函数
                    APP_PATH . "extensions/plugins/", // 项目的通用私有插件
                    APP_PATH . "extensions/drivers/", // 当前应用的驱动类库
                    APP_PATH . "extensions/drivers/db/", // 当前应用的db扩展
                    APP_PATH . "extensions/drivers/auth/", // 当前应用的auth认证扩展
                    APP_PATH . "extensions/drivers/cache/", // 当前应用的cache扩展
                    APP_PATH . "html/", // 当前应用的静态文件目录
                    APP_PATH . "public/", // 资源公共目录
                    APP_PATH . "public/styles/",
                    APP_PATH . "public/javascripts/", // 额外js开发目录
                    APP_PATH . "public/images/",
                    APP_PATH . "public/css/", // css缓存
                    APP_PATH . "public/js/", // js缓存
                    APP_PATH . "public/xpjs/", // xpjs目录
                    );
            self::mkdir($dirs);
            foreach ($dirs as $v) {
                if (($v == APP_PATH) || strpos($v, "views/") !== false) {
                    continue;
                }
                copy(PHPK_PATH . "sources/html/index.html", $v . 'index.html');
            }
            
            copy(PHPK_PATH . "sources/file/indexcontroller.php", 
                    APP_PATH . 'controllers/indexController.class.php');
            copy(PHPK_PATH . "sources/html/welcome.html", 
                    APP_PATH . 'views/default/welcome.php');
            copy(PHPK_PATH . "sources/html/layout.html",
                APP_PATH . 'views/layouts/layout.php');
            file_put_contents(APP_PATH . 'index.html', 
                    "<meta http-equiv='Refresh' content='0;URL=" . WEB_URL .
                             "index.php?app=" . APP_NAME . "'>");
        }
    }
    /**
     *
     *
     * 创建目录
     *
     * @param array|string $dirs            
     */
    public static function mkdir ($dirs)
    {
        if (is_array($dirs)) {
            foreach ($dirs as $dir) {
                if (! is_dir($dir)) {
                    if (!mkdir($dir, 0755)) {
                        halt("创建目录" . $dir . "失败");
                    }
                }
            }
        }
        else {
            if (! is_dir($dirs)) {
                if (!mkdir($dirs, 0755)) {
                    halt("创建目录" . $dirs . "失败");
                }
            }
        }
    }
    /**
     * 自动加载控制器
     *
     * @param string $controllerfile            
     * @param string $class            
     * @param string $app            
     * @return void
     */
    public static function run ()
    {
        // 控制器类所在的路径
        $control = strtolower(APP_CONTROL);
        $action = APP_ACTION;
        
        $controllerFile = APP_PATH . "controllers" . DS . $control .  "Controller.class.php";
        debug("当前访问的控制器类在项目应用目录下的: <b>$controllerFile</b> 文件！");
        // 控制器类的创建
        if (is_file($controllerFile)) {
            
            //将控制器类中的内容读出来
            $classContent = file_get_contents( $controllerFile );
            //看类中有没有继承父类
            $super = '/extends\s+(.+?)\s*{/i';
            //如果已经有父类
            if (preg_match( $super , $classContent , $arr ))
            {
                $parentClass = $arr[1];
                if($parentClass !== 'Controller'){
                    $parentFile = APP_PATH . "controllers" . DS . lcfirst($parentClass) . ".class.php";
                    requireCache($parentFile);
                }
            }
            //加载控制器
            $class = ucfirst($control) . 'Controller';
            requireCache($controllerFile);
            //var_dump($controllerFile);
            if (class_exists($class)) {
                
                $controller = new $class();
                //执行钩子
                Boot::checkHook('post_controller_constructor',$controller);
                if (! empty($action)) {
                    if (method_exists($controller, $action)) {
                        //判断是否执行权限认证
                        //Auth::init($class,$action,$classContent);
                        //执行action
                        $controller->$action();
                        //执行钩子
                        Boot::checkHook('post_controller',$controller);
                    }
                    else {
                        //var_dump(lang('error'));
                        msg("控制器方法不存在");
                    }
                }
                else {
                    msg("控制器方法不存在");
                }
            }
            else {
                msg("当前访问的控制器类<b>$class</b> 不存在！");
            }
        }
        else {
            msg("<font color='red'>对不起!你访问的模块不存在,应该在" . APP_PATH .
                             "controllers目录下创建文件名为" . $control .
                             "Controller.class.php的文件，声明一个类名为" .
                             ucfirst($control) . "的类！</font>");
        }
    }
}
?>