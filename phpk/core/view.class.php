<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * PHPK
 *
 * 模板类
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 *
 */
class View extends Base
{
    //替换的tag
    public $replacecode = array(
            'search' => array(),
            'replace' => array()
    );
    //缓存文件
    public $cacheType;
    //需要检测的模版
    public $checkFiles = array();
    /**
     * 模板初始化
     * 
     * @param 模版名称 $file            
     * @param 变量 $vars            
     * @param unknown_type $layoutName            
     */
    public function init ($file, $vars, $layoutName = '')
    {
        $fileName = self::getTplFile($file);
        $this->cacheType = C('output.tmpl_cache');
        if($this->cacheType == 0){
            self::ob_output($fileName, $vars, $layoutName);
        }
        else{
            self::cache_output($fileName, $vars, $layoutName);
        }
    }
    /**
     * 动态缓存输出
     * @param string $fileName
     * @param string $vars
     * @param string $layoutName
     */
    public function ob_output($fileName, $vars, $layoutName = ''){
        //$staticVars = $vars;
        extract($vars, EXTR_OVERWRITE);
        ob_start(self::get_gzip());
        include ($fileName);
        $template = ob_get_clean();
        // 开启布局
        if (C('output.layout_on') === 1) {
            if(false === strpos($template, "{no_layout}")){
                $layout_name = empty($layoutName) ? C('output.layout_name') : $layoutName;
                $layoutFile = self::getTplFile($layout_name, 'layout');
                ob_start();
                include ($layoutFile);
                $layoutTmpl = ob_get_clean();
                
                $template = str_replace(C('output.layout_item'), $template,
                        $layoutTmpl);
            }else{
                $template = str_replace("{no_layout}", "",$template);
            }
            
        }
        $template = self::replaceInclude($template,$vars);
        $template = self::replaceStaticTempl($template);
        $template = self::compressHtml($template);
        //执行钩子
        Boot::checkHook('display_override',$template);
        
        echo $template;
    
        if (extension_loaded("zlib")) {
            ob_end_flush();
        }
    }
    /**
     * 静态缓存输出
     * @param string $fileName
     * @param string $vars
     * @param string $layoutName
     */
    public function cache_output($fileName, $vars, $layoutName = ''){
        extract($vars, EXTR_OVERWRITE);
        $cacheFile = TPLC_PATH . PAGE_KEY .substr(md5($fileName),0,5). '.tpl.php';
        if($this->cacheType == 1){
            if(!is_file($cacheFile) || (filemtime($fileName) > filemtime($cacheFile))){
                $this->parse_template($fileName,$cacheFile);
            }
        }
        //执行钩子
        Boot::checkHook('cache_override',$cacheFile);
        
        if(C('output.gzip') == 1){
            ob_start(self::get_gzip());
            include $cacheFile;
            echo ob_get_clean();
        }else{
            include $cacheFile;
        }
        
    }
    /**
     * 解析动态模版
     * @param string $fileName
     * @param string $cacheFile
     */
    public function parse_template($fileName,$cacheFile){
        //var_dump($cacheFile);
        $template = file_get_contents($fileName);
        // 开启布局
        if (C('output.layout_on') === 1) {
            if(false === strpos($template, "{no_layout}")){
                $layout_name = empty($layoutName) ? C('output.layout_name') : $layoutName;
                $layoutFile = self::getTplFile($layout_name, 'layout');
                array_push($this->checkFiles, $layoutFile);
                $layoutTmpl = file_get_contents($layoutFile);
                $template = str_replace(C('output.layout_item'), $template,
                        $layoutTmpl);
            }else{
                $template = str_replace("{no_layout}", "",$template);
            }
            
        }
        $template = self::replaceInclude($template);
        $template = self::replaceStaticTempl($template);
        $headeradd = '';
        if (! empty($this->checkFiles)) {
            $checkFiles = implode("|", $this->checkFiles);
            $headeradd = "\nif(C(\"output.tmpl_cache\")==1){\n\t\$this->checktplrefresh('$checkFiles', '$fileName', '$cacheFile');\n}\n";
        }
        $template = "<?php \ndefined('IN_ROOT') || exit('Access Denied'); {$headeradd}?>\n".$template;
        $template = self::compressHtml($template);
        file_put_contents($cacheFile, $template);
    }
    /**
     * 判断是否需要更新模版
     * @param string $files
     * @param string $fileName
     * @param string $cacheFile
     */
    function checktplrefresh($files,$fileName,$cacheFile){
        $fileArr = explode("|", $files);
        if(count($fileArr) > 0){
            $ifRefresh = self::checkrefresh($fileArr,$cacheFile);
            if($ifRefresh){
                self::parse_template($fileName,$cacheFile);
           }
        }
    }
    public static function compressHtml($template){
        if(C('output.htmlzip') == 1){
            return Compress::compressHTML($template);
        }else{
            return $template;
        }
    }
    /**
     * 判断是否可以gzip压缩
     * 
     * @return string NULL
     */
    public function get_gzip ()
    {
        if (extension_loaded("zlib") && C('output.gzip') == 1) {
            return "ob_gzhandler";
        }
        else {
            return null;
        }
    }
    /**
     * 显示hook
     * 
     * @param string $file            
     * @param string $vars            
     */
    public function displayHook ($file, $vars)
    {
        extract($vars, EXTR_OVERWRITE);
        $fileName = self::getTplFile($file, 'hooks');
        
        ob_start();
        include ($fileName);
        $template = ob_get_clean();
        $template = self::replaceStaticTempl($template);
        echo $template;
    }
    
