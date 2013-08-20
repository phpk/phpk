<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * 公用xml处理函数
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */

/**
 * 检查XML是否有效
 * @param string $xmlfile 文件地址或者字符串
 * @return boolean
 */
function xmlChecker($xmlfile){
    //创建XML解析器
    $xml_parser = xml_parser_create();
    $return = true;
    $has_error = false;            //标志位
    //使用大小写折叠来保证能在元素数组中找到这些元素名称
    xml_parser_set_option($xml_parser, XML_OPTION_CASE_FOLDING, true);
    if(!is_file($xmlfile)){
        if(!xml_parse($xml_parser,$xmlfile,true)){
            $return = false;
        }
    }else{
        if (!($fp = fopen($xmlfile, "r"))) {
            debug("无法读取XML文件$xmlfile");
            return false;
        }
        
        //解析XML文件
        
        while ($data = fread($fp, 4096))
        {
            //循环地读入XML文档，只到文档的EOF，同时停止解析
            if (!xml_parse($xml_parser, $data, feof($fp)))
            {
                $return = false;
                break;
            }
        }
    }
    
    
    if(!$return)
    {
        $message = "该XML文档是错误的！<br />";
    
        //输出错误行，列及其错误信息
        $error_line   = xml_get_current_line_number($xml_parser);
        $error_row   = xml_get_current_column_number($xml_parser);
        $error_string = xml_error_string(xml_get_error_code($xml_parser));
        
        $message .= sprintf("［第%d行，%d列］：%s",
                $error_line,
                $error_row,
                $error_string);
        debug($message);
        $return = false;
    }
    
    //关闭XML解析器指针，释放资源
    xml_parser_free($xml_parser);
    return $return;
    
}
/**
 * XML编码
 * @param mixed $data 数据
 * @param string $root 根节点名
 * @param string $item 数字索引的子节点名
 * @param string $attr 根节点属性
 * @param string $id   数字索引子节点key转换的属性名
 * @param string $encoding 数据编码
 * @return string
 */
function xmlEncode($data, $root='phpk', $item='item', $attr='', $id='id', $encoding='utf-8') {
    if(is_array($attr)){
        $_attr = array();
        foreach ($attr as $key => $value) {
            $_attr[] = "{$key}=\"{$value}\"";
        }
        $attr = implode(' ', $_attr);
    }
    $attr   = trim($attr);
    $attr   = empty($attr) ? '' : " {$attr}";
    $xml    = "<?xml version=\"1.0\" encoding=\"{$encoding}\"?>";
    $xml   .= "<{$root}{$attr}>";
    $xml   .= dataToXml($data, $item, $id);
    $xml   .= "</{$root}>";
    return $xml;
}

/**
 * 数据XML编码
 * @param mixed  $data 数据
 * @param string $item 数字索引时的节点名称
 * @param string $id   数字索引key转换为的属性名
 * @return string
 */
function dataToXml($data, $item='item', $id='id') {
    $xml = $attr = '';
    foreach ($data as $key => $val) {
        if(is_numeric($key)){
            $id && $attr = " {$id}=\"{$key}\"";
            $key  = $item;
        }
        $xml    .=  "<{$key}{$attr}>";
        $xml    .=  (is_array($val) || is_object($val)) ? dataToXml($val, $item, $id) : $val;
        $xml    .=  "</{$key}>";
    }
    return $xml;
}

