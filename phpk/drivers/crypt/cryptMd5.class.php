<?php
/**
 * HMAC md5加密实现类(只能实现单项加密)
 * 
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 */
defined('IN_ROOT') || exit('Access Denied');


class CryptMd5 {

    
    /**
     * MD5加密
     * @access static
     * @param string $key 加密key
     * @param string $str 字符串
     * @return string
     */
	public static function encrypt($key, $str) {
        $b = 64;
        if (strlen($key) > $b) {
            $key = pack("H*",md5($key));
        }

        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;

        return md5($k_opad . pack("H*",md5($k_ipad . $str)));
    }
    /**
     * 强力加密
     * @param string $hash 加密的字符
     * @param int $times 加密的次数
     * @return string
     */
    function forceEncrypt($hash,$times = 3) {
        for($i=$times;$i>0;$i--) {
            $hash=base64_encode($hash);
            $hash=md5($hash);
            $hash=sha1($hash);
            $hash=hash("sha256", $hash);
            $hash=hash("sha512", $hash);
        }
        return $hash;
    }
    
}