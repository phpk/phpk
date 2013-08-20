<?php
/**
 * PHPK
 *
 * 框架主核心文件
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */

version_compare(PHP_VERSION, '5.3.0', '<') and die('require PHP > 5.3.0 !');
// 设置默认时区
date_default_timezone_set('PRC');
// 框架版本号
define('PHPK_VERSION', 1.0);
//判断文件是否在系统内
define('IN_ROOT', true);
//是否开启内存日志记录
define("MEMORY_LOG", true);
//目录分隔符
define("DS",DIRECTORY_SEPARATOR);
//window环境
define("IS_WIN", strstr(PHP_OS, 'WIN') ? true : false); 
/**
 *基本目录参数 
 */
// 项目根目录
defined('ROOT') || define('ROOT', dirname($_SERVER['SCRIPT_FILENAME']) . DS); 
//定义系统时间
define('ROOT_TIME', time());
// PHPK框架的路径
defined('PHPK_PATH') || define('PHPK_PATH', dirname(__FILE__) . DS); 
// phpk框架文件夹
defined('PHPK_NAME') || define('PHPK_NAME', str_replace(array(ROOT, "/"), "", PHPK_PATH));
//当前运行的文件
define('PHP_SELF', $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']);
//加载核心配置项
if (!is_file( ROOT . "config.inc.php" ))
{
    copy(PHPK_PATH.'sources'.DS.'file'.DS.'sysconfig.php', ROOT . "config.inc.php");
}
include ROOT . "config.inc.php";
// 加载系统公用函数库
include PHPK_PATH . 'commons'.DS.'common.inc.php';
include PHPK_PATH . 'commons'.DS.'function.inc.php';
include PHPK_PATH . 'core'.DS.'boot.class.php';

Boot::init();