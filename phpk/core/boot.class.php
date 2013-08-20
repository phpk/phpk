<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * PHPK
 *
 * 系统入口类
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 *
 */
class Boot{
    /**
     * 类库包
     * @var array
     */
    public static $_classes = array();
    /**
     * 系统基础定义
     * @var array
     */
    public static $_configs = array();

    /**
     * 系统初始化加载
     * 
     */
    public static function init(){
        global $CONFIG;
        self::$_configs = $CONFIG; 
        //定义系统常量
        self::systemDefine();
        //非win系统加入系统负载
        if(!IS_WIN){
            self::_load_protection();
        }
        //语言包侦测
        self::checkLanguage();
        //if(self::$_configs['language']['switch_on'] == 1){
            //加入或引用语言包
            //$GLOBALS['_languages'] = self::checkLanguage();
        //}
        
        //加载系统文件
        self::_loadBaseLib();
        spl_autoload_register('Boot::autoLoad');
        
        //判断系统入口
        if(in_array(APP_NAME, self::$_configs['system']['app']['allow_dir'])){
            Phpk::structure();
        }else{
            msg("项目应用地址不存在，请联系管理员！");
        }
        //加载应用的配置
        if (!is_file( APP_PATH . "config.inc.php" )) {
            copy(PHPK_PATH . "sources/file/appconfig.php", APP_PATH . 'config.inc.php');    
        }
        include APP_PATH . "config.inc.php";
        $GLOBALS['_configs'] = $CONFIG;
        
        //var_dump($CONFIG);
        //加载外围配置
        $otherFile = self::base_get_loader();
        R($otherFile);
        
        //加载缓存库
        if(isset($CONFIG['cache']['type'])){
            L($CONFIG['cache']['type'], 'cache');
        }
        
        //开启错误日志
        self::_sysDebug();
        
        //执行钩子
        self::checkHook('pre_system');
        
        // 引入核心控制器与模型
        Route::parseUrl($CONFIG['route']);
        // 获取$_GET['c']定义controller
        // 定义页面的唯一标志
        define("PAGE_KEY", strtolower(APP_NAME . '_' . APP_CONTROL . '_' . APP_ACTION));
        //引用语言包
        if(self::$_configs['language']['switch_on'] == 1){
            $GLOBALS['_languages'] = self::loadLanguage();
        }
        
        //执行钩子
        self::checkHook('pre_controller');
        //启动系统
        Phpk::run();
        //执行钩子
        self::checkHook('post_system');
        // 获取结束时间输出调试信息
        if (DEBUG === 1) {
            Debug::stop();
            Debug::output();
        }
        
        
    }
    
