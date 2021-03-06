<?php
/**
 * cachePhp.class.php
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */
defined('IN_ROOT') || exit('Access Denied');

class cachePhp extends Base {

    /**
     * 分析缓存文件名.
     *
     * @param string $fileName
     * @return string
     */
    protected static function parseCacheFile($fileName) {

        return CACHE_PATH . md5($fileName) .'.cache.php';
    }

    /**
     * 设置缓存
     *
     * @param string $key
     * @param string $value
     * @param integer $expire 生存周期
     * @return boolean
     */
    public static function set($key, $value, $expire = null) {

        //参数分析
        if (!$key) {
            return false;
        }
        $expire = is_null($expire) ? time() + 31536000 : time() + (int)$expire;

        //分析缓存文件
        $cacheFile = self::parseCacheFile($key);
        //分析缓存内容
        $content    = '<?php if(!defined(\'IN_ROOT\'))exit(); $data = array(' . $expire . ', ' . var_export($value, true) . ');';

        //分析缓存目录
        $cacheDir = CACHE_PATH . 'data' . DS;
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        } else if (!is_writeable($cacheDir)) {
            chmod($cacheDir, 0777);
        }

        return file_put_contents($cacheFile, $content, LOCK_EX) ? true : false;
    }

    /**
     * 获取一个已经缓存的变量
     *
     * @param string $key
     * @return string
     */
    public static function get($key) {

        //参数分析
        if (!$key) {
            return false;
        }

        //分析缓存文件
        $cacheFile = self::parseCacheFile($key);

        if (is_file($cacheFile)) {
            include $cacheFile;
            //删除过期的缓存文件
            if(time() > $data[0]){
                unlink($cacheFile);
                return false;
            }
            return $data[1];
        }


        return false;
    }

    /**
     * 删除缓存
     *
     * @param string $key
     * @return void
     */
    public static function rm($key) {

        //参数分析
        if (!$key) {
            return true;
        }

        //分析缓存文件
        $cacheFile = self::parseCacheFile($key);

        return is_file($cacheFile) ? unlink($cacheFile) : true;
    }
}