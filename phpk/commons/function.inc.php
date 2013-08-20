<?php
defined('PHPK_PATH') || exit('Access Denied');

/**
 * PHPK
 *
 * 框架内置核心函数库
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 *
 */

/**
 * 打印各种类型的数据
 *
 * @return void
 */
function dump ()
{
    // if (DEBUG > 0) {
    $args = func_get_args(); // 获取多个参数
    if (count($args) < 1) {
        debug("<font color='red'>必须为dump()函数提供参数!");
        return;
    }
    
    echo '<div style="width:100%;text-align:left"><pre>';
    // 多个参数循环输出
    foreach ($args as $arg) {
        if (is_array($arg)) {
            print_r($arg);
            echo '<br>';
        } else 
            if (is_string($arg)) {
                echo $arg . '<br>';
            } else {
                var_dump($arg);
                echo '<br>';
            }
    }
    echo '</pre></div>';
    // }
}

/**
 * 显示提示信息操作
 *
 * 所显示的提示信息并非完全是错误信息。如：用户登陆时用户名或密码错误，可用本方法输出提示信息
 *
 * 注：显示提示信息的页面模板内容可以自定义. 方法：在项目视图目录中的error子目录中新建message.html文件,自定义该文件内容
 * 显示错误信息处模板标签为<!--{$message}-->
 *
 * 本方法支持URL的自动跳转，当显示时间有效期失效时则跳转到自定义网址，若跳转网址为空则函数不执行跳转功能，当自定义网址参数为-1时默认为:返回上一页。
 *
 * @access public
 * @param string $message
 *            所要显示的提示信息
 * @param string $gotoUrl
 *            所要跳转的自定义网址
 * @param int $limitTime
 *            显示信息的有效期,注:(单位:秒) 默认为3秒
 * @return void
 */
function msg ($message, $gotoUrl = null, $limitTime = 3)
{
    // 参数分析
    if (! $message) {
        return false;
    }
    
    // 当自定义跳转网址存在时
    if (! is_null($gotoUrl)) {
        $limitTime = 1000 * $limitTime;
        // 分析自定义网址是否为返回页
        if ($gotoUrl == - 1) {
            $gotoUrl = 'javascript:history.go(-1);';
            $message .= '<br/><a href="' . $gotoUrl .
                     '" target="_self">如果你的浏览器没反应,请点击这里...</a>';
        } else {
            // 防止网址过长，有换行引起跳转变不正确
            $gotoUrl = str_replace(array(
                    "\n",
                    "\r"
            ), '', $gotoUrl);
            $gotoUrl = U($gotoUrl);
            $message .= '<br/><a href="' . $gotoUrl .
                     '" target="_self">如果你的浏览器没反应,请点击这里...</a>';
        }
        $message .= '<script type="text/javascript">setTimeout(function(){location.href=\'' .
                 $gotoUrl . '\'}, ' . $limitTime . ');</script>';
    }
    require_once getFile("message", "msg");
    exit();
}

/**
 * trigger_error()的简化函数
 *
 * 用于显示错误信息. 若调试模式关闭时(即:DEBUG为0时)，则将错误信息并写入日志
 *
 * @access public
 * @param string $message
 *            所要显示的错误信息
 * @param string $level
 *            日志类型. 默认为Error. 参数：Warning, Error, Notice
 * @return void
 */
function halt ($message, $level = 'Error')
{
    
    // 参数分析
    if (empty($message)) {
        return false;
    }
    
    // 调试模式下优雅输出错误信息
    $trace = debug_backtrace();
    $sourceFile = $trace[0]['file'] . '(' . $trace[0]['line'] . ')';
    
    $traceString = '';
    foreach ($trace as $key => $t) {
        $traceString .= '#' . $key . ' ' . $t['file'] . '(' . $t['line'] . ')' .
                 $t['class'] . $t['type'] . $t['function'] . '(' .
                 implode('.', $t['args']) . ')<br/>';
    }
    
    // 加载,分析,并输出excepiton文件内容
    include_once getFile('halt', 'msg');
    
    if (defined("DEBUG") && DEBUG === 0) {
        // 写入程序运行日志
        Log::write($message, $level);
    }
    
    // 终止程序
    exit();
}

