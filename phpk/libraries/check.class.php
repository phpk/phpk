<?php
/**
 * PHPK
 *
 * 字符串检查类
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 *
 */
defined('IN_ROOT') || exit('Access Denied');
class Check extends Base
{
    /**
     *	数据基础验证-检测字符串长度
     *  规则结构:
     *  array(
     *   $data数组的key|验证的方法名称(例如isEmail)|验证错误后的提示|参数
     *  )
     *  例子：
     *		array(
     *		'email|email|邮箱地址不正确',
     *		'email|length|长度在0-111|0|50',
     *		'phone|phone|手机号码格式不正确'
     *		)
     * 	@param  array $data 数据
     * 	@param  array $rule 规则模型
     *  @return bool
     */
    public static function validate($data, $rule) {
        if (!is_array($data) || !is_array($rule)) return false;
        foreach ($rule as $val) {
            $temp = explode('|', $val);
            $params = $temp;
            unset($params[0], $params[1], $params[2]);
            array_unshift($params, $data[$temp[0]]);
            $r = call_user_func_array(array($this, 'is'.ucfirst($temp[1])), $params);
            if (!$r) return $temp[2];
        }
        return true;
    }
    
    /**
     *	数据基础验证-检测字符串长度
     * 	@param  string $value 需要验证的值
     * 	@param  int    $min   字符串最小长度
     * 	@param  int    $max   字符串最大长度
     *  @return bool
     */
    public static function isLength($value, $min = 0, $max= 0) {
        $value = trim($value);
        if ($min != 0 && strlen($value) < $min) return false;
        if ($max != 0 && strlen($value) > $max) return false;
        return true;
    }
    
    /**
     *	数据基础验证-是否必须填写的参数
     *  Controller中使用方法：$this->controller->is_require($value)
     * 	@param  string $value 需要验证的值
     *  @return bool
     */
    public static function isRequire($value) {
        return preg_match('/.+/', trim($value));
    }
    
    /**
     *	数据基础验证-是否是空字符串
     *  Controller中使用方法：$this->controller->is_empty($value)
     * 	@param  string $value 需要验证的值
     *  @return bool
     */
    public static function isEmpty($value) {
        if (empty($value) || $value=="") return true;
        return false;
    }
    
    /**
     *	数据基础验证-检测数组，数组为空时候也返回FALSH
     *  Controller中使用方法：$this->controller->is_arr($value)
     * 	@param  string $value 需要验证的值
     *  @return bool
     */
    public static function isArr($value) {
        if (!is_array($value) || empty($value)) return false;
        return true;
    }
    /**
     * 正则表达式验证email格式
     *
     * @param string $str            
     * @return boolean
     */
    public static function isEmail ($str)
    {
        return preg_match(
                '#^[a-z0-9]+([._\-\+]*[a-z0-9]+)*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+\.)+[a-z0-9]+$#is', 
                trim($str)) ? true : false;
    }
    
    /**
     * 正则表达式验证网址
     *
     * @param string $str            
     * @return boolean
     */
    public static function isUrl ($str)
    {
        return preg_match(
                '#(http|https|ftp|ftps)://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?#i', 
                $str) ? true : false;
    }
    
    /**
     * 验证字符串中是否含有非法字符
     *
     * @param string $str            
     * @return boolean
     */
    public static function isInvalidStr ($str)
    {
        return preg_match('#[!#$%^&*(){}~`"\';:?+=<>/\[\]]+#', $str) ? true : false;
    }
    
    /**
     * 用正则表达式验证邮证编码
     *
     * @param int $num            
     * @return boolean
     */
    public static function isZip ($value)
    {
        $num = intval($num);
        return preg_match('#^[1-9][0-9]{5}$#', trim($value)) ? true : false;
    }
    /**
     *	数据基础验证-是否是数字类型
     * 	@param  string $value 需要验证的值
     *  @return bool
     */
    public function isNumber($value) {
        return preg_match('/\d+$/', trim($value)) ? true : false;
    }
    /**
     * 正则表达式验证身份证号码
     *
     * @param integer $num            
     * @return boolean
     */
    public static function isCard ($num)
    {
        return preg_match('#^[\d]{15}$|^[\d]{18}$#', $num) ? true : false;
    }
    