    /**
     * 更换标签
     * 
     * @param string $template            
     */
    public function replaceStaticTempl ($template)
    {
        $template = self::replaceStaticTags($template);
        // lang
        $template = preg_replace("/\{lang\s+(.+?)\}/ies", 
                "\$this->langtags('\\1')", $template);
        // css
        $template = preg_replace("/[\n\r\t]*\{css\s+(.+?)\s*\}[\n\r\t]*/ise", 
                "\$this->csstags('\\1')", $template);
        // js
        $template = preg_replace("/[\n\r\t]*\{js\s+(.+?)\s*\}[\n\r\t]*/ise", 
                "\$this->jstags('\\1')", $template);
        // url
        $template = preg_replace("/[\n\r\t]*\{url\s+(.+?)\s*\}[\n\r\t]*/ise", 
                "\$this->urltags('\\1')", $template);
        
        if (! empty($this->replacecode)) {
            $template = str_replace($this->replacecode['search'], 
                    $this->replacecode['replace'], $template);
        }
        $template = $this->xpjstags($template);
        return $template;
    }
    
    /**
     * 更换静态标签
     * @param string $template
     * @return mixed
     */
    public function replaceStaticTags($template){
        //if($this->cacheType == 0){
            $template = str_replace("{img}", WEB_IMG, $template);
            $template = str_replace("{js}", WEB_JS, $template);
            $template = str_replace("{css}", WEB_CSS, $template);
            $template = str_replace("{url}", WEB_URL, $template);
            $template = str_replace("{pro}", WEB_APP, $template);
            $template = str_replace("{public}", WEB_PUBLIC, $template);
            //$template = self::replaceOtherTags($template);
            
            //$template = str_replace('"', "'", $template);
            //var_dump($template);
            //$template = eval("$template");
            /*
        }else{
            //这里替换是为了多域名访问而造成访问错误
            $template = str_replace("{img}", "<?php echo WEB_IMG;?>", $template);
            $template = str_replace("{js}", "<?php echo WEB_JS;?>", $template);
            $template = str_replace("{css}", "<?php echo WEB_CSS;?>", $template);
            $template = str_replace("{url}", "<?php echo WEB_URL;?>", $template);
            $template = str_replace("{pro}", "<?php echo WEB_APP;?>", $template);
            $template = str_replace("{public}", "<?php echo WEB_PUBLIC;?>", $template);
            //下面只有动态模版才可以替换
            //$template = self::replaceOtherTags($template);
        }
        */
        return $template;
    }
    /**
     * 更换动态标签
     * 
     */
    public function replaceOtherTags($template){
        $var_regexp = "((\\\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*(\-\>)?[a-zA-Z0-9_\x7f-\xff]*)(\[[a-zA-Z0-9_\-\.\"\'\[\]\$\x7f-\xff]+\])*)";
        $template = preg_replace(
                "/\{(\\\$[a-zA-Z0-9_\-\>\[\]\'\"\$\.\x7f-\xff]+)\}/s",
                "<?=\\1?>", $template);
        $template = preg_replace("/\<\?\=\<\?\=$var_regexp\?\>\?\>/se",
                "\$this->addquote('<?=\\1?>')", $template);
        $template = preg_replace("/[\n\r\t]*\{echo\s+(.+?)\}[\n\r\t]*/ise",
                "\$this->stripvtags('<? echo \\1; ?>')",
                $template);
        $template = preg_replace("/([\n\r\t]*)\{if\s+(.+?)\}([\n\r\t]*)/ise",
                "\$this->stripvtags('\\1<? if(\\2) { ?>\\3')", $template);
        $template = preg_replace(
                "/([\n\r\t]*)\{elseif\s+(.+?)\}([\n\r\t]*)/ise",
                "\$this->stripvtags('\\1<? } elseif(\\2) { ?>\\3')", $template);
        $template = preg_replace("/\{else\}/i", "<? } else { ?>", $template);
        $template = preg_replace("/\{\/if\}/i", "<? } ?>", $template);
        
        $template = preg_replace(
                "/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\}[\n\r\t]*/ise",
                "\$this->stripvtags('<? if(is_array(\\1)) foreach(\\1 as \\2) { ?>')",
                $template);
        $template = preg_replace(
                "/[\n\r\t]*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\}[\n\r\t]*/ise",
                "\$this->stripvtags('<? if(is_array(\\1)) foreach(\\1 as \\2 => \\3) { ?>')",
                $template);
        $template = preg_replace("/\{\/loop\}/i", "<? } ?>", $template);
        $template = preg_replace("/\<\?(\s{1})/is", "<?php\\1", $template);
        $template = preg_replace("/\<\?\=(.+?)\?\>/is",
                "<?php echo \\1;?>", $template);
        return $template;
    }
    /**
     * 模版过滤
     * @param string $var
     * @return mixed
     */
    public function addquote ($var)
    {
        return str_replace("\\\"", "\"",
                preg_replace("/\[([a-zA-Z0-9_\-\.\x7f-\xff]+)\]/s", "['\\1']",
                        $var));
    }
    /**
     * 模版过滤
     * @param string $var
     * @return mixed
     */
    public function stripvtags ($expr, $statement = '')
    {
        $expr = str_replace("\\\"", "\"",
                preg_replace("/\<\?\=(\\\$.+?)\?\>/s", "\\1", $expr));
        $statement = str_replace("\\\"", "\"", $statement);
        return $expr . $statement;
    }
    /**
     * 更换tpl标签或者ad标签
     * @param string $template
     * @param array $vars
     */
    public function replaceInclude($template,$vars=''){
        if (false !== strpos($template, "{tpl") || false !== strpos($template, "{ad")){
            $regex = '/[\n\r\t]*\{tpl\s+(.+?)\}[\n\r\t]*|[\n\r\t]*\{ad\s+(.+?)\}[\n\r\t]*/';
            preg_match_all($regex, $template, $matches);
            $search = $matches[0];
            $replace = $matches[1];
            $adreplace = $matches[2];
            //var_dump($matches);
            $repalceArr = array();
            
            if(count($replace)>0){
                if($this->cacheType == 0){
                    extract($vars, EXTR_OVERWRITE);
                    foreach ($replace as $k => $v){
                        if(empty($v)){
                            $v = $adreplace[$k];
                            $fileName = self::getTplFile($v,'ads');
                        }else{
                            $fileName = self::getTplFile($v,'tpl');
                        }
                        ob_start();
                        include ($fileName);
                        $repalceArr[] = ob_get_clean();
                    }
                }else{
                    foreach ($replace as $k => $v){
                        if(empty($v)){
                            $v = $adreplace[$k];
                            $fileName = self::getTplFile($v,'ads');
                        }else{
                            $fileName = self::getTplFile($v,'tpl');
                        }
                        //压入更新文件队列
                        array_push($this->checkFiles, $fileName);
                        $repalceArr[] = file_get_contents($fileName);
                    }
                }
                
            }
            $template = str_replace($search, $repalceArr, $template);
        }
        return $template;
    }
    /**
     * xpjs过滤
     * 
     * @param string $tpl            
     * @return string
     */
    public function xpjstags ($tpl)
    {
        $regex = '/js\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^"\'>\s]+))/';
        preg_match_all($regex, $tpl, $matches);
        $gettag = str_replace(
                array(
                        "js=",
                        '"',
                        "'"
                ), '', $matches[0]);
        $xptag = array_unique($gettag);
        $tagArr = $urlArr = array();
        if (count($xptag) > 0) {
            $str = '';
            array_unshift($xptag, 'base');
            foreach ($xptag as $v) {
                $tagFile = APP_PATH . 'public'.DS.'xpjs' . DS . $v . '.xp.js';
                $urlFile = WEB_APP . 'public/xpjs/' . $v . '.xp.js';
                
                if (! is_file($tagFile)) {
                    $xpFile = PHPK_PATH . 'javascript'. DS .'web' . DS . $v . '.js';
                    
                    
                    if (is_file($xpFile)) {
                        copy($xpFile, $tagFile);
                    }
                    else {
                        continue;
                    }
                }
                //压入更新文件队列
                array_push($this->checkFiles, $tagFile);
                $tagArr[$v] = $tagFile;
                $urlArr[$v] = $urlFile;
            }
            $str = "";
            $output = C('output');
            if ($output['jszip'] == 1) {
                $cfile = 'public/xpjs/xpjs_' . substr(md5(PAGE_KEY.implode("_",$tagArr)), 0, 6) .
                         '.js';
                $cachefile = APP_PATH . $cfile;
                if ($this->checkrefresh($tagArr, $cachefile)) {
                    if ($output['jszip'] == 1) {
                        Compress::compressJS($tagArr, $cachefile, "jsmin");
                    }
                    else {
                        Compress::compressJS($tagArr, $cachefile, "jspacker");
                    }
                }
                $str .= '<script type="text/javascript" src="' . WEB_APP . $cfile .
                         '"></script>';
            }
            else {
                foreach ($urlArr as $key => $val) {
                    $str .= '<script type="text/javascript" src="' . $val .
                             '"></script>';
                }
            }
            if (! empty($str)) {
                $tpl = str_replace("</head>", $str . "</head>", $tpl);
            }
        }
        return $tpl;
    }
    /**
     * 更换lang标签 {lang error}
     *
     * @param string $parameter
     * @return string
     */
    function langtags($parameter) {
        $parameter = stripslashes($parameter);
        $i = count($this->replacecode['search']);
        $this->replacecode['search'][$i] = $search = "<!--LANG_TAG_$i-->";
        $this->replacecode['replace'][$i] = lang($parameter);
        return $search;
    }

