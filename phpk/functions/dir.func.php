<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * 公用目录处理函数
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */
/**
 * 替代 scandir, safe_mode
 *
 * @param unknown_type $dir            
 * @return multitype: multitype:string
 */
function scanDirs ($dir)
{
    if (function_exists('scanDirs'))
        return scanDirs($dir);
    $df = opendir($dir);
    $arr = array();
    while ($file = readdir($df)) {
        if ($file == '.' || $file == '..')
            continue;
        $arr[] = $file;
    }
    closedir($df);
    return $arr;
}
/**
 * 返回目录结构
 *
 *
 * @access public
 * @param string	path to source
 * @param int		depth of directories to traverse (0 = fully recursive, 1 = current dir, etc)
 * @return array
 */
function dirMap ($source_dir, $directory_depth = 0, $ext = '', $hidden = FALSE)
{
    if ($fp = @opendir($source_dir)) {
        $filedata = array();
        $new_depth = $directory_depth - 1;
        $source_dir = rtrim($source_dir, DIRECTORY_SEPARATOR) .
                 DIRECTORY_SEPARATOR;
        
        while (FALSE !== ($file = readdir($fp))) {
            // Remove '.', '..', and hidden files [optional]
            if (! trim($file, '.') or ($hidden == FALSE && $file[0] == '.')) {
                continue;
            }
            if($ext !== ''){
                if(pathinfo($file, PATHINFO_EXTENSION) != $ext){
                    continue;
                }
            }
            if (($directory_depth < 1 or $new_depth > 0) &&
                     @is_dir($source_dir . $file)) {
                $filedata[$file] = dirMap($source_dir . $file .
                 DIRECTORY_SEPARATOR, $new_depth, $hidden);
            }
            else {
                $filedata[] = $file;
            }
        }
        
        closedir($fp);
        return $filedata;
    }
    
    return FALSE;
}
// 包含目录下的所有文件
function includeOnceDir($dir,$ext='php') {
    $dirMap = dirMap($dir, 0 ,$ext);
    foreach ($dirMap as $v){
        include_once $v;
    }
}
/**
 * 递归删除目录，这个函数比较危险，传参一定要小心
 *
 * @param string $dir            
 * @param string $keepdir            
 * @return boolean
 */
function rmDirs ($dir, $keepdir = 1)
{
    if ($dir == '/' || $dir == '../')
        return FALSE; // 不允许删除根目录，避免程序意外删除数据。
    if (! is_dir($dir))
        return FALSE;
    substr($dir, - 1, 1) != '/' && $dir .= '/';
    $files = scanDirs($dir);
    foreach ($files as $file) {
        if ($file == '.' || $file == '..')
            continue;
        $filepath = $dir . $file;
        if (! is_dir($filepath)) {
            try {
                unlink($filepath);
            }
            catch (Exception $e) {
            }
        }
        else {
            rmDirs($filepath . '/');
        }
    }
    try {
        if ($keepdir == 0)
            rmdir($dir);
    }
    catch (Exception $e) {
    }
    return TRUE;
}
/**
 *
 *
 *
 * 创建目录
 *
 * @param array|string $dirs            
 */
function mkDirs ($dirs)
{
    if (is_array($dirs)) {
        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                if (! mkdir($dir, 0755)) {
                    die("$dir 目录创建失败，请检查目录权限");
                }
            }
        }
    }
    else {
        if (! is_dir($dirs)) {
            if (! mkdir($dirs, 0755)) {
                die("$dirs 目录创建失败，请检查目录权限");
            }
        }
    }
}
?>