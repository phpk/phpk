<?php
/**
 * PHPK
*
* 字符串检查类
*
* @author LiuRuiTao xpbb@qq.com
* @version v1.0
* @copyright Copyright (c) 2013,phpk.cn
* @filesource
*
*
*/
defined('IN_ROOT') || exit('Access Denied');
class Filter extends Base
{
    /**
     * 安全过滤类-加反斜杠，放置SQL注入
     * @param  string $value 需要过滤的值
     * @return string
     */
    public static function filterSlashes(&$value) {
        if (get_magic_quotes_gpc()) return false; //开启魔术变量
        $value = (array) $value;
        foreach ($value as $key => $val) {
            if (is_array($val)) {
                self::filterSlashes($value[$key]);
            } else {
                $value[$key] = addslashes($val);
            }
        }
    }
    
    /**
     * 安全过滤类-过滤javascript,css,iframes,object等不安全参数 过滤级别高
     * @param  string $value 需要过滤的值
     * @return string
     */
    public static function filterScript($value) {
        $value = preg_replace("/(javascript:)?on(click|load|key|mouse|error|abort|move|unload|change|dblclick|move|reset|resize|submit)/i","&111n\\2",$value);
        $value = preg_replace("/<script(.*?)>(.*?)<\/script>/si","",$value);
        $value = preg_replace("/<iframe(.*?)>(.*?)<\/iframe>/si","",$value);
        $value = preg_replace ("/<object.+<\/object>/iesU", '', $value);
        return $value;
    }
    
    /**
     * 安全过滤类-过滤HTML标签
     * @param  string $value 需要过滤的值
     * @return string
     */
    public static function filterHtml($value) {
        if (function_exists('htmlspecialchars')) return htmlspecialchars($value);
        return str_replace(array("&", '"', "'", "<", ">"), array("&amp;", "&quot;", "&#039;", "&lt;", "&gt;"), $value);
    }
    /**
     * 输出安全的html
     * @param string $text
     * @param string $tags
     * @return mixed
     */
    public static function filterSafeHtml($text, $tags = null) {
        $text	=	trim($text);
        //完全过滤注释
        $text	=	preg_replace('/<!--?.*-->/','',$text);
        //完全过滤动态代码
        $text	=	preg_replace('/<\?|\?'.'>/','',$text);
        //完全过滤js
        $text	=	preg_replace('/<script?.*\/script>/','',$text);
    
        $text	=	str_replace('[','&#091;',$text);
        $text	=	str_replace(']','&#093;',$text);
        $text	=	str_replace('|','&#124;',$text);
        //过滤换行符
        $text	=	preg_replace('/\r?\n/','',$text);
        //br
        $text	=	preg_replace('/<br(\s\/)?'.'>/i','[br]',$text);
        $text	=	preg_replace('/<p(\s\/)?'.'>/i','[br]',$text);
        $text	=	preg_replace('/(\[br\]\s*){10,}/i','[br]',$text);
        //过滤危险的属性，如：过滤on事件lang js
        while(preg_match('/(<[^><]+)( lang|on|action|background|codebase|dynsrc|lowsrc)[^><]+/i',$text,$mat)){
            $text=str_replace($mat[0],$mat[1],$text);
        }
        while(preg_match('/(<[^><]+)(window\.|javascript:|js:|about:|file:|document\.|vbs:|cookie)([^><]*)/i',$text,$mat)){
            $text=str_replace($mat[0],$mat[1].$mat[3],$text);
        }
        if(empty($tags)) {
            $tags = 'table|td|th|tr|i|b|u|strong|img|p|br|div|strong|em|ul|ol|li|dl|dd|dt|a';
        }
        //允许的HTML标签
        $text	=	preg_replace('/<('.$tags.')( [^><\[\]]*)>/i','[\1\2]',$text);
        $text = preg_replace('/<\/('.$tags.')>/Ui','[/\1]',$text);
        //过滤多余html
        $text	=	preg_replace('/<\/?(html|head|meta|link|base|basefont|body|bgsound|title|style|script|form|iframe|frame|frameset|applet|id|ilayer|layer|name|script|style|xml)[^><]*>/i','',$text);
        //过滤合法的html标签
        while(preg_match('/<([a-z]+)[^><\[\]]*>[^><]*<\/\1>/i',$text,$mat)){
            $text=str_replace($mat[0],str_replace('>',']',str_replace('<','[',$mat[0])),$text);
        }
        //转换引号
        while(preg_match('/(\[[^\[\]]*=\s*)(\"|\')([^\2=\[\]]+)\2([^\[\]]*\])/i',$text,$mat)){
            $text=str_replace($mat[0],$mat[1].'|'.$mat[3].'|'.$mat[4],$text);
        }
        //过滤错误的单个引号
        while(preg_match('/\[[^\[\]]*(\"|\')[^\[\]]*\]/i',$text,$mat)){
            $text=str_replace($mat[0],str_replace($mat[1],'',$mat[0]),$text);
        }
        //转换其它所有不合法的 < >
        $text	=	str_replace('<','&lt;',$text);
        $text	=	str_replace('>','&gt;',$text);
        $text	=	str_replace('"','&quot;',$text);
        //反转换
        $text	=	str_replace('[','<',$text);
        $text	=	str_replace(']','>',$text);
        $text	=	str_replace('|','"',$text);
        //过滤多余空格
        $text	=	str_replace('  ',' ',$text);
        return $text;
    }
    function filterXss($val) {
        // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
        // this prevents some character re-spacing such as <java\0script>
        // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
    
        // straight replacements, the user should never need these since they're normal characters
        // this prevents like <IMG SRC=@avascript:alert('XSS')>
        $search = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
    
            // @ @ search for the hex values
            $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
            // @ @ 0{0,7} matches '0' zero to seven times
            $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
        }
    