    /**
     * 正则表达式验证IP地址, 注:仅限IPv4
     *
     * @param string $str            
     * @return boolean
     */
    public static function isIp ($str)
    {
        if (! preg_match('#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $str)) {
            return false;
        }
        
        $ipArray = explode('.', $str);
        
        // 真实的ip地址每个数字不能大于255（0-255）
        return ($ipArray[0] <= 255 && $ipArray[1] <= 255 && $ipArray[2] <= 255 &&
                 $ipArray[3] <= 255) ? true : false;
    }
    
    /**
     * 用正则表达式验证出版物的ISBN号
     *
     * @param string $str            
     * @return boolean
     */
    public static function isBookIsbn ($str)
    {
        return preg_match('#^978[\d]{10}$|^978-[\d]{10}$#', $str) ? true : false;
    }
    
    /**
     * 用正则表达式验证手机号码(中国大陆区)
     * 
     * @param int $num            
     * @return boolean
     */
    public static function isMobile ($num)
    {
        $num = intval($num);
        return preg_match('#^(13|15|18)[0-9]{9}$#', $num) ? true : false;
    }
    
    /**
     * 用正则表达式验证电话号码(中国大陆区) 匹配形式如 0511-4405222 或 021-87888822
     *
     * @param string $str            
     * @return boolen
     */
    public static function isTel ($str)
    {
        return preg_match('#^\d{3}-\d{7,8}|\d{4}-\d{7,8}$#', $str) ? true : false;
    }
    
    /**
     * 验证QQ号是否合法
     *
     * @param int $num            
     * @return boolen
     */
    public static function isQQ ($num)
    {
        $num = intval($num);
        return preg_match('#^[1-9][0-9]{4,10}$#', $num) ? true : false;
    }
    
    /**
     * 验证颜色代码是否合法
     *
     * @param string $str            
     * @return boolen
     */
    public static function isColorCode ($str)
    {
        return preg_match('#^\#[0-9a-f]{6}$#i', $str) ? true : false;
    }
    /**
     *	是否是英文字母
     * 	@param  string $value 需要验证的值
     *  @return bool
     */
    public static function isEnglish($value) {
        return preg_match('/^[A-Za-z]+$/', trim($value));
    }
    
    /**
     *	是否是中文
     * 	@param  string $value 需要验证的值
     *  @return bool
     */
    public static function isChinese($value) {
        return preg_match("/^([\xE4-\xE9][\x80-\xBF][\x80-\xBF])+$/", trim($value));
    }
    /**
     * 检查对象中是否有可调用函数
     * @param string $object
     * @param string $method
     * @return bool
     */
    public static function isMethod($object, $method) {
        $method = strtolower ( $method );
        return method_exists($object, $method) && is_callable (array($object, $method));
    }
    
    /**
     * 检查是否是安全的账号
     * @param string $value
     * @return bool
     */
    public static function isSafeAccount($value) {
        return preg_match ("/^[a-zA-Z]{1}[a-zA-Z0-9_\.]{3,31}$/", $value);
    }
    
    /**
     * 检查是否是安全的昵称
     *  Controller中使用方法：$this->controller->is_safe_nickname()
     * @param string $value
     * @return bool
     */
    public static function isSafeNickname($value) {
        return preg_match ("/^[-\x{4e00}-\x{9fa5}a-zA-Z0-9_\.]{2,10}$/u", $value);
    }
    
    /**
     * 检查是否是安全的密码
     * @param string $str
     * @return bool
     */
    public static function isSafePassword($str) {
        if (preg_match('/[\x80-\xff]./', $str) || preg_match('/\'|"|\"/', $str) || strlen($str) < 6 || strlen($str) > 20 ){
            return false;
        }
        return true;
    }
    
    /**
     * 检查是否是正确的标识符
     * 1. 以字母或下划线开始，后面跟着任何字母，数字或下划线。
     **/
    public static function isIdentifier($value) {
        return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]+$/', trim($value));
    }
    /**
     * 判断字符串是否是UTF-8编码
     *
     * @param string $str
     * @return bool
     */
    public static function IsUtf8($str) {
        return preg_match ('%^(?:[\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*$%xs', $str );
    }
}