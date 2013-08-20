<?php
/**
 * 文件压缩类
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */
defined('IN_ROOT') || exit('Access Denied');
class Compress extends Base
{
    /**
     * 获取压缩的文件或者字符串
     * @param array|string $fileArray
     * @return string
     */
    static function getBuffer($fileArray) {
        $buffer = "";
        if(!is_array($fileArray)){
            if(!is_file($fileArray)){
                $buffer .= $fileArray;
            }else{
                $buffer .= file_get_contents($fileArray);
            }
        }else{
            foreach ($fileArray as $val) {
                $buffer .= file_get_contents($val);
            }
        }
        return $buffer;
    }
    /**
     * 返回压缩后的文件
     * @param string $buffer
     * @param string $filePath
     * @return string|boolean
     */
    static function returnBuffer($buffer, $filePath = ""){
        if (empty($filePath)) {
            return $buffer;
        }
        else {
            file_put_contents($filePath, $buffer);
            return true;
        }
    }
    /**
     * 压缩css文件并缓存到另外一个CSS文件里
     *
     * @param array $fileArray
     * @param string $filePath
     * @return mixed boolean
     */
    static function compressCSS ($fileArray, $filePath = "")
    {
        $buffer = self::getBuffer($fileArray);
        $buffer = preg_replace("!/\*[^*]*\*+([^/][^*]*\*+)*/!", "", $buffer);
        $arr = array(
                "\r\n",
                "\r",
                "\n",
                "\t",
                "  ",
                "    ",
                "    "
        );
        $buffer = str_replace($arr, "", $buffer);
        return self::returnBuffer($buffer, $filePath);
    }
    /**
     * 压缩js文件
     *
     * @param array $fileArray
     * @param string $filePath
     * @param string $pressType
     * @return string boolean
     */
    static function compressJS ($fileArray, $filePath = "", $pressType = "jsmin")
    {
        $buffer = self::getBuffer($fileArray);
        $return = "";
        //dump($buffer);
        if ($pressType == "jsmin") {
            $jsmin = L("jsmin");
            //dump($jsmin);
            $return = $jsmin->minify($buffer);
        }
        else {
            $jspacker = L("jspacker");
            $return = $jspacker->minify($buffer);
        }
    
        return self::returnBuffer($return, $filePath);
    }
    /**
     * 压缩html : 清除换行符,清除制表符,去掉注释标记
     * @param $string
     * @return 压缩后的$string
     *
     */
    static function compressHTML($fileArray, $filePath = "") {
        $buffer = self::getBuffer($fileArray);
        $buffer = str_replace("\r\n", '', $buffer); //清除换行符
        $buffer = str_replace("\n", '', $buffer); //清除换行符
        $buffer = str_replace("\t", '', $buffer); //清除制表符
        $pattern = array (
                "/> *([^ ]*) *</", //去掉注释标记
                "/[\s]+/",
                "/<!--[^!]*-->/",
                "/\" /",
                "/ \"/",
                "'/\*[^*]*\*/'"
        );
        $replace = array (
                ">\\1<",
                " ",
                "",
                "\"",
                "\"",
                ""
        );
        $return = preg_replace($pattern, $replace, $buffer);
        return self::returnBuffer($return, $filePath);
    }
    /**
     * 压缩php文件
     * @param string|array $fileArray
     * @param string $filePath
     * @return Ambigous <string, boolean, string>
     */
    function compressPHP($fileArray, $filePath = ""){
        $buffer = self::getBuffer($fileArray);
        $return = strip_whitespace($buffer);
        return self::returnBuffer($return, $filePath);
    }
}