    /**
     * 类文件自动加载方法 callback
     * @param string $className
     * @return boolean
     */
    public static function autoLoad($className) {
        if ( !isset( self::$_classes[$className] ) ) {
            return false;
        }
        require_once( self::$_classes[$className] );
        return true;
    }
    /**
     * 系统钩子检测
     * 
     * @param string $hookType
     * 
     * $hookType类型说明：
     * 
     * pre_system
     *     系统执行的早期调用.仅仅在系统类和配置项加载完毕的时候. 没有执行路由或者其它的过程.
     * pre_controller
     *     在调用你的任何控制器之前调用.此时所用的基础类,路由选择和安全性检查都已完成.
     * post_controller_constructor
     *     在你的控制器实例化之后，任何方法调用之前调用，传入的参数为实例化的控制器。
     * post_controller
     *     在你的控制器完全运行之后调用，传入的参数为实例化的控制器。
     * display_override
     *     output.tmpl_cache=0时截取调试模式下的模版, 用来在系统执行末尾向web浏览器发送最终页面,传入参数为解析后的模版内容。调用后记得exit。
     * cache_override
     *     截取cache模式下的模版,这可以让你使用自己的方法来显示缓存文件，传入参数为解析后的缓存文件。调用后记得exit。
     * post_system
     *     在最终着色页面发送到浏览器之后,浏览器接收完最终数据的系统执行末尾调用
     * 
     */
    public static function checkHook($hookType, &$params=NULL){
        $config = $GLOBALS['_configs']['loader']['hooks'];
        if(isset($config[$hookType])){
            hook($config[$hookType], $params, 'run');
        }
    }
    /**
     * 加载核心层库函数
     *
     * @return void
     */
    private static function _loadBaseLib() {
        $driver = ucfirst(strtolower(self::$_configs['system']['db']['driver']));
        define('DB_DRIVER',$driver);
        $driverFile = getFile(strtolower($driver),'db');
        if(!is_file($driverFile)){
            halt($driverFile.'数据库驱动文件不存在');
        }
        $driver = 'Db'.$driver;
        self::$_classes = array(
                'Base'       => PHPK_PATH . 'core/base.class.php',
                'Phpk'       => PHPK_PATH . 'core/phpk.class.php',
                'Compress'   => PHPK_PATH . 'core/compress.class.php',
                'Response'   => PHPK_PATH . 'core/response.class.php',
                'View'       => PHPK_PATH . 'core/view.class.php',
                'Auth'       => PHPK_PATH . 'core/auth.class.php',
                'Controller' => PHPK_PATH . 'core/controller.class.php',
                'Hook'       => PHPK_PATH . 'core/hook.class.php',
                'Model'      => PHPK_PATH . 'core/model.class.php',
                'Debug'      => PHPK_PATH . 'core/debug.class.php',
                'Route'      => PHPK_PATH . 'core/route.class.php',
                'Cache'      => PHPK_PATH . 'core/cache.class.php',
                'Plugin'     => PHPK_PATH . 'core/plugin.class.php',
                'Http'       => PHPK_PATH . 'core/http.class.php',
                "$driver"    => $driverFile
             );
    }
    /**
     * 开启错误日志
     *
     * @return void
     */
    private static function _sysDebug(){
        // 是否强制输出编码
        if (C('output.forceheader') == 1) {
            header("Content-Type:text/html;charset=" . C('output.charset'));
        }
        //开启错误日志
        if (C('debug') == 1) {
            error_reporting(E_ALL ^ E_NOTICE);
            define('DEBUG', 1);
            // 开启脚本计算时间
            Debug::start();
            // 设置捕获系统异常
            set_error_handler(array(
            "Debug",
            'catcher'
                    ));
        }
        else {
            define('DEBUG', 0);
            ini_set( 'display_errors' , 'Off' ); //屏蔽错误输出
        }
        //开启错误日志记录
        if (C('output.errorlog') == 1) {
            define("PHPK_LOG", true);
            ini_set('log_errors', 'On'); // 开启错误日志，将错误报告写入到日志中
            ini_set('error_log', LOG_PATH . 'error_' . date('Y-m-d') . '.log'); // 指定错误日志文件
        }
        else {
            define("PHPK_LOG", false);
        }
    }
    /**
     * 加载核心配置项和公用函数
     *
     * @return void
     */
    private static function _loadConfig(){
        //加载核心配置项
        if (!is_file( ROOT . "config.inc.php" ))
        {
            copy(PHPK_PATH.'sources/file/sysconfig.php', ROOT . "config.inc.php");
        }
        include ROOT . "config.inc.php";
        self::$_configs = $CONFIG;
        // 加载系统公用函数库
        include PHPK_PATH . 'commons/common.inc.php';
        include PHPK_PATH . 'commons/function.inc.php';
    }
    /**
     * 定义系统核心常量
     */
    public static function systemDefine(){
        // 项目的目录文件夹
        defined('APP_NAME') || define('APP_NAME', self::base_get_appname());
        define("PRO_PATH", ROOT . "projects" . DS);//项目的总目录地址
        defined('APP_PATH') || define('APP_PATH', PRO_PATH . APP_NAME . DS); // 用户项目的应用路径
        define("DATA_PATH", ROOT . "data" . DS);
        define("PLUGIN_PATH", ROOT . "plugins" . DS);       
        define("LOG_PATH", DATA_PATH . "logs" . DS);
        define("CACHE_PATH", DATA_PATH . "cache" . DS);
        define("TPLC_PATH", DATA_PATH . "tplc" . DS);
        define("DB_PATH", DATA_PATH . "db" . DS);
        define("UPLOAD_PATH", DATA_PATH . "upload" . DS);
        define("VIEW_PATH", APP_PATH . "views" . DS);
        define("PUBLIC_PATH", APP_PATH . "publics" . DS);
        define("TMPPATH", self::getTmpPath()); // 页面路径
        // Web服务器根到项目的根
        define("WEB_ROOT", self::getWebRoot());
        // 获取网址域名部分.
        define("WEB_DOMAIN", ! empty($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : PHP_SELF);
        $serverPort = ($_SERVER['SERVER_PORT'] == '80') ? '' : ':' . intval($_SERVER['SERVER_PORT']);
        // 获取网络协议.
        $secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
        //define("WEB_HOST", $secure . WEB_DOMAIN . $serverPort);
        define("WEB_HOST", $secure . WEB_DOMAIN);
        define("WEB_URL", WEB_HOST . ((WEB_ROOT == '/') ? '' : WEB_ROOT) . '/'); // 网站路径
        define("CURRENT_URL", $secure .$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]);
        define("WEB_PHPK", WEB_URL . PHPK_NAME . '/'); // phpk目录的网址
        define("WEB_PLUGIN", WEB_URL . 'plugins/'); // phpk目录的网址
        define("WEB_APP", WEB_URL . 'projects/' . APP_NAME . '/'); // 项目的网址
        define("WEB_PUBLIC", WEB_APP . 'public/');
        define("WEB_CSS", WEB_PUBLIC . 'styles/');
        define("WEB_JS", WEB_PUBLIC . 'javascripts/');
        define("WEB_IMG", WEB_PUBLIC . 'images/');
        define("WEB_NAME", $_SERVER["SCRIPT_NAME"] . '/');
        // var_dump(WEB_APP);
        if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            // 是否强制设置魔术方法
            ini_set('magic_quotes_runtime', 0);
            // PHP.ini是否自动开启过滤机制
            define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc() ? True : False);
        }
        else {
            define('MAGIC_QUOTES_GPC', false);
        }
        if(function_exists('curl_init')){
            define('SUPPORT_CURL',true);
        }else{
            define('SUPPORT_CURL',false);
        }
    }
    
    /**
     * 验证help、hook文件，并返回
     * @param string $loader
     * @param string $ftype
     * @return array
     */
    public static function base_get_loader_file($loader,$ftype){
        $files = array();
        if(count($loader) > 0) {
            foreach($loader as $libs) {
                $file = getFile($libs, $ftype);
                if($file) {
                    $files[] = $file;
                }
            }
        }
        return $files;
    }
    /**
     * 返回文件
     * @param array $loader
     * @return array
     */
    public static function base_get_loader(){
        $loader = C('loader');
        
        $libs = self::base_get_loader_file($loader['libs'],'libs');
        $helpers = self::base_get_loader_file($loader['helpers'],'helpers');
        //$loader['hooks']
        $hooks = array();
        if(count($loader['hooks'])>0){
            foreach ($loader['hooks'] as $v){
                if(false === strpos($v,',')){
                    $hooks[] = $v;
                }else{
                    $vv = explode(",", $v);
                    $hooks = array_merge($hooks,$vv);
                }
            }
        }
        $hooks = self::base_get_loader_file($hooks,'hooks');
        return array_merge($libs,$helpers,$hooks);
    
    }
    /**
     * 获取项目的名称
     * @return string|Ambigous <>
     */
    public static  function base_get_appname(){
        if(self::$_configs['system']['app']['auto_detect'] == 1 && self::isMobile()){
            $defaultApp = self::$_configs['system']['app']['defalut_wap_path'];
        }else{
            $defaultApp = self::$_configs['system']['app']['defalut_web_path'];
        }
        if (PHP_SAPI !== 'cli') {
            if(isset($_GET['app'])){
                return htmlspecialchars(trim($_GET['app']));
            }else{
                $self = htmlspecialchars(PHP_SELF);
                if(false !== strpos($self,"index.php/")){
                    $app = explode("index.php/", $self);
            
                    if(!empty($app[1])){
                        if(false === strpos($app[1],"/")){
                            return $app[1];
                        }else{
                            $str = explode("/", $app[1]);
                            //var_dump($str);
                            if(!empty($str[1])){
                                if($str[0] != 'app'){
                                    return $str[0];
                                }
                                return $str[1];
                            }else{
                                return $str[0];
                            }
                        }
                    }else{
                        return $defaultApp;
                    }
                }else{
                    return $defaultApp;
                }
            }
        }else{
            //cli模式下处理
            if($_SERVER['argc'] == 0){
                $_SERVER['argv'][0] = $defaultApp;
                return $defaultApp;
            }else{
                if($_SERVER['argv'][0] != $defaultApp){
                    array_unshift($_SERVER['argv'],$defaultApp);
                }
                return $defaultApp;
            }
        }
        
    }
    
    /**
     * 判断是否是手机访问
     * @return boolean
     */
    public static function isMobile() {
        $is_mobile = false;
        if(isset($_SERVER['HTTP_USER_AGENT'])){
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $mobile_agents = Array("240x320", "acer", "acoon",
                    "acs-", "abacho", "ahong", "airness", "alcatel",
                    "amoi", "android", "anywhereyougo.com",
                    "applewebkit/525", "applewebkit/532", "asus",
                    "audio", "au-mic", "avantogo", "becker", "benq",
                    "bilbo", "bird", "blackberry", "blazer", "bleu",
                    "cdm-", "compal", "coolpad", "danger", "dbtel",
                    "dopod", "elaine", "eric", "etouch", "fly ",
                    "fly_", "fly-", "go.web", "goodaccess",
                    "gradiente", "grundig", "haier", "hedy",
                    "hitachi", "htc", "huawei", "hutchison",
                    "inno", "ipad", "ipaq", "ipod", "jbrowser",
                    "kddi", "kgt", "kwc", "lenovo", "lg ", "lg2",
                    "lg3", "lg4", "lg5", "lg7", "lg8", "lg9", "lg-",
                    "lge-", "lge9", "longcos", "maemo", "mercator",
                    "meridian", "micromax", "midp", "mini", "mitsu",
                    "mmm", "mmp", "mobi", "mot-", "moto", "nec-",
                    "netfront", "newgen", "nexian", "nf-browser",
                    "nintendo", "nitro", "nokia", "nook", "novarra",
                    "obigo", "palm", "panasonic", "pantech", "philips",
                    "phone", "pg-", "playstation", "pocket", "pt-",
                    "qc-", "qtek", "rover", "sagem", "sama", "samu",
                    "sanyo", "samsung", "sch-", "scooter", "sec-",
                    "sendo", "sgh-", "sharp", "siemens", "sie-",
                    "softbank", "sony", "spice", "sprint", "spv",
                    "symbian", "tablet", "talkabout", "tcl-",
                    "teleca", "telit", "tianyu", "tim-", "toshiba",
                    "tsm", "up.browser", "utec", "utstar", "verykool",
                    "virgin", "vk-", "voda", "voxtel", "vx", "wap",
                    "wellco", "wig browser", "wii", "windows ce",
                    "wireless", "xda", "xde", "zte");
            
            foreach ($mobile_agents as $device) {
                if (stristr($user_agent,  $device)) {
                    $is_mobile = true;
                    break;
                }
            }
        }
        
        return $is_mobile;
    }
    /**
     * 语言检查
     * 检查浏览器支持语言，并自动加载语言包
     * @access private
     * @return void
     */
    private static function checkLanguage() {
        $lang = self::$_configs['language'];
        // 不开启语言包功能，仅仅加载框架语言文件直接返回
        if (!$lang || $lang['switch_on'] ==0){
            R(PHPK_PATH . 'language' . DS . 'zh-cn'. DS .'common.lang.php');
            return false;
        }
        $langSet = $lang['default_lang'];
        // 启用了语言包功能
        // 根据是否启用自动侦测设置获取语言选择
        if ($lang['auto_detect'] == 1){
            if(isset($_GET[$lang['get_var']])){
                $langSet = $_GET[$lang['get_var']];// url中设置了语言变量
                cookie('phpk_language',$langSet,3600);
            }elseif(isset($_COOKIE['phpk_language'])){// 获取上次用户的选择
                $langSet = $_COOKIE['phpk_language'];
            }elseif(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])){// 自动侦测浏览器语言
                preg_match('/^([a-z\d\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
                $langSet = $matches[1];
                setcookie('phpk_language',$langSet,3600);
            }
            if(false === stripos($lang['lang_list'],$langSet)) { // 非法语言参数
                $langSet = $lang['default_lang'];
            }
        }
        // 定义当前语言
        define('LANG_SET',strtolower($langSet));
    }
    /**
     * 加载语言包
     * @return Ambigous <object, boolean, unknown, unknown>
     */
    private static function loadLanguage(){
        // 读取项目公共语言包
        $file1 = PHPK_PATH . 'language' . DS . LANG_SET. DS .'common.lang.php';
        //var_dump($file);
        $path = APP_PATH . 'language' . DS . LANG_SET. DS;
        // 读取应用公共语言包
        $file2 = $path.'common.lang.php';
        // 读取当前模块语言包
        $file3 = $path.APP_CONTROL.'.lang.php';
        
        return R(array($file1,$file2,$file3));
    }
    /*
     * Web服务器根到项目的根
    */
    public static function getWebRoot(){
        return str_replace('\\', '/',
                dirname(
                        rtrim(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']),'/')
                )
        );
    }
    /**
     * 页面路径
     * @return mixed
     */
    public static function getTmpPath(){
        return str_replace(array(
                ".",
                "/"
        ), "_", ltrim(PHP_SELF, '/'));
    }
    /**
     * 服务器负载保护函数，本方法目前不支持window系统
     * 最大负载不要超过3*N核，例如有16核（含8核超线程）则 16*3=48
     * @see http://php.net/manual/en/function.sys-getloadavg.php
     */
    public static function _load_protection($max_load_avg=24)
    {
        global $dir_log,$dir_wwwroot;
        if ( !function_exists('sys_getloadavg') )
        {
            return false;
        }
    
        $load = sys_getloadavg();
    
        if ( !isset($load[0]) )
        {
            return false;
        }
    
        if ( $load[0] <= $max_load_avg )
        {
            // 未超过负载，则跳出
            return false;
        }
    
        $msg_tpl = "[%s] HOST:%s LOAD:%s ARGV/URI:%s\n";
        $time = @date(DATE_RFC2822);
        $host = php_uname('n');
        $load = sprintf('%.2f', $load[0]);
        if ( php_sapi_name() == "cli" || empty($_SERVER['PHP_SELF']) )
        {
            $argv_or_uri = implode(',', $argv);
        }
        else
        {
            $argv_or_uri = $_SERVER['REQUEST_URI'];
        }
    
        $msg = sprintf($msg_tpl, $time, $host, $load, $argv_or_uri);
    
        if ( is_dir(LOG_PATH) )
        {
            file_put_contents( LOG_PATH."php-server-overload.log", $msg, FILE_APPEND );
        }
    
        # exit with 500 page
        header("HTTP/1.1 500 Internal Server Error");
        header("Expires: " . gmdate("D, d M Y H:i:s", time()-99999) . " GMT");
        header("Cache-Control: private");
        header("Pragma: no-cache");
        require_once getFile("server_overload", "msg");
        exit();
    }
}