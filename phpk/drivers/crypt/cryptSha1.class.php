<?php
/**
 * HMAC sha1加密实现类(只能实现单项加密)
 * 
 * @author		LiuRuiTao xpbb@qq.com
 * @version		v1.0
 * @copyright	Copyright (c) 2013,phpk.cn
 * @filesource
 */
defined('IN_ROOT') || exit('Access Denied');


class CryptSha1 {

    /**
     * SHA1加密
     * @access static
     * @param string $key 加密key
     * @param string $str 字符串
     * @return string
     */
	public static function encrypt($key,$str) {
        $blocksize=64;
        $hashfunc='sha1';
        if (strlen($key)>$blocksize)
            $key=pack('H*', $hashfunc($key));
        $key=str_pad($key,$blocksize,chr(0x00));
        $ipad=str_repeat(chr(0x36),$blocksize);
        $opad=str_repeat(chr(0x5c),$blocksize);
        $hmac = pack(
                    'H*',$hashfunc(
                        ($key^$opad).pack(
                            'H*',$hashfunc(
                                ($key^$ipad).$str
                            )
                        )
                    )
                );
        return $hmac;
    }


}