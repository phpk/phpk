<?php
/**
 * PHPK
 *
 * 控制器基类，controller和hook都继承自该类
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */
class Response extends Base
{
    /**
     * 构造方法
     */
    public function __construct ()
    {
        
    }
    /**
     *	简单的 AJAX输出
     *  使用方法：$this->ajaxReturn()
     * 	@param  int     $status  0:错误信息|1:正确信息
     * 	@param  string  $message  显示的信息
     * 	@param  array   $data    传输的信息
     * 	@param  array   $type    返回数据类型，json|xml|php
     *  @return object
     */
    public function ajaxReturn($status, $message = '', $data = array(), $type = 'json') {
        $return_data = array('status' => $status, 'message' => $message, 'data' => $data);
        $type = strtolower($type);
        if ($type == 'json') {
            exit(json_encode($return_data));
        } elseif ($type == 'xml') {
            $xml = '<?xml version="1.0" encoding="utf-8"?>';
            $xml .= '<return>';
            $xml .= '<status>' .$status. '</status>';
            $xml .= '<message>' .$message. '</message>';
            $xml .= '<data>' .serialize($data). '</data>';
            $xml .= '</return>';
            exit($xml);
        } elseif ($type == 'php') {
            exit($return_data);
        } else {
    
        }
    }
    /**
     * 输出返回数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回类型 JSON XML PHP
     * @param integer $code HTTP状态
     * @return void
     */
    protected function response($data,$type='json',$code=200) {
        $this->sendHttpStatus($code);
        exit($this->encodeData($data,strtolower($type)));
    }
    /**
     *	返回404
     *  Controller中使用方法：$this->return404()
     *  @return
     */
    public function return404() {
        self::sendHttpStatus('404');
        return;
    }
    
    /**
     *	返回404
     *  Controller中使用方法：$this->return200()
     *  @return
     */
    public function return200() {
        self::sendHttpStatus('200');
        return;
    }
    
    /**
     *	返回500
     *  Controller中使用方法：$this->return500()
     *  @return
     */
    public function return500() {
        self::sendHttpStatus('500');
        return;
    }
    /**
     *	类加载-检测token值
     *  Controller中使用方法：$this->checkToken($ispost = true)
     *  @return
     */
    public function checkToken($ispost = true) {
        if ($ispost && !$this->isPost()) return false;
        if (cookie('phpk_token') !== $this->getToken()) return false;
        return true;
    }
    
    /**
     *	类加载-设置全局TOKEN，防止CSRF攻击
     *  Controller中使用方法：$this->setToken()
     *  @return
     */
    private function setToken() {
        if (!cookie('phpk_token')) {
            $str = substr(md5(time(). $_SERVER['HTTP_USER_AGENT']), 5, 8);
            cookie("phpk_token", $str);
        }
    }
    /**
     *	类加载-获取全局TOKEN，防止CSRF攻击
     *  Controller中使用方法：$this->getToken()
     *  @return
     */
    public function getToken() {
        return cookie('phpk_token');
    }
    /**
     * Request-判断是否为POST方法提交
     *  Controller中使用方法：$this->isPost()
     * @return bool
     */
    public function isPost() {
        return (strtolower($_SERVER['REQUEST_METHOD']) == 'post') ? true : false;
    }
    
    /**
     * Request-判断是否为GET方法提交
     *  Controller中使用方法：$this->isGet()
     * @return bool
     */
    public function isGet() {
        return (strtolower($_SERVER['REQUEST_METHOD']) == 'get') ? true : false;
    }
    
