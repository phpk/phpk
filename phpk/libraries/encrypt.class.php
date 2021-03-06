<?php
/**
 * encrypt.class.php
 *
 * Form令牌加密,防止伪造表单提交及数据的加密,解密
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 */
defined('IN_ROOT') || exit('Access Denied');


class encrypt extends Base {

    /**
     * 时间周期
     *
     * @var integer
     */
    protected static $_liftTime = 7200;

    /**
     * 加密字符串(密钥)
     *
     * @var string
     */
    protected static $_key = 'your-secret-code';

    /**
     * config data
     *
     * @var array
     */
    protected $_config = array();


    /**
     * 构造函数
     *
     * @access public
     * @return boolean
     */
    public function __construct() {

        //set config infomation
        $this->_config = array(
        'hash'      => 'sha1',
        'xor'       => false,
        'mcrypt'    => function_exists('mcrypt_encrypt') ? true : false,
        'noise'     => true,
        'cipher'    => MCRYPT_RIJNDAEL_256,
        'mode'      => MCRYPT_MODE_ECB
        );

        return true;
    }

    /**
     * 设置或获取配置参数($_config)信息
     *
     * @access public
     * @param mixed $key 键值
     * @param mixed $value 参数值
     * @return mixed
     */
    public function config($key = null, $value = null) {

        if (is_null($key)) {
            return $this->_config;
        }

        if (is_array($key)) {
            $this->_config = $key + $this->_config;
            return $this;
        }

        if (is_null($value)) {
            return $this->_config[$key];
        }

        $this->_config[$key] = $value;
    }

    /**
     * 加密
     *
     * @access public
     * @param string $str 待加密的字符串
     * @param string $key 密钥
     * @return string
     */
    public function encode($str, $key = null) {

        if (is_null($key)) {
            $key = self::$_key;
        }

        if ($this->_config['xor']) {
            $str = $this->_xorEncode($str, $key);
        }

        if ($this->_config['mcrypt']) {
            $str = $this->_mcryptEncode($str, $key);
        }

        if ($this->_config['noise']) {
            $str = $this->_noise($str, $key);
        }

        return base64_encode($str);
    }

    /**
     * 解密
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    public function decode($str, $key = null) {

        if (is_null($key)) {
            $key = self::$_key;
        }

        if (preg_match('/[^a-zA-Z0-9\/\+=]/', $str)) {
            return false;
        }

        $str = base64_decode($str);

        if ($this->_config['noise']) {
            $str = $this->_denoise($str, $key);
        }

        if ($this->_config['mcrypt']) {
            $str = $this->_mcryptDecode($str, $key);
        }

        if ($this->_config['xor']) {
            $str = $this->_xorDecode($str, $key);
        }

        return $str;
    }

    /**
     * Mcrypt encode
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _mcryptEncode($str, $key) {

        $cipher = $this->_config['cipher'];
        $mode   = $this->_config['mode'];
        $size   = mcrypt_get_iv_size($cipher, $mode);
        $vect   = mcrypt_create_iv($size, MCRYPT_RAND);

        return mcrypt_encrypt($cipher, $key, $str, $mode, $vect);
    }

    /**
     * Mcrypt decode
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _mcryptDecode($str, $key) {

        $cipher = $this->_config['cipher'];
        $mode   = $this->_config['mode'];
        $size   = mcrypt_get_iv_size($cipher, $mode);
        $vect   = mcrypt_create_iv($size, MCRYPT_RAND);

        return rtrim(mcrypt_decrypt($cipher, $key, $str, $mode, $vect), "\0");
    }

    /**
     * XOR encode
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _xorEncode($str, $key) {

        $rand = $this->_config['hash'](rand());
        $code = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $r     = substr($rand, ($i % strlen($rand)), 1);
            $code .= $r . ($r ^ substr($str, $i, 1));
        }

        return $this->_xor($code, $key);
    }

    /**
     * XOR decode
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _xorDecode($str, $key) {

        $str = $this->_xor($str, $key);
        $code = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $code .= (substr($str, $i++, 1) ^ substr($str, $i, 1));
        }

        return $code;
    }

    /**
     * XOR
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _xor($str, $key) {

        $hash = $this->_config['hash']($key);
        $code = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $code .= substr($str, $i, 1) ^ substr($hash, ($i % strlen($hash)), 1);
        }

        return $code;
    }

    /**
     * Noise
     *
     * @see http://www.ciphersbyritter.com/GLOSSARY.HTM#IV
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _noise($str, $key) {

        $hash = $this->_config['hash']($key);
        $hashlen = strlen($hash);
        $strlen = strlen($str);
        $code = '';

        for ($i = 0, $j = 0; $i < $strlen; ++$i, ++$j) {
            if ($j >= $hashlen) $j = 0;
            $code .= chr((ord($str[$i]) + ord($hash[$j])) % 256);
        }

        return $code;
    }

    /**
     * Denoise
     *
     * @param string $str
     * @param string $key
     * @return string
     */
    protected function _denoise($str, $key) {

        $hash = $this->_config['hash']($key);
        $hashlen = strlen($hash);
        $strlen = strlen($str);
        $code = '';

        for ($i = 0, $j = 0; $i < $strlen; ++$i, ++$j) {
            if ($j >= $hashlen) $j = 0;
            $temp = ord($str[$i]) - ord($hash[$j]);
            if ($temp < 0) $temp = $temp + 256;
            $code .= chr($temp);
        }

        return $code;
    }

    /**
     * 生成随机码
     *
     * @access public
     * @param integer $length 随机码长度 (0~32)
     * @return string
     */
    public static function randCode($length = 5) {

        //参数分析
        $length = (int)$length;
        $length = ($length > 32) ? 32 : $length;

        $code  = md5(uniqid(mt_rand(), true));
        $start = mt_rand(0, 32 - $length);

        return substr($code, $start, $length);
    }

    /**
     * 生成令牌密码
     *
     * @access public
     * @param string $code 所要加密的字符(也可以是随机的)
     * @param string $lifeTime 令版密码的有效时间(单位:秒)
     * @param string $key 自定义密钥
     * @return string
     */
    public static function tokenCode($data, $lifeTime = null, $key = null) {

        //参数分析
        if (!$data) {
            return false;
        }
        //设置生存周期
        if (!is_null($lifeTime)) {
            $lifeTime = (int)$lifeTime;
            if ($lifeTime) {
                self::$_liftTime = $lifeTime;
            }
        }
        $per  = ceil(time() / self::$_liftTime);
        //设置密钥
        if (!is_null($key)) {
            self::$_key = $key;
        }

        return hash_hmac('md5', $per . $data, self::$_key);
    }

    /**
     * 令牌密码验证
     *
     * @access public
     * @param string $data 所要验证的数据
     * @param string $tokenCode 所要验证的加密字符串
     * @param string $lifeTime 令版密码的有效时间(单位:秒)
     * @param string $key 自定义密钥
     * @return boolean
     */
    public static function tokenValidate($data, $tokenCode, $lifeTime = null, $key = null) {

        //参数分析
        if (!$data || !$tokenCode) {
            return false;
        }

        //设置生存周期
        if (!is_null($lifeTime)) {
            $lifeTime = (int)$lifeTime;
            if ($lifeTime) {
                self::$_liftTime = $lifeTime;
            }
        }
        $per  = ceil(time() / self::$_liftTime);
        //设置密钥
        if (!is_null($key)) {
            self::$_key = $key;
        }
        $code = hash_hmac('md5', $per . $data, self::$_key);

        return ($code == $tokenCode) ? true : false;
    }
}