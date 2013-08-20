<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * 公用shell处理函数
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 *
 */

/**
 * 远程调用SHELL函数
 * 
 * @param string $ip            
 * @param string $shell            
 * @param string $sysuser            
 * @param string $syspass            
 * @return boolean string
 */
function remoteRunShell ( $ip, $shell, $sysuser, $syspass )
{
    $connection = ssh2_connect($ip, 22);
    if ( ! ssh2_auth_password($connection, $sysuser, $syspass) )
    {
        return false;
    }
    $stream = ssh2_exec($connection, $shell);
    stream_set_blocking($stream, true);
    $output = stream_get_contents($stream);
    fclose($stream);
    return $output;
}
/**
 * PHP执行系统命令函数
 * 
 * @param string $cfe            
 * @return string
 */
function shellExecute ( $cfe )
{
    $res = '';
    if ( $cfe )
    {
        if ( function_exists('system') )
        {
            @ob_start();
            @system($cfe);
            $res = @ob_get_contents();
            @ob_end_clean();
        }
        elseif ( function_exists('passthru') )
        {
            @ob_start();
            @passthru($cfe);
            $res = @ob_get_contents();
            @ob_end_clean();
        }
        elseif ( function_exists('shell_exec') )
        {
            $res = @shell_exec($cfe);
        }
        elseif ( function_exists('exec') )
        {
            @exec($cfe, $res);
            $res = join("\n", $res);
        }
        elseif ( @is_resource($f = @popen($cfe, "r")) )
        {
            $res = '';
            while (! @feof($f))
            {
                $res .= @fread($f, 1024);
            }
            @pclose($f);
        }
    }
    return $res;
}