/**
 * 统一加载类库
 *
 * @param string $class            
 * @return object
 */
function L ($class, $ctype = 'libs', $param = '')
{
    if (empty($class)) {
        halt('加载类库名称为空');
    }
    
    // $fileName = R($class,$ctype);
    $fileName = getFile($class, $ctype);
    //var_dump($fileName);
    if (false !== strpos($class, ":")) {
        // $class = array_pop(explode(":",$class));
        $class = substr(strstr($class, ':'), 1);
    }
    //var_dump($fileName);
    if ($fileName) {
        // require_once $fileName;
        requireCache($fileName);
        $className = getClassName($class, $ctype);
        //var_dump($className);
        return factory($className,$param);
    } else {
        debug($class . $ctype . '类库文件不存在');
    }
}

/**
 * 获取model
 *
 * @param string $name            
 * @return object
 */
function M ($name = '')
{
    $class = empty($name) ? APP_CONTROL : $name;
    if(empty($class)){
        return false;
    }else{
        return L($class, 'model');
    }
    
}

/**
 * 获取db类
 *
 * @return object
 */
function db ()
{
    return factory('Model');
}

/**
 * 获取plugins
 *
 * @param string $name            
 * @return object
 */
function P ($name)
{
    if (empty($name)) {
        hlat('插件名称不能为空');
    }
    return L($name, 'plugins');
}


/**
 * 字符串加密解密
 *
 * @param string $str
 *            需要加密的字符串
 * @param int $operation
 *            0或者不设置为加密，1为解密
 * @param string $detype
 *            加密类型
 */
function crypto($str, $operation = 0, $detype = 'auth', $key = '')
{
    $security = C('system.security') ? C('system.security') : '$R%ffdsphpF#';
    $key = md5($key != '' ? $key : $security);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $cryptkey = substr(md5($keya . md5($keyb)), 0, 9);
    $crypt = L($detype, 'crypt');
    if ($operation == 0) {
        return $crypt::encrypt($str, $cryptkey);
    } else {
        return $crypt::decrypt($str, $cryptkey);
    }
}

/**
 * 返回唯一的实例(单例模式，调用函数时用)
 *
 * @access public
 * @param string $className
 *            要获取的对象的类名字
 * @param string $method
 *            方法名，如果为空则返回实例化对象
 * @param array $args
 *            调用参数
 * @return object 返回对象实例
 */
function getClass ($className, $method = '', $args = array())
{
    static $_object = array();
    // 参数分析
    
    if (! $className) {
        return false;
    }
    // $key = trim($className);
    $key = empty($args) ? $className . $method : $className . $method . getUuid($args);
    if (! isset($_object[$key])) {
        if (class_exists($className)) {
            
            if (empty($method)) {
                $_object[$key] = new $className();
            } else {
                $o = new $className();
                // var_dump($method);
                if (method_exists($o, $method)) {
                    if (! empty($args)) {
                        $_object[$key] = call_user_func_array(
                                array(
                                        &$o,
                                        $method
                                ), $args);
                    } else {
                        $_object[$key] = $o->$method();
                    }
                } else {
                    debug($className . $method . "调用的类方法不存在");
                }
            }
        } else {
            debug($className . "调用的类不存在");
        }
    }
    
    return $_object[$key];
}

/**
 * 根据$className返回一个实例化并静态存储的对象
 *
 * @access public
 * @param string $className
 *            要获取的对象的类名字
 * @param array|string $param
 *            给对象传入的参数
 * @return object 返回对象实例
 */
function factory ($className, $param = '')
{
    static $_object;
    if (! $className) {
        return false;
    }
    if(class_exists($className)){
        if (! isset($_object[$className])) {
            if (empty($param)) {
                $_object[$className] = new $className();
            } else {
                $_object[$className] = new $className($param);
            }
        }
        return $_object[$className];
    }else{
        halt($className.'调用的类不存在');
    }
    
}

/**
 * 加载外围文件,支持批量加载
 *
 * @param string $class            
 * @return object
 */