        // now the only remaining whitespace attacks are \t, \n, and \r
        $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra = array_merge($ra1, $ra2);
    
        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }
        return $val;
    }
    /**
     * 安全过滤类-对进入的数据加下划线 防止SQL注入
     * @param  string $value 需要过滤的值
     * @return string
     */
    public static function filterSql($value) {
        $sql = array("select", 'insert', "update", "delete", "\'", "\/\*",
                "\.\.\/", "\.\/", "union", "into", "load_file", "outfile");
        $sql_re = array("","","","","","","","","","","","");
        return str_replace($sql, $sql_re, $value);
    }
    
    /**
     * 安全过滤类-通用数据过滤
     * @param string $value 需要过滤的变量
     * @return string|array
     */
    public static function filterEscape($value) {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = self::filter_str($v);
            }
        } else {
            $value = self::filter_str($value);
        }
        return $value;
    }
    
    /**
     * 安全过滤类-字符串过滤 过滤特殊有危害字符
     * @param  string $value 需要过滤的值
     * @return string
     */
    public static function filterStr($value) {
        $value = str_replace(array("\0","%00","\r"), '', $value);
        $value = preg_replace(array('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','/&(?!(#[0-9]+|[a-z]+);)/is'), array('', '&amp;'), $value);
        $value = str_replace(array("%3C",'<'), '&lt;', $value);
        $value = str_replace(array("%3E",'>'), '&gt;', $value);
        $value = str_replace(array('"',"'","\t",'  '), array('&quot;','&#39;','    ','&nbsp;&nbsp;'), $value);
        return $value;
    }
    
    /**
     * 私有路径安全转化
     * @param string $fileName
     * @return string
     */
    public static function filterDir($fileName) {
        $tmpname = strtolower($fileName);
        $temp = array('://',"\0", "..");
        if (str_replace($temp, '', $tmpname) !== $tmpname) {
            return false;
        }
        return $fileName;
    }
    
    /**
     * 过滤目录
     * @param string $path
     * @return array
     */
    public static function filterPath($path) {
        $path = str_replace(array("'",'#','=','`','$','%','&',';'), '', $path);
        return rtrim(preg_replace('/(\/){2,}|(\\\){1,}/', '/', $path), '/');
    }
    
    /**
     * 过滤PHP标签
     * @param string $string
     * @return string
     */
    public static function filterPhptag($string) {
        return str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $string);
    }
    
    /**
     * 安全过滤类-返回函数
     * @param  string $value 需要过滤的值
     * @return string
     */
    public static function strOut($value) {
        $badstr = array("&", '"', "'", "<", ">", "%3C", "%3E");
        $newstr = array("&amp;", "&quot;", "&#039;", "&lt;", "&gt;", "&lt;", "&gt;");
        $value  = str_replace($newstr, $badstr, $value);
        return stripslashes($value); //下划线
    }
    
}