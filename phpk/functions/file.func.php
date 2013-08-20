<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * 公用文件处理函数
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */

/**
 * 文件大小转换
 *
 * @param number $bytes
 * @return string
 */
function toSize ($bytes)
{
    // 自定义一个文件大小单位转换函数
    if ($bytes >= pow(2, 40)) {
        // 如果提供的字节数大于等于2的40次方，则条件成立
        $return = round($bytes / pow(1024, 4), 2); // 将字节大小转换为同等的T大小
        $suffix = "TB"; // 单位为TB
    } elseif ($bytes >= pow(2, 30)) {
        // 如果提供的字节数大于等于2的30次方，则条件成立
        $return = round($bytes / pow(1024, 3), 2); // 将字节大小转换为同等的G大小
        $suffix = "GB"; // 单位为GB
    } elseif ($bytes >= pow(2, 20)) {
        // 如果提供的字节数大于等于2的20次方，则条件成立
        $return = round($bytes / pow(1024, 2), 2); // 将字节大小转换为同等的M大小
        $suffix = "MB"; // 单位为MB
    } elseif ($bytes >= pow(2, 10)) {
        // 如果提供的字节数大于等于2的10次方，则条件成立
        $return = round($bytes / pow(1024, 1), 2); // 将字节大小转换为同等的K大小
        $suffix = "KB"; // 单位为KB
    } else {
        // 否则提供的字节数小于2的10次方，则条件成立
        $return = $bytes; // 字节大小单位不变
        $suffix = "Byte"; // 单位为Byte
    }
    return $return . " " . $suffix; // 返回合适的文件大小和单位
}


/**
 * 判断远程文件是否存在
 *
 * @param string $url_file
 * @return boolen
 */
function remoteFileExists ($url_file)
{
    // 检测输入
    $url_file = trim($url_file);
    if (empty($url_file)) {
        return false;
    }
    $url_arr = parse_url($url_file);

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
}
// 获取文件权限
function getChmod($filepath){
    return substr(base_convert(@fileperms($filepath),10,8),-4);
}

/**
 * 获取文件扩展名
 * 
 * PATHINFO_DIRNAME - 目录
 * PATHINFO_BASENAME - 文件名（含扩展名）
 * PATHINFO_EXTENSION - 扩展名
 * PATHINFO_FILENAME - 文件名（不含扩展名，PHP>5.2）
 * @param unknown_type $file
 * @return mixed
 */
function getFileExt($file)
{
    return pathinfo($file, PATHINFO_EXTENSION);
}

?>