    /**
     * 更换CSStag {css cssfile1,cssfile2}
     * 
     * @param string $parameter            
     * @return string
     */
    function csstags ($parameter)
    {
        $parameter = stripslashes($parameter);
        $i = count($this->replacecode['search']);
        $this->replacecode['search'][$i] = $search = "<!--CSS_TAG_$i-->";
        $this->replacecode['replace'][$i] = $this->css($parameter);
        return $search;
    }
    /**
     * 更换css内置标签
     * <?php echo $this->css('cssfile1,cssfile2');?>
     * @param string $parameter            
     * @return string
     */

    public function css ($parameter)
    {
        $fileArray = $this->getPublicFile(explode(",", $parameter), 'css');
        $str = '';
        if (count($fileArray) > 0) {
            $output = C('output');
            if ($output['csszip'] == 1) {
                $FileName = 'public/css/' . PAGE_KEY . substr(md5($parameter), 0, 3) . '.css';
                $cachefile = APP_PATH . $FileName;
                //压入更新文件队列
                //$this->checkFiles = array_merge($this->checkFiles, $fileArray);
                
                //if ($this->checkrefresh($fileArray, $cachefile)) {
                    Compress::compressCSS($fileArray, $cachefile);
                //}
                $str .= '<link type="text/css" rel="stylesheet" href="' . WEB_APP .
                         $FileName . '"/>';
            }
            else {
                
                foreach ($fileArray as $key => $val) {
                    $str .= '<link type="text/css" rel="stylesheet" href="' .
                             WEB_CSS . $key . '.css"/>';
                }
            }
        }
        return $str;
    }
    /**
     * 更换js标签 {js jsfile1,jsfile2}标签
     * 
     * @param string $parameter            
     * @return string
     */
    function jstags ($parameter)
    {
        $parameter = stripslashes($parameter);
        $i = count($this->replacecode['search']);
        $this->replacecode['search'][$i] = $search = "<!--JS_TAG_$i-->";
        
        $this->replacecode['replace'][$i] = $this->js($parameter);
        return $search;
    }
    /**
     * 更换js内置标签
     * <?php echo $this->css('cssfile1,cssfile2');?>
     * @param string $parameter            
     * @return string
     */

