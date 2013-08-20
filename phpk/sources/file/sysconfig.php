<?php
//全局配置声明在这个文件中.
$CONFIG['system']['db']['driver']                 = 'mysql';//数据库类型
$CONFIG['system']['db']['charset']                = 'utf8';//数据库编码
$CONFIG['system']['db']['prefix']                 = 'cms_';//表名前缀
$CONFIG['system']['db']['master']['host']         = 'localhost';//主库地址
$CONFIG['system']['db']['master']['database']     = 'cms';//数据库名称
$CONFIG['system']['db']['master']['port']         = '3306';//默认端口
$CONFIG['system']['db']['master']['user']         = 'root';//数据库帐号
$CONFIG['system']['db']['master']['password']     = '123456';//数据库密码
$CONFIG['system']['app']['allow_dir']             = array("www","wap","admin","tools");//系统允许的应用
$CONFIG['system']['app']['auto_detect']           = 1; //是否自动侦测是wap还是web
$CONFIG['system']['app']['defalut_web_path']      = 'www';//默认的应用目录
$CONFIG['system']['app']['defalut_wap_path']      = 'wap';//默认的手机应用目录
$CONFIG['system']['security']                     = '_phpk_@#!%DFDS_';   // 系统的加密字符串
$CONFIG['language']['switch_on']                  = 1; //是否打开语言包
$CONFIG['language']['default_lang']               = 'zh-cn';//默认语言
$CONFIG['language']['auto_detect']                = 1; //是否自动侦测语言
$CONFIG['language']['get_var']                    = 'lang'; //前端get获取语言包的字符
$CONFIG['language']['lang_list']                  = 'zh-cn';//语言包列表，用逗号隔开