function R ($name, $ftype = 'other')
{
    // var_dump($name);
    if (! is_array($name)) {
        return requireCache($name, $ftype);
    } else {
        $return = array();
        foreach ($name as $v) {
            $r = requireCache($v, $ftype);
            if (is_array($r)) {
                $return[$v] = $r;
            }
        }
        return array_multi_to_single($return);
    }
}

/**
 * 优化的require_once
 *
 * @param string $filename
 *            文件地址
 * @return boolean
 */
function requireCache ($filename, $ftype = 'other')
{
    static $_importFiles = array();
    $key = $filename . $ftype;
    if (isset($_importFiles[$key])) {
        return $_importFiles[$key];
    } else {
        if ($ftype !== 'other') {
            $file_name = getFile($filename, $ftype);
        } else {
            $file_name = $filename;
        }
        // var_dump($file_name);
        if ($file_name && file_exists_case($file_name)) {
            
            $_importFiles[$key] = true;
            $return = require $file_name;
            // var_dump($return);
            if (is_array($return)) {
                return $return;
            } else {
                return true;
            }
        }
        return false;
    }
}

/**
 * 区分大小写的判断文件判断
 *
 * @param string $file
 *            需要判断的文件
 * @return boolean
 */
function file_exists_case ($file)
{
    // if(stream_resolve_include_path($file)){
    if (is_file($file)) {
        // windows环境下检测文件大小写
        if (IS_WIN) {
            if (basename(realpath($file)) != basename($file)) {
                return false;
            }
        }
        return true;
    }
    return false;
}

/**
 * 执行某个钩子
 *
 * @param string $name
 *            钩子名称
 * @param Mixed $params
 *            传人的参数
 * @return void
 */
function hook ($name, &$params = NULL, $method = '')
{
    if (false !== strpos($name, ',')) {
        $names = explode(",", $name);
        foreach ($names as $v) {
            L($v, 'hooks', $params);
        }
    } else {
        $hook = L($name, 'hooks', $params);
        //var_dump($method);
        if (! empty($method) && method_exists($hook, $method)) {
            $hook->$method();
        }
    }
    return false;
}

/**
 * 快速文件数据读取和保存 针对简单类型数据 字符串、数组
 *
 * @param string  $name     缓存名称，如果不带：则自动加上项目的名称，加上则指定项目
 * @param mixed   $value    缓存值
 * @param string  $compress 是否压缩，1默认压缩，0为不压缩
 * @return mixed
 */
function F ($name, $value = '', $compress = 1)
{
    static $_cache = array();
    if(strpos($name,":") === false){
        $filename = CACHE_PATH . APP_NAME . '_' . $name . '.php';
    }else{
        $nameArr = explode(":", $name);
        $filename = CACHE_PATH . $nameArr[0] . '_' . $nameArr[1] . '.php';
    }
    if ('' !== $value) {
        if (is_null($value)) {
            // 删除缓存
            return false !== strpos($name, '*') ? array_map("unlink", glob($filename)) : unlink($filename);
        } else {
            // 缓存数据
            $dir = dirname($filename);
            // 目录不存在则创建
            if (! is_dir($dir))
                mkdir($dir, 0755, true);
            $_cache[$name] = $value;
            $data = "<?php if(!defined('IN_ROOT'))exit;\n\treturn " . array_to_String($value) . ";?>";
            if($compress === 1){
                $data = strip_whitespace($data);
            }
            return file_put_contents($filename, $data);
        }
    }
    if (isset($_cache[$name]))
        return $_cache[$name];
        // 获取缓存数据
    if (is_file($filename)) {
        $value = include $filename;
        $_cache[$name] = $value;
    } else {
        $value = false;
    }
    return $value;
}

/**
 * 加载或设置系统配置
 *
 * @param string $n            
 * @param string $v            
 * @return array
 */
function C ($n = '', $v = '')
{
    $CONFIG = $GLOBALS['_configs'];
    static $_config = array();
    if (empty($n) && empty($v)) {
        return $CONFIG['system'];
    }
    if (empty($v)) {
        if (! isset($_config[$n])) {
            $setArr = explode(".", $n);
            $val = getConfigData($CONFIG,$setArr);
            $_config[$n] = $val;
            return $val;
        } else {
            return $_config[$n];
        }
    } else {
        $_config[$n] = $v;
        $setArr = explode(".", $n);
        $GLOBALS['_configs'] = setConfigData($CONFIG, $setArr, $value);
        return $v;
    }
}
/**
 * 获取基本配置数据,深度支持6级
 * @param array $data
 * @param array $setArr
 * @return Ambigous <boolean, unknown>
 */