    /**
     * Request-判断是否为AJAX方式提交
     *  Controller中使用方法：$this->isAjax()
     * @return bool
     */
    public function isAjax() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
            return true;
        }
        if (I('is_ajax')){
            return true; //程序中自定义AJAX标识
        }
        return false;
    }
    /**
     * 获取类的名称
     * @return string
     */
    public function getClassName(){
        return get_class($this);
    }
    /**
     * 获取当前Action名称
     * @access protected
     */
    protected function getActionName() {
        if(empty($this->name)) {
            // 获取Action名称
            $this->name     =   substr(get_class($this),0,-10);
        }
        return $this->name;
    }
    /**
     * 设置页面输出的CONTENT_TYPE和编码
     * @access public
     * @param string $type content_type 类型对应的扩展名
     * @param string $charset 页面输出编码
     * @return void
     */
    public function setContentType($type, $charset=''){
        if(headers_sent()) return;
        if(empty($charset))  $charset = C('output.charset');
        $type = strtolower($type);
        if(isset($this->_types[$type])) //过滤content_type
            header('Content-Type: '.$this->_types[$type].'; charset='.$charset);
    }
    
    
    
    /**
     * 编码数据
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type 返回类型 JSON XML
     * @return void
     */
    protected function encodeData($data,$type='') {
        if(empty($data))  return '';
        if('json' == $type) {
            // 返回JSON数据格式到客户端 包含状态信息
            $data = json_encode($data);
        }elseif('xml' == $type){
            // 返回xml格式数据
            $data = self::xmlEncode($data);
        }elseif('php'==$type){
            $data = serialize($data);
        }// 默认直接输出
        $this->setContentType($type);
        header('Content-Length: ' . strlen($data));
        return $data;
    }
    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     */
    public static function xmlEncode($data, $root='phpk', $item='item', $attr='', $id='id', $encoding='utf-8') {
        if(is_array($attr)){
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr   = trim($attr);
        $attr   = empty($attr) ? '' : " {$attr}";
        $xml    = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
        $xml   .= "<{$root}{$attr}>";
        $xml   .= self::dataToXml($data, $item, $id);
        $xml   .= "</{$root}>";
        return $xml;
    }
    /**
     * 数据XML编码
     * @param mixed  $data 数据
     * @param string $item 数字索引时的节点名称
     * @param string $id   数字索引key转换为的属性名
     * @return string
     */
    public static function dataToXml($data, $item='item', $id='id') {
        $xml = $attr = '';
        foreach ($data as $key => $val) {
            if(is_numeric($key)){
                $id && $attr = " {$id}=\"{$key}\"";
                $key  = $item;
            }
            $xml    .=  "<{$key}{$attr}>";
            $xml    .=  (is_array($val) || is_object($val)) ? self::dataToXml($val, $item, $id) : $val;
            $xml    .=  "</{$key}>";
        }
        return $xml;
    }
    // 发送Http状态信息
    public static function sendHttpStatus($status) {
        static $_status = array(
                // Informational 1xx
                100 => 'Continue',
                101 => 'Switching Protocols',
                // Success 2xx
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                // Redirection 3xx
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Moved Temporarily ',  // 1.1
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                // 306 is deprecated but reserved
                307 => 'Temporary Redirect',
                // Client Error 4xx
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',
                // Server Error 5xx
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported',
                509 => 'Bandwidth Limit Exceeded'
        );
        if(isset($_status[$code])) {
            header('HTTP/1.1 '.$code.' '.$_status[$code]);
            // 确保FastCGI模式下正常
            header('Status:'.$code.' '.$_status[$code]);
        }
    }
    /**
     * 获取当前请求的Accept头信息
     * @return string
     */
    protected function getAcceptType(){
        $type = array(
                'html'  =>  'text/html,application/xhtml+xml,*/*',
                'xml'   =>  'application/xml,text/xml,application/x-xml',
                'json'  =>  'application/json,text/x-json,application/jsonrequest,text/json',
                'js'    =>  'text/javascript,application/javascript,application/x-javascript',
                'css'   =>  'text/css',
                'rss'   =>  'application/rss+xml',
                'yaml'  =>  'application/x-yaml,text/yaml',
                'atom'  =>  'application/atom+xml',
                'pdf'   =>  'application/pdf',
                'text'  =>  'text/plain',
                'png'   =>  'image/png',
                'jpg'   =>  'image/jpg,image/jpeg,image/pjpeg',
                'gif'   =>  'image/gif',
                'csv'   =>  'text/csv'
        );
    
        foreach($type as $key=>$val){
            $array   =  explode(',',$val);
            foreach($array as $k=>$v){
                if(stristr($_SERVER['HTTP_ACCEPT'], $v)) {
                    return $key;
                }
            }
        }
        return false;
    }
    /**
     * 获取PHP在CLI运行模式下的参数
     *
     * @access public
     * @param string $string 参数键值, 注:不支持数组
     * @param string $defaultParam 默认参数
     * @return string
     */
    public static function getCliParams($string , $defaultParam = null) {
    
        //参数分析
        if (!isset($_SERVER['argv'][$string])) {
            return is_null($defaultParam) ? '' : htmlspecialchars(trim($defaultParam));
        }
    
        $cliParams = htmlspecialchars(trim($_SERVER['argv'][$string]));
        return ($cliParams) ? $cliParams : (is_null($defaultParam) ? '' : htmlspecialchars(trim($defaultParam)));
    }
}