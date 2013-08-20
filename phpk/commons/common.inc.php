<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * phpk系统获取文件
 *
 * @param string $fileName            
 * @param string $ftype            
 * @return string boolean
 */
function getFile ($fileName, $ftype = 'libs')
{
    static $_files = array();
    // halt($fileName);
    $key = $fileName . $ftype;
    if (! isset($_files[$key])) {
        $driveMap = array('db','cache','crypt','session','auth','http');
        if(!in_array($ftype, $driveMap)){
            if ($ftype == 'libs') {
                $fileArr = parsePhpkFile($fileName, "extensions" . DS . "classes");
                $fileName = $fileArr[0];
                $basePath = $fileArr[1];
                $sysPath = PHPK_PATH . 'libraries' . DS;
                $ext = ".class.php";
            }  elseif ($ftype == 'helpers') {
                $fileArr = parsePhpkFile($fileName, "extensions" . DS . "functions");
                $fileName = $fileArr[0];
                $basePath = $fileArr[1];
                $sysPath = PHPK_PATH . 'functions' . DS;
                $ext = ".func.php";
            } elseif ($ftype == 'plugins') {
                $fileArr = parsePhpkFile($fileName, "extensions" . DS . "plugins");
                $fileName = $fileArr[0];
                $basePath = $fileArr[1];
                $sysPath = ROOT . 'plugins' . DS . $fileName . DS;
                $ext = "Plugin.class.php";
            } elseif ($ftype == 'controller') {
                $fileArr = parsePhpkFile($fileName, "controllers");
                $fileName = $fileArr[0];
                $basePath = $fileArr[1];
                $sysPath = $basePath;
                $ext = "Controller.class.php";
            }  elseif ($ftype == 'model') {
                $fileArr = parsePhpkFile($fileName, "models");
                $fileName = $fileArr[0];
                $basePath = $fileArr[1];
                $sysPath = $basePath;
                $ext = "Model.class.php";
            
            }
            elseif ($ftype == 'hooks') {
                $fileArr = parsePhpkFile($fileName, "hooks");
                $fileName = $fileArr[0];
                $basePath = $fileArr[1];
                $sysPath = PHPK_PATH . 'hooks' . DS;
                $ext = "Hook.class.php";
            }elseif ($ftype == 'msg') {
                $basePath = APP_PATH . "views" . DS . "errors" . DS;
                $sysPath = PHPK_PATH . "sources" . DS . "html" . DS;
                $ext = ".html";
            }else{
                halt($fileName. $ftype ."调用文件错误");
            }
            
        }else{
            $fileArr = parsePhpkFile($fileName, "extensions" . DS . "drivers" . DS . $ftype);
            $fileName = $ftype . ucfirst($fileArr[0]);
            $basePath = $fileArr[1];
            $sysPath = PHPK_PATH . 'drivers' . DS . $ftype . DS;
            $ext = ".class.php";
        }
        
        $appFile = $basePath . $fileName . $ext;
        $sysFile = $sysPath . $fileName . $ext;
        //var_dump($appFile);
        if (is_file($appFile)) {
            // var_dump($appFile);
            $_files[$key] = $appFile;
            // var_dump($appFile);
            return $appFile;
        } elseif (is_file($sysFile)) {
            $_files[$key] = $sysFile;
            return $sysFile;
        } else {
            return false;
        }
    } else {
        return $_files[$key];
    }
}

/**
 * 获取调用文件的类名
 *
 * @param string $class            
 * @return string
 */
function getClassName ($class, $ctype = '')
{
    $className = '';
    $sysName = array('controller','model','hook','plugin');
    $driveMap = array('db','cache','crypt','session','auth','http');
    if(in_array($ctype, $sysName)){
        $className = ucfirst($class) . ucfirst($ctype);
    }
    elseif(in_array($ctype, $driveMap)){
        $className = ucfirst($ctype) . ucfirst($class);
    }else{
        $className = ucfirst($class);
    }
    return $className;
}

/**
 * 解析系统文件格式
 *
 * @param string $fileName            
 * @param string $path            
 * @return array
 */
function parsePhpkFile ($fileName, $path)
{
    if (strpos($fileName, ":") === false) {
        $basePath = APP_PATH . $path . DS;
    } else {
        $cls = explode(":", $fileName);
        $basePath = ROOT . 'projects' . DS . $cls[0] . DS . $path . DS;
        $fileName = $cls[1];
    }
    return array(
            $fileName,
            $basePath
    );
}

/**
 * 将数组转为字符串表示形式
 *
 * @param array $array
 *            数组
 * @param int $level
 *            等级不要传参数
 * @return string
 */
function array_to_String ($array, $level = 0)
{
    if (! is_array($array)) {
        return "'" . $array . "'";
    }
    $space = ''; // 空白
    for ($i = 0; $i <= $level; $i ++) {
        $space .= "\t";
    }
    $arr = "Array\n$space(\n";
    $c = $space;
    foreach ($array as $k => $v) {
        $k = is_string($k) ? '\'' . addcslashes($k, '\'\\') . '\'' : $k;
        $v = ! is_array($v) &&
                 (! preg_match("/^\-?[1-9]\d*$/", $v) || strlen($v) > 12) ? '\'' .
                 addcslashes($v, '\'\\') . '\'' : $v;
        if (is_array($v)) {
            $arr .= "$c$k=>" . array_to_String($v, $level + 1);
        } else {
            $arr .= "$c$k=>$v";
        }
        $c = ",\n$space";
    }
    $arr .= "\n$space)";
    return $arr;
}

/**
 * 多维数组变一维数组
 *
 * @param unknown_type $array            
 * @return unknown
 */
function array_multi_to_single ($array)
{
    static $result_array;
    foreach ($array as $k => $v) {
        if (is_array($v)) {
            array_multi_to_single($v);
        } else {
            if ($v)
                $result_array[$k] = $v;
        }
    }
    return $result_array;
}

/**
 * 根据PHP各种类型变量生成唯一标识号
 *
 * @param mixed $mix
 *            变量
 * @return string
 */
function getUuid ($mix)
{
    if (is_object($mix) && function_exists('spl_object_hash')) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);
}

/**
 * 对内容进行安全处理
 *
 * @param string|array $string
 *            要处理的字符串或者数组
 * @param $string $flags
 *            指定标记
 */
function dhtmlspecialchars ($string, $flags = null)
{
    if (is_array($string)) {
        foreach ($string as $key => $val) {
            $string[$key] = dhtmlspecialchars($val, $flags);
        }
    } else {
        if ($flags === null) {
            $string = str_replace(
                    array(
                            '&',
                            '"',
                            '<',
                            '>'
                    ), array(
                            '&',
                            '"',
                            '<',
                            '>'
                    ), $string);
            if (strpos($string, '&#') !== false) {
                $string = preg_replace('/&((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', 
                        '&\\1', $string);
            }
        } else {
            if (PHP_VERSION < '5.4.0') {
                $string = htmlspecialchars($string, $flags);
            } else {
                if (strtolower(CHARSET) == 'utf-8') {
                    $charset = 'UTF-8';
                } else {
                    $charset = 'ISO-8859-1';
                }
                $string = htmlspecialchars($string, $flags, $charset);
            }
        }
    }
    return $string;
}

/**
 * 判断是否SSL协议
 *
 * @return boolean
 */
function is_ssl ()
{
    if (isset($_SERVER['HTTPS']) &&
             ('1' == $_SERVER['HTTPS'] ||
             'on' == strtolower($_SERVER['HTTPS']))) {
        return true;
    } elseif (isset($_SERVER['SERVER_PORT']) &&
             ('443' == $_SERVER['SERVER_PORT'])) {
        return true;
    }
    return false;
}

/**
 * 发送HTTP状态
 *
 * @param integer $code
 *            状态码
 * @return void
 */
function send_http_status ($code)
{
    static $_status = array(
            // Success 2xx
            200 => 'OK',
            // Redirection 3xx
            301 => 'Moved Permanently',
            302 => 'Moved Temporarily ', // 1.1
                                         // Client Error 4xx
            400 => 'Bad Request',
            403 => 'Forbidden',
            404 => 'Not Found',
            // Server Error 5xx
            500 => 'Internal Server Error',
            503 => 'Service Unavailable'
    );
    if (isset($_status[$code])) {
        header('HTTP/1.1 ' . $code . ' ' . $_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:' . $code . ' ' . $_status[$code]);
    }
}

/**
 * 设置时间
 *
 * @param int $timestamp            
 * @param string $timeformat
 *            return string
 */
function fDate ($timeformat = '', $timestamp = '')
{
    if (empty($timestamp)) {
        $timestamp = time();
    }
    if ($timeformat == "") {
        return date('Y-m-d H:i', $timestamp);
    } else {
        return date($timeformat, $timestamp);
    }
}

/**
 * 对数组或字符串进行转义处理，数据可以是字符串或数组及对象
 *
 * @param void $data            
 * @return type
 */
function addslashes_d ($data)
{
    if (is_string($data)) {
        return addslashes($data);
    }
    if (is_numeric($data)) {
        return $data;
    }
    if (is_array($data)) {
        $var = array();
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $var[$k] = addslashes_d($v);
                continue;
            } else {
                $var[$k] = addslashes($v);
            }
        }
        return $var;
    }
}

/**
 * 去除转义
 *
 * @param type $data            
 * @return type
 */
function stripslashes_d ($data)
{
    if (empty($data)) {
        return $data;
    } elseif (is_string($data)) {
        return stripslashes($data);
    } elseif (is_array($data)) {
        $var = array();
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $var[$k] = stripslashes_d($v);
                continue;
            } else {
                $var[$k] = stripslashes($v);
            }
        }
        return $var;
    }
}

/**
 * 去除代码中的空白和注释
 *
 * @param string $content
 *            代码内容
 * @return string
 */
function strip_whitespace ($content)
{
    $stripStr = '';
    // 分析php源码
    $tokens = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i ++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr .= $tokens[$i];
        } else {
            switch ($tokens[$i][0]) {
                // 过滤各种PHP注释
                case T_COMMENT:
                case T_DOC_COMMENT:
                    break;
                // 过滤空格
                case T_WHITESPACE:
                    if (! $last_space) {
                        $stripStr .= ' ';
                        $last_space = true;
                    }
                    break;
                case T_START_HEREDOC:
                    $stripStr .= "<<<PHPK\n";
                    break;
                case T_END_HEREDOC:
                    $stripStr .= "PHPK;\n";
                    for ($k = $i + 1; $k < $j; $k ++) {
                        if (is_string($tokens[$k]) && $tokens[$k] == ';') {
                            $i = $k;
                            break;
                        } else 
                            if ($tokens[$k][0] == T_CLOSE_TAG) {
                                break;
                            }
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}

/**
 * 判断网页能够打开
 *
 * @param string $url            
 * @return boolean
 */
function checkUrl ($url)
{
    $url = trim($url);
    if (empty($url)) {
        return false;
    }
    if (function_exists('curl_init')) {
        $curl = curl_init($url);
        // 不取回数据
        curl_setopt($curl, CURLOPT_NOBODY, true);
        // 发送请求
        $result = curl_exec($curl);
        $found = false;
        // 如果请求没有发送失败
        if ($result !== false) {
            // 再检查http响应码是否为200
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($statusCode == 200) {
                curl_close($curl);
                return true;
            }
        }
        curl_close($curl);
        return false;
    }
    
    if (function_exists('get_headers')) {
        $headeraar = get_headers($url);
        if (strpos($headeraar[0], 'HTTP/1.1 200') === 0) {
            return true;
        } else {
            return false;
        }
    }
    if (function_exists('exec')) {
        exec("ping -n 1 -w 50 $url", $pingResult);
        if (! in_array("请求超时。", $pingResult))         // 别漏了那个句号
        {
            return true;
        }
        unset($pingResult);
        return false;
    }
    if (function_exists('fsockopen')) {
        $url_arr = parse_url($url);
        
        if (! is_array($url_arr) || empty($url_arr)) {
            return false;
        }
        
        // 获取请求数据
        $host = $url_arr['host'];
        $path = $url_arr['path'] . "?" . $url_arr['query'];
        $port = isset($url_arr['port']) ? $url_arr['port'] : "80";
        
        // 连接服务器
        $fp = fsockopen($host, $port, $err_no, $err_str, 30);
        if (! $fp) {
            return false;
        }
        
        // 构造请求协议
        $request_str = "GET " . $path . "HTTP/1.1\r\n";
        $request_str .= "Host:" . $host . "\r\n";
        $request_str .= "Connection:Close\r\n\r\n";
        
        // 发送请求
        fwrite($fp, $request_str);
        $first_header = fgets($fp, 1024);
        fclose($fp);
        
        // 判断文件是否存在
        if (trim($first_header) == "") {
            return false;
        }
        if (! preg_match("/200/", $first_header)) {
            return false;
        }
        return true;
    } else {
        return false;
    }
}
/**
 * checkStr
 * 使用正则验证数据
 *
 * @param string $value
 *            要验证的数据
 * @param string $rule
 *            验证规则
 * @return mixed
 */
function checkStr ($value, $rule)
{
    $validate = array(
            'require' => '/.+/', // 必填
            'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', // 邮箱
            'url' => '/^http:\/\/[a-zA-Z0-9]+\.[a-zA-Z0-9]+[\/=\?%\-&_~`@\[\]\':+!]*([^<>\"\"])*$/', // 链接
            'currency' => '/^\d+(\.\d+)?$/', // 货币
            'number' => '/^\d+$/', // 数字
            'zip' => '/^[0-9]\d{5}$/', // 邮编
            'tel' => '/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/', // 电话
            'ip' => '#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', //ip
            'integer' => '/^[-\+]?\d+$/', // 整型
            'double' => '/^[-\+]?\d+(\.\d+)?$/', // 带小数点
            'english' => '/^[a-zA-Z]+$/', // 英文字母
            'chinese' => '/^[\x{4e00}-\x{9fa5}]+$/u', // 中文汉字
            'pinyin' => '/^[a-zA-Z0-9\-\_]+$/', // 拼音
            'username' => '/^(?!_)(?!.*?_$)[a-zA-Z0-9_\x{4e00}-\x{9fa5}]{1,15}$/u', // 用户名
            'en' => '/^[a-zA-Z0-9_\s\-]+$/', // 英文字符
            'cn' => '/^[\w\s\-\x{4e00}-\x{9fa5}]+$/u' // 中文字符
    );
    // 检查是否有内置的正则表达式
    if (isset($validate[strtolower($rule)]))
        $rule = $validate[strtolower($rule)];
    //var_dump(preg_match($rule, strval($value)));
    return preg_match($rule, strval($value)) === 1;
}
/**
 * 获取客户端IP
 *
 * @return string
 */
function getIp ()
{
    if (getenv("HTTP_CLIENT_IP") &&
             strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR") &&
                 strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } else {
            if (getenv("REMOTE_ADDR") &&
                     strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
                $ip = getenv("REMOTE_ADDR");
            } else {
                if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] &&
                         strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
                    $ip = $_SERVER['REMOTE_ADDR'];
                } else {
                    $ip = "unknown";
                }
            }
        }
    }
    return $ip;
}

/**
 * 自动转换字符集 支持数组转换
 *
 * @param string $fContents            
 * @param string $from            
 * @param string $to            
 * @return string
 */
function autoCharset ($fContents, $from = 'gbk', $to = 'utf-8')
{
    $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
    $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
    if (strtoupper($from) === strtoupper($to) || empty($fContents) ||
             (is_scalar($fContents) && ! is_string($fContents))) {
        // 如果编码相同或者非字符串标量则不转换
        return $fContents;
    }
    if (is_string($fContents)) {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    } elseif (is_array($fContents)) {
        foreach ($fContents as $key => $val) {
            $_key = autoCharset($key, $from, $to);
            $fContents[$_key] = autoCharset($val, $from, $to);
            if ($key != $_key)
                unset($fContents[$key]);
        }
        return $fContents;
    } else {
        return $fContents;
    }
}
?>