function getConfigData($data,$setArr){
    $val = false;
    if (isset($setArr[0]) && ! isset($setArr[1]) && isset($data[$setArr[0]])) {
        $val = $data[$setArr[0]];
    }
    if (isset($setArr[1]) && ! isset($setArr[2]) && isset($data[$setArr[0]][$setArr[1]])) {
        $val = $data[$setArr[0]][$setArr[1]];
    }
    if (isset($setArr[2]) && ! isset($setArr[3]) && isset($data[$setArr[0]][$setArr[1]][$setArr[2]])) {
        $val = $data[$setArr[0]][$setArr[1]][$setArr[2]];
    }
    if (isset($setArr[3]) && ! isset($setArr[4])  && isset($data[$setArr[0]][$setArr[1]][$setArr[2]][$setArr[3]])) {
        $val = $data[$setArr[0]][$setArr[1]][$setArr[2]][$setArr[3]];
    }
    if (isset($setArr[4]) && ! isset($setArr[5])  && isset($data[$setArr[0]][$setArr[1]][$setArr[2]][$setArr[3]][$setArr[4]])) {
        $val = $data[$setArr[0]][$setArr[1]][$setArr[2]][$setArr[3]][$setArr[4]];
    }
    if (isset($setArr[5])  && isset($data[$setArr[0]][$setArr[1]][$setArr[2]][$setArr[3]][$setArr[4]][$setArr[5]])) {
        $val = $data[$setArr[0]][$setArr[1]][$setArr[2]][$setArr[3]][$setArr[4]][$setArr[5]];
    }
    return $val;
}
/**
 * 设置基本数据,深度支持6级
 * @param array $data
 * @param array $setArr
 * @param array $value
 * @return array
 */
function setConfigData($data, $setArr, $value){
    if (isset($setArr[0]) && ! isset($setArr[1])) {
        $data[$setArr[0]] = $value;
    }
    if (isset($setArr[1]) && ! isset($setArr[2])) {
        $data[$setArr[0]][$setArr[1]] = $value;
    }
    if (isset($setArr[2]) && ! isset($setArr[3])) {
        $data[$setArr[0]][$setArr[1]][$setArr[2]] = $value;
    }
    if (isset($setArr[3]) && ! isset($setArr[4])) {
        $data[$setArr[0]][$setArr[1]][$setArr[2]][$setArr[3]] = $value;
    }
    if (isset($setArr[4]) && ! isset($setArr[5])) {
        $data[$setArr[0]][$setArr[1]][$setArr[2]][$setArr[3]][$setArr[4]] = $value;
    }
    if (isset($setArr[5])) {
        $data[$setArr[0]][$setArr[1]][$setArr[2]][$setArr[3]][$setArr[4]][$setArr[5]] = $value;
    }
    return $data;
}
/**
 * 加载或设置应用配置，此函数用于额外配置项，设置会写入配置项
 * <code>
 * FC('auth.auth_on') 读取配置项目
 * FC('auth.auth_on',1) 写入配置项
 * FC(admin:auth.auth_on) 支持跨项目读取
 * </code>
 *
 * @param string $name
 * @param string $value
 * @return array
 */
function FC($name, $value = ''){
    static $_config = array();
    //防止重复读
    if (isset($_config[$name]) && '' === $value) {
        return $_config[$name];
    }
    if(strpos($name,".") !== false){
            $fileName = strstr($name, '.', true);
            $data = F($fileName);
            $keys = substr(str_replace($fileName, '', $name),1);
            $setArr = explode(".", $keys);
            if('' === $value) {
                if($data){
                    $return = getConfigData($data,$setArr);
                    $_config[$name] = $return;
                    return $return;
                }else{
                    return false;
                }
            }else{
                $data = setConfigData($data, $setArr, $value);
                $_config[$name] = $data;
                F($fileName,$data);
                return $value;
                
            }
            
    }else{
            if('' === $value){
                $return = F($name);
                $_config[$name] = $return;
                return $return;
            }else{
                F($name,$value);
                $_config[$name] = $value;
                return $value;
            }
    }
    
    
}
/**
 * 设置和获取统计数据
 * 使用方法:
 * <code>
 * N('db',1); // 记录数据库操作次数
 * N('read',1); // 记录读取次数
 * echo N('db'); // 获取当前页面数据库的所有操作次数
 * echo N('read'); // 获取当前页面读取次数
 * </code>
 *
 * @param string $key
 *            标识位置
 * @param integer $step
 *            步进值
 * @return mixed
 */
function N ($key, $step = 0, $save = false)
{
    static $_num = array();
    if (! isset($_num[$key])) {
        $_num[$key] = (false !== $save) ? cache('N_' . $key) : 0;
    }
    if (empty($step))
        return $_num[$key];
    else
        $_num[$key] = $_num[$key] + (int) $step;
    if (false !== $save) { // 保存结果
        cache('N_' . $key, $_num[$key], $save);
    }
}
/**
 * 一个简单的计数器
 * @return number
 */
function counter() {
    $counter = 1;
    return function() use(&$counter) {return $counter ++;};
}
/**
 * 缓存管理
 *
 * @param mixed $name
 *            缓存名称，如果为数组表示进行缓存设置
 * @param mixed $value
 *            缓存值
 * @param mixed $options
 *            缓存参数
 * @return mixed
 */
function cache ($name, $value = '', $options = null)
{
    static $cache = '';
    if (is_array($options)) {
        // 缓存操作的同时初始化
        $type = isset($options['type']) ? $options['type'] : '';
        $cache = Cache::getInstance($type, $options);
    } elseif (is_array($name)) { // 缓存初始化
        $type = isset($name['type']) ? $name['type'] : '';
        $cache = Cache::getInstance($type, $name);
        return $cache;
    } elseif (empty($cache)) { // 自动初始化
        $cache = Cache::getInstance();
    }
    if (! $cache) {
        return false;
    }
    if ('' === $value) { // 获取缓存
        return $cache->get($name);
    } elseif (is_null($value)) { // 删除缓存
        return $cache->rm($name);
    } else { // 缓存数据
        if (is_array($options)) {
            $expire = isset($options['expire']) ? $options['expire'] : NULL;
        } else {
            $expire = is_numeric($options) ? $options : NULL;
        }
        return $cache->set($name, $value, $expire);
    }
}

/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 *
 * @param string $name
 *            变量的名称 支持指定类型
 * @param mixed $default
 *            不存在的时候默认值
 * @param mixed $filter
 *            参数过滤方法
 * @return mixed
 */
function I ($name, $default = '', $filter = null)
{
    if (strpos($name, '.')) { // 指定参数来源
        list ($method, $name) = explode('.', $name);
    } else { // 默认为自动判
        $method = 'param';
    }
    switch (strtolower($method)) {
        case 'get':
            $input = & $_GET;
            break;
        case 'post':
            $input = & $_POST;
            break;
        case 'put':
            parse_str(file_get_contents('php://input'), $input);
            break;
        case 'param':
            switch ($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input = $_POST;
                    break;
                case 'PUT':
                    parse_str(file_get_contents('php://input'), $input);
                    break;
                default:
                    $input = $_GET;
            }
            if (C('url.params') && isset($_GET[C('url.params')])) {
                $input = array_merge($input, $_GET[C('url.params')]);
            }
            break;
        case 'request':
            $input = & $_REQUEST;
            break;
        case 'session':
            $input = & $_SESSION;
            break;
        case 'cookie':
            $input = & $_COOKIE;
            break;
        case 'server':
            $input = & $_SERVER;
            break;
        case 'globals':
            $input = & $GLOBALS;
            break;
        default:
            return NULL;
    }
    
    if (empty($name)) { // 获取全部变量
        $data = $input;
    } elseif (isset($input[$name])) { // 取值操作
        $data = $input[$name];
        $filters = isset($filter) ? $filter : "dhtmlspecialchars";
        $data = $filters($data); // 参数过滤
    } else { // 变量默认值
        $data = isset($default) ? $default : NULL;
    }
    return $data;
}

/**
 * $_GET简写
 *
 * @param string $name            
 * @param string $val            
 * @return Ambigous <mixed, NULL, unknown>
 */
function get ($name, $val = '')
{
    return I("get." . $name, $val);
}

/**
 * $_POST简写
 *
 * @param string $name            
 * @param string $val            
 * @return Ambigous <mixed, NULL, unknown>
 */
function post ($name, $val = '')
{
    return I("post." . $name, $val);
}

/**
 * 返回语言
 *
 * @param string $key            
 */
function lang ($key)
{
    $lang = $GLOBALS['_languages'];
    if (isset($lang[$key])) {
        return $lang[$key];
    } else {
        return '';
    }
}



/**
 * URL组装 支持不同URL模式
 *
 * @param string $url
 *            URL表达式，格式：'{url app/index/login/username=test&password=test2}'
 * @param boolean $redirect
 *            是否跳转，如果设置为1则表示跳转到该URL地址
 * @return string
 */
function U ($url = '', $redirect = 0)
{
    $return = '';
    if (empty($url)) {
        return WEB_URL;
    }
    if(false === strpos($url,'http://') || false === strpos($url,'https://')){
        $urlType = C("route.url_type");
        
        $urlArr = explode("/", $url);
        if (count($urlArr) == 4 && empty($urlArr[0])) {
            array_shift($urlArr);
        }
        
        if (count($urlArr) == 3) {
            if (false === strpos($urlArr[2], "=")) {
                $urlArr[3] = '';
            } else {
                array_unshift($urlArr, APP_NAME);
            }
        }
        if (count($urlArr) == 2) {
            // {url /login}
            if (empty($urlArr[0])) {
                $urlArr[0] = APP_CONTROL;
            }
            // {url admin/}
            if (empty($urlArr[1])) {
                $urlArr[1] = APP_CONTROL;
                $urlArr[2] = APP_ACTION;
                $urlArr[3] = '';
            } else {
                if (false === strpos($urlArr[1], "=")) {
                    array_unshift($urlArr, APP_NAME);
                    $urlArr[3] = '';
                } else {
                    array_unshift($urlArr, APP_CONTROL);
                    array_unshift($urlArr, APP_NAME);
                }
            }
        }
        // {url admin}
        if (count($urlArr) == 1) {
            array_unshift($urlArr, APP_ACTION);
            array_unshift($urlArr, APP_CONTROL);
            array_unshift($urlArr, APP_NAME);
        }
        $app = $urlArr[0];
        $control = $urlArr[1];
        $action = $urlArr[2];
        
        $space = ($urlType == 1) ? '&' : '/';
        $param = empty($urlArr[3]) ? "" : ($space . $urlArr[3]);
        $param = (substr($param, - 1, 1) == "=") ? ($param . $space) : $param;
        $paramTo = str_replace(array("&","=","/amp;"), "/", $param);
        $strTo = 'app/' . $app . '/c/' . $control . '/a/' . $action . $paramTo;
        $strTo = str_replace("//", "/", $strTo);
        if ($urlType == 1) {
            $return = WEB_URL . 'index.php?app=' . $app . '&c=' . $control . '&a=' . $action . $param;
        } elseif ($urlType == 2) {      
            $return = WEB_URL . 'index.php/' . $strTo;
        } elseif ($urlType == 3) {
            $return = WEB_URL . $strTo;
        } elseif ($urlType == 4) {
            $strTo = str_replace('/', '_', $strTo);
            $return = WEB_URL . $strTo;
        } else {
            $return = WEB_URL . 'index.php?app=' . $app . '&c=' . $control . '&a=' . $action . $param;
        }
    }
    else{
        $return = $url;
    }
    
    // var_dump($return);
    if ($redirect === 0) {
        return $return;
    } else {
        go($return);
    }
}

/**
 * 记录和统计时间（微秒）和内存使用情况
 * 使用方法:
 * <code>
 * G('begin'); // 记录开始标记位
 * // ... 区间运行代码
 * G('end'); // 记录结束标签位
 * echo G('begin','end',6); // 统计区间运行时间 精确到小数后6位
 * echo G('begin','end','m'); // 统计区间内存使用情况
 * 如果end标记位没有定义，则会自动以当前作为标记位
 * 其中统计内存使用需要 MEMORY_LOG 常量为true才有效
 * </code>
 *
 * @param string $start
 *            开始标签
 * @param string $end
 *            结束标签
 * @param integer|string $dec
 *            小数位或者m
 * @return mixed
 */
function G ($start, $end = '', $dec = 4)
{
    static $_info = array();
    static $_mem = array();
    if (is_float($end)) { // 记录时间
        $_info[$start] = $end;
    } elseif (! empty($end)) { // 统计时间和内存使用
        if (! isset($_info[$end]))
            $_info[$end] = microtime(TRUE);
        if (MEMORY_LOG && $dec == 'm') {
            if (! isset($_mem[$end]))
                $_mem[$end] = memory_get_usage();
            return number_format(($_mem[$end] - $_mem[$start]) / 1024);
        } else {
            return number_format(($_info[$end] - $_info[$start]), $dec);
        }
    } else { // 记录时间和内存使用
        $_info[$start] = microtime(TRUE);
        if (MEMORY_LOG)
            $_mem[$start] = memory_get_usage();
    }
}

/**
 * 记录调试日志
 *
 * @param string $message            
 * @return void
 */
function debug ($message = '')
{
    if (DEBUG > 0) {
        Debug::addMessage($message);
    }
}

/**
 * URL重定向
 *
 * @param string $url
 *            重定向的URL地址
 * @param integer $time
 *            重定向的等待时间（秒）
 * @param string $msg
 *            重定向前的提示信息
 * @return void
 */
function go ($url, $time = 0, $msg = '')
{
    // 多行URL地址支持
    $url = str_replace(array(
            "\n",
            "\r"
    ), '', $url);
    if (empty($msg))
        $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
    if (! headers_sent()) {
        // redirect
        if (0 === $time) {
            header('Location: ' . $url);
        } else {
            header("refresh:{$time};url={$url}");
            echo ($msg);
        }
        exit();
    } else {
        $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
        if ($time != 0)
            $str .= $msg;
        exit($str);
    }
}

/**
 * session管理函数
 *
 * @param string|array $name
 *            session名称 如果为数组则表示进行session设置
 * @param mixed $value
 *            session值
 * @return mixed
 */
function session ($name, $value = '')
{
    $prefix = C('session.prefix');
    if (is_array($name)) { // session初始化 在session_start 之前调用
        if (isset($name['prefix']))
            C('session.prefix', $name['prefix']);
        if (C('session.id') && isset($_REQUEST[C('session.id')])) {
            session_id($_REQUEST[C('session.id')]);
        } elseif (isset($name['id'])) {
            session_id($name['id']);
        }
        ini_set('session.auto_start', 0);
        if (isset($name['name']))
            session_name($name['name']);
        if (isset($name['path']))
            session_save_path($name['path']);
        if (isset($name['domain']))
            ini_set('session.cookie_domain', $name['domain']);
        if (isset($name['expire']))
            ini_set('session.gc_maxlifetime', $name['expire']);
        if (isset($name['use_trans_sid']))
            ini_set('session.use_trans_sid', $name['use_trans_sid'] ? 1 : 0);
        if (isset($name['use_cookies']))
            ini_set('session.use_cookies', $name['use_cookies'] ? 1 : 0);
        if (isset($name['cache_limiter']))
            session_cache_limiter($name['cache_limiter']);
        if (isset($name['cache_expire']))
            session_cache_expire($name['cache_expire']);
        if (isset($name['type']))
            C('session.type', $name['type']);
        if (C('session.type')) { // 读取session驱动
            $class = 'session' . ucwords(strtolower(C('session.type')));
            // 检查驱动类
            if (requireCache(
                    PHPK_PATH . 'core' . DS . 'session' . DS . $class .
                             '.class.php')) {
                $className = ucfirst($class);
                $hander = new $class();
                $hander->execute();
            } else {
                // 类没有定义
                hlt('类没有定义: ' . $class);
            }
        }
        // 启动session
        if (C('session.auto_start'))
            session_start();
    } elseif ('' === $value) {
        if (0 === strpos($name, '[')) { // session 操作
            if ('[pause]' == $name) { // 暂停session
                session_write_close();
            } elseif ('[start]' == $name) { // 启动session
                session_start();
            } elseif ('[destroy]' == $name) { // 销毁session
                $_SESSION = array();
                session_unset();
                session_destroy();
            } elseif ('[regenerate]' == $name) { // 重新生成id
                session_regenerate_id();
            }
        } elseif (0 === strpos($name, '?')) { // 检查session
            $name = substr($name, 1);
            if ($prefix) {
                return isset($_SESSION[$prefix][$name]);
            } else {
                return isset($_SESSION[$name]);
            }
        } elseif (is_null($name)) { // 清空session
            if ($prefix) {
                unset($_SESSION[$prefix]);
            } else {
                $_SESSION = array();
            }
        } elseif ($prefix) { // 获取session
            return isset($_SESSION[$prefix][$name]) ? $_SESSION[$prefix][$name] : null;
        } else {
            return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
        }
    } elseif (is_null($value)) { // 删除session
        if ($prefix) {
            unset($_SESSION[$prefix][$name]);
        } else {
            unset($_SESSION[$name]);
        }
    } else { // 设置session
        if ($prefix) {
            if (! is_array($_SESSION[$prefix])) {
                $_SESSION[$prefix] = array();
            }
            $_SESSION[$prefix][$name] = $value;
        } else {
            $_SESSION[$name] = $value;
        }
    }
}

/**
 * Cookie 设置、获取、删除
 *
 * @param string $name
 *            cookie名称
 * @param mixed $value
 *            cookie值
 * @param mixed $options
 *            cookie参数
 * @return mixed
 */
function cookie ($name, $value = '', $option = null)
{
    // 默认设置
    $config = array(
            'prefix' => C('cookie.prefix'), // cookie 名称前缀
            'expire' => C('cookie.expire'), // cookie 保存时间
            'path' => C('cookie.path'), // cookie 保存路径
            'domain' => C('cookie.domain') // cookie 有效域名
    );
    // 参数设置(会覆盖黙认设置)
    if (! is_null($option)) {
        if (is_numeric($option))
            $option = array(
                    'expire' => $option
            );
        elseif (is_string($option))
            parse_str($option, $option);
        $config = array_merge($config, array_change_key_case($option));
    }
    // 清除指定前缀的所有cookie
    if (is_null($name)) {
        if (empty($_COOKIE))
            return;
            // 要删除的cookie前缀，不指定则删除config设置的指定前缀
        $prefix = empty($value) ? $config['prefix'] : $value;
        if (! empty($prefix)) { // 如果前缀为空字符串将不作处理直接返回
            foreach ($_COOKIE as $key => $val) {
                if (0 === stripos($key, $prefix)) {
                    setcookie($key, '', time() - 3600, $config['path'], 
                            $config['domain']);
                    unset($_COOKIE[$key]);
                }
            }
        }
        return;
    }
    $name = $config['prefix'] . $name;
    
    if ('' === $value) {
        if (isset($_COOKIE[$name])) {
            $value = crypto($_COOKIE[$name], 1);
            // var_dump($_COOKIE[$name]);
            // 如果是数据
            if (0 === strpos($value, 'phpk:')) {
                $value = substr($value, 5);
                return array_map('urldecode', 
                        json_decode(
                                MAGIC_QUOTES_GPC ? stripslashes($value) : $value, 
                                true));
            } else {
                return $value;
            }
        } else {
            return null;
        }
    } else {
        if (is_null($value)) {
            setcookie($name, '', time() - 3600, $config['path'], 
                    $config['domain']);
            unset($_COOKIE[$name]); // 删除指定cookie
        } else {
            // 设置cookie
            if (is_array($value)) {
                $value = 'phpk:' .
                         json_encode(array_map('urlencode', $value));
            }
            $expire = ! empty($config['expire']) ? time() +
                     intval($config['expire']) * 60 : 0;
            $value = crypto($value);
            setcookie($name, $value, $expire, $config['path'], 
                    $config['domain']);
            $_COOKIE[$name] = $value;
        }
    }
}

?>