    public function js ($parameter)
    {
        $fileArray = $this->getPublicFile(explode(",", $parameter), 'js');
        $str = '';
        if (count($fileArray) > 0) {
            $output = C('output');
            if ($output['jszip'] > 0) {
                $FileName = 'public/js/' .
                         PAGE_KEY . substr(md5($parameter), 0, 3) .
                         '.js';
                $cachefile = APP_PATH . $FileName;
                //压入更新文件队列
                //$this->checkFiles = array_merge($this->checkFiles, $fileArray);
                
                //if ($this->checkrefresh($fileArray, $cachefile)) {
                    if ($output['jszip'] == 1) {
                        Compress::compressJS($fileArray, $cachefile, "jsmin");
                    }
                    else {
                        Compress::compressJS($fileArray, $cachefile, "jspacker");
                    }
                //}
                $str .= '<script type="text/javascript" src="' . WEB_APP .
                         $FileName . '"></script>';
            }
            else {
                
                foreach ($fileArray as $key => $val) {
                    $str .= '<script type="text/javascript" src="' . WEB_JS .
                             $key . '.js"></script>';
                }
            }
        }
        return $str;
    }
    /**
     * 更换URL标签
     * 
     * @param string $parameter            
     * @return string
     */
    function urltags ($parameter)
    {
        $parameter = htmlspecialchars($parameter);
        $i = count($this->replacecode['search']);
        $this->replacecode['search'][$i] = $search = "<!--URL_TAG_$i-->";
        //var_dump($parameter);
        $this->replacecode['replace'][$i] = U($parameter);
        return $search;
    }
    /**
     * 判断是否需要更新文件
     *
     * @param array|string $file            
     * @param string $cache            
     * @return boolean
     */
    public function checkrefresh ($file, $cache)
    {
        if (! is_file($cache)) {
            return true;
        }
        $cacheTime = filemtime($cache);
        if (is_array($file)) {
            foreach ($file as $k => $v) {
                if (filemtime($v) > $cacheTime) {
                    return true;
                }
            }
        }
        else {
            if (filemtime($file) > $cacheTime) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 得到pubilc中的文件
     *
     * @param array $fileArray            
     * @param string $ext            
     * @return multitype:string
     */
    function getPublicFile ($fileArray, $ext = "css")
    {
        // $result = array_unique($fileArray);
        $return = array();
        foreach ($fileArray as $val) {
            if ($ext == "css") {
                if (strpos(strtolower($val), ".css") === false) {
                    $fileName = APP_PATH . 'public/styles/' . $val . ".css";
                }
                else {
                    $fileName = APP_PATH . 'public/styles/' . $val;
                }
            }
            else {
                if (strpos(strtolower($val), ".js") === false) {
                    $fileName = APP_PATH . 'public/javascripts/' . $val . ".js";
                }
                else {
                    $fileName = APP_PATH . 'public/javascripts/' . $val;
                }
            }
            // dump($fileName);
            if (is_file($fileName)) {
                //压入更新文件队列
                array_push($this->checkFiles, $fileName);
                
                $return[$val] = $fileName;
            }
        }
        return $return;
    }
    /**
     * 获取模版路径
     *
     * @param string $file            
     * @return string
     */
    public function getTplFile ($file, $type = 'tpl')
    {
        $defaultskin = C('output.default_tmpl');
        $defaultext = C('output.tmpl_suffix');
        if ($type == 'tpl') {
            $skin = $defaultskin ? $defaultskin : "default";
        }
        elseif ($type == 'layout') {
            $skin = 'layouts';
        }
        elseif ($type == 'ads') {
            $skin = 'ads';
        }
        elseif ($type == 'hooks') {
            $skin = 'hooks';
        }
        else {
            $skin = 'default';
        }
        $tpldir = APP_PATH . 'views' . DS . $skin . DS . $file;
        $filename = "";
        //var_dump($tpldir);
        if (isset($defaultext)) {
            if (false === strpos($file, $defaultext)) {
                $filename = $tpldir . $defaultext;
            }
            else {
                $filename = $tpldir;
            }
            // dump($filename);
        }
        else {
            $ext = array(
                    '.php',
                    '.htm',
                    '.html',
                    '.tpl'
            );
            foreach ($ext as $v) {
                if (is_file($tpldir . $v)) {
                    $filename = $tpldir . $v;
                    break;
                }
                if (is_file($tpldir)) {
                    $filename = $tpldir;
                    break;
                }
            }
        }
        
        if (! is_file($filename)) {
            halt($filename."模版文件不存在");
        }
        return $filename;
    }
}
?>
