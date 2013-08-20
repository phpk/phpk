<?php
defined('IN_ROOT') || exit('Access Denied');
/**
 * 公用字符串处理函数
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */


/**
 * 截取字串双字节处理函数
 *
 * @param string $p_Str            
 * @param int $p_Len            
 * @param string $p_vStr            
 * @return string
 */
function cutstr ($p_Str, $p_Len, $p_vStr = "")
{
    if (strlen($p_Str) <= $p_Len) {
        return $p_Str;
    }
    $ls_Str = "";
    $xi = 0;
    while ($xi < $p_Len) {
        $ls_Str .= $p_Str[$xi];
        if (ord($p_Str[$xi]) > 127) {
            $xi ++;
            $ls_Str .= $p_Str[$xi];
        }
        $xi ++;
    }
    return $ls_Str . $p_vStr;
}
/**
 * 生成UUID 单机使用
 * 
 * @access public
 * @return string
 */
function uuid ()
{
    $charid = md5(uniqid(mt_rand(), true));
    $hyphen = chr(45); // "-"
    $uuid = chr(123) .     // "{"
    substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen .
             substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen .
             substr($charid, 20, 12) . chr(125); // "}"
    return $uuid;
}

/**
 * 生成Guid主键
 * 
 * @return Boolean
 */
function keyGen ()
{
    return str_replace('-', '', substr(uuid(), 1, - 1));
}

/**
 * 检查字符串是否是UTF8编码
 * 
 * @param string $string
 *            字符串
 * @return Boolean
 */
function isUtf8 ($str)
{
    $c = 0;
    $b = 0;
    $bits = 0;
    $len = strlen($str);
    for ($i = 0; $i < $len; $i ++) {
        $c = ord($str[$i]);
        if ($c > 128) {
            if (($c >= 254))
                return false;
            elseif ($c >= 252)
                $bits = 6;
            elseif ($c >= 248)
                $bits = 5;
            elseif ($c >= 240)
                $bits = 4;
            elseif ($c >= 224)
                $bits = 3;
            elseif ($c >= 192)
                $bits = 2;
            else
                return false;
            if (($i + $bits) > $len)
                return false;
            while ($bits > 1) {
                $i ++;
                $b = ord($str[$i]);
                if ($b < 128 || $b > 191)
                    return false;
                $bits --;
            }
        }
    }
    return true;
}
/**
 * 字符串截取，支持中文和其他编码
 * 
 * @static
 *
 * @access public
 * @param string $str
 *            需要转换的字符串
 * @param string $start
 *            开始位置
 * @param string $length
 *            截取长度
 * @param string $charset
 *            编码格式
 * @param string $suffix
 *            截断显示字符
 * @return string
 */
function msubstr ($str, $start = 0, $length, $charset = "utf-8", $suffix = false)
{
    if (function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif (function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
        if (false === $slice) {
            $slice = '';
        }
    }
    else {
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice . '...' : $slice;
}

/**
 * 产生随机字串，可用来自动生成密码 默认长度6位 字母和数字混合
 * 
 * @param string $len
 *            长度
 * @param string $type
 *            字串类型
 *            0 字母 1 数字 其它 混合
 * @param string $addChars
 *            额外字符
 * @return string
 */
function randString ($len = 6, $type = '', $addChars = '')
{
    $str = '';
    switch ($type) {
        case 0:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz' .
                     $addChars;
            break;
        case 1:
            $chars = str_repeat('0123456789', 3);
            break;
        case 2:
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $addChars;
            break;
        case 3:
            $chars = 'abcdefghijklmnopqrstuvwxyz' . $addChars;
            break;
        case 4:
            $chars = "们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距触星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借" .
                     $addChars;
            break;
        default:
            // 默认去掉了容易混淆的字符oOLl和数字01，要添加请使用addChars参数
            $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789' .
                     $addChars;
            break;
    }
    if ($len > 10) { // 位数过长重复字符串一定次数
        $chars = $type == 1 ? str_repeat($chars, $len) : str_repeat($chars, 5);
    }
    if ($type != 4) {
        $chars = str_shuffle($chars);
        $str = substr($chars, 0, $len);
    }
    else {
        // 中文随机字
        for ($i = 0; $i < $len; $i ++) {
            $str .= msubstr($chars, 
                    floor(mt_rand(0, mb_strlen($chars, 'utf-8') - 1)), 1);
        }
    }
    return $str;
}
/**
 * 获取登录验证码 默认为4位数字
 * 
 * @param string $fmode
 *            文件名
 * @return string
 */
function buildVerify ($length = 4, $mode = 1)
{
    return randString($length, $mode);
}

/**
 * 字节格式化 把字节数格式为 B K M G T 描述的大小
 * 
 * @return string
 */
function byteFormat ($size, $dec = 2)
{
    $a = array(
            "B",
            "KB",
            "MB",
            "GB",
            "TB",
            "PB"
    );
    $pos = 0;
    while ($size >= 1024) {
        $size /= 1024;
        $pos ++;
    }
    return round($size, $dec) . " " . $a[$pos];
}
/**
 * 编辑UBB代码处理
 *
 * @param string $Text
 *            文字内容
 * @return string
 */
function ubb ($Text)
{
    $Text = trim($Text);
    $source_array = array(
            "/\\t/is",
            "/\[h1\](.+?)\[\/h1\]/is",
            "/\[h2\](.+?)\[\/h2\]/is",
            "/\[h3\](.+?)\[\/h3\]/is",
            "/\[h4\](.+?)\[\/h4\]/is",
            "/\[h5\](.+?)\[\/h5\]/is",
            "/\[h6\](.+?)\[\/h6\]/is",
            "/\[separator\]/is",
            "/\[center\](.+?)\[\/center\]/is",
            "/\[url=http:\/\/([^\[]*)\](.+?)\[\/url\]/is",
            "/\[url=([^\[]*)\](.+?)\[\/url\]/is",
            "/\[url\]http:\/\/([^\[]*)\[\/url\]/is",
            "/\[url\]([^\[]*)\[\/url\]/is",
            "/\[img\](.+?)\[\/img\]/is",
            "/\[color=(.+?)\](.+?)\[\/color\]/is",
            "/\[size=(.+?)\](.+?)\[\/size\]/is",
            "/\[sup\](.+?)\[\/sup\]/is",
            "/\[sub\](.+?)\[\/sub\]/is",
            "/\[pre\](.+?)\[\/pre\]/is",
            "/\[email\](.+?)\[\/email\]/is",
            "/\[colorTxt\](.+?)\[\/colorTxt\]/eis",
            "/\[emot\](.+?)\[\/emot\]/eis",
            "/\[i\](.+?)\[\/i\]/is",
            "/\[u\](.+?)\[\/u\]/is",
            "/\[b\](.+?)\[\/b\]/is",
            "/\[quote\](.+?)\[\/quote\]/is",
            "/\[code\](.+?)\[\/code\]/eis",
            "/\[php\](.+?)\[\/php\]/eis",
            "/\[sig\](.+?)\[\/sig\]/is",
            "/\\n/is"
    );
    
    $replaceArray = array(
            "  ",
            "<h1>\\1</h1>",
            "<h2>\\1</h2>",
            "<h3>\\1</h3>",
            "<h4>\\1</h4>",
            "<h5>\\1</h5>",
            "<h6>\\1</h6>",
            "",
            "<center>\\1</center>",
            "<a href=\"http://\\1\" target=_blank>\\2</a>",
            "<a href=\"http://\\1\" target=_blank>\\2</a>",
            "<a href=\"http://\\1\" target=_blank>\\1</a>",
            "<a href=\"\\1\" target=_blank>\\1</a>",
            "<img src=\\1>",
            "<font color=\\1>\\2</font>",
            "<font size=\\1>\\2</font>",
            "<sup>\\1</sup>",
            "<sub>\\1</sub>",
            "<pre>\\1</pre>",
            "<a href='mailto:\\1'>\\1</a>",
            "color_txt('\\1')",
            "emot('\\1')",
            "<i>\\1</i>",
            "<u>\\1</u>",
            "<b>\\1</b>",
            " <div class='quote'><h5>引用:</h5><blockquote>\\1</blockquote></div>",
            "self::highlight_code('\\1')",
            "self::highlight_code('\\1')",
            "<div class='sign'>\\1</div>",
            "<br/>"
    );
    
    $Text = preg_replace($source_array, $replaceArray, $Text);
    
    return $Text;
}

/**
 * 生成一定数量的随机数，并且不重复
 * 
 * @param integer $number
 *            数量
 * @param string $len
 *            长度
 * @param string $type
 *            字串类型
 *            0 字母 1 数字 其它 混合
 * @return string
 */
function buildCountRand ($number, $length = 4, $mode = 1)
{
    if ($mode == 1 && $length < strlen($number)) {
        // 不足以生成一定数量的不重复数字
        return false;
    }
    $rand = array();
    for ($i = 0; $i < $number; $i ++) {
        $rand[] = randString($length, $mode);
    }
    $unqiue = array_unique($rand);
    if (count($unqiue) == count($rand)) {
        return $rand;
    }
    $count = count($rand) - count($unqiue);
    for ($i = 0; $i < $count * 3; $i ++) {
        $rand[] = randString($length, $mode);
    }
    $rand = array_slice(array_unique($rand), 0, $number);
    return $rand;
}
/**
 * 带格式生成随机字符 支持批量生成
 * 但可能存在重复
 * 
 * @param string $format
 *            字符格式
 *            # 表示数字 * 表示字母和数字 $ 表示字母
 * @param integer $number
 *            生成数量
 * @return string | array
 */
function buildFormatRand ($format, $number = 1)
{
    $str = array();
    $length = strlen($format);
    for ($j = 0; $j < $number; $j ++) {
        $strtemp = '';
        for ($i = 0; $i < $length; $i ++) {
            $char = substr($format, $i, 1);
            switch ($char) {
                case "*": // 字母和数字混合
                    $strtemp .= randString(1);
                    break;
                case "#": // 数字
                    $strtemp .= randString(1, 1);
                    break;
                case "$": // 大写字母
                    $strtemp .= randString(1, 2);
                    break;
                default: // 其他格式均不转换
                    $strtemp .= $char;
                    break;
            }
        }
        $str[] = $strtemp;
    }
    return $number == 1 ? $strtemp : $str;
}

/**
 * 获取一定范围内的随机数字 位数不足补零
 * 
 * @param integer $min
 *            最小值
 * @param integer $max
 *            最大值
 * @return string
 */
function randNumber ($min, $max)
{
    return sprintf("%0" . strlen($max) . "d", mt_rand($min, $max));
}
/**
 * 取出字符串中的第一个匹配
 *
 * @param string $str            
 * @param string $start            
 * @param string $end            
 * @return string
 */
function strCutOne ($str, $start, $end)
{
    $content = strstr($str, $start);
    $content = substr($content, strlen($start), 
            strpos($content, $end) - strlen($start));
    return $content;
}

/**
 * 取出所有匹配,不包含开始和结束字符串
 *
 * @param string $content            
 * @param string $start            
 * @param string $end            
 * @return multitype:Ambigous <>
 */
function strCutAll ($content, $start, $end, $has = false)
{
    $m = explode($start, $content);
    $a = array();
    $sum = count($m);
    for ($i = 1; $i < $sum; $i ++) {
        $my = explode($end, $m[$i]);
        if ($has === false) {
            $a[] = $my[0];
        }
        else {
            $a[] = $start . $my[0] . $end;
        }
        unset($my);
    }
    return $a;
}
/**
 * 批量替换字符串
 * 
 * @param string $start            
 * @param string $end            
 * @param string $func            
 * @param string $content            
 * @return string
 */
function strReplaceAll ($start, $end, $func, $content)
{
    $search = strCutAll($content, $start, $end, true);
    $repalce = array();
    if (function_exists($func)) {
        foreach ($search as $v) {
            $repalce[] = call_user_func($func, $v);
        }
        return str_replace($search, $replace, $content);
    }
    elseif (is_array($func)) {
        foreach ($search as $v) {
            $repalce[] = call_user_func_array($func, $v);
        }
        return str_replace($search, $replace, $content);
    }
    return $content;
}
/**
 * 词语过滤
 *
 * <p>A list of disallowed words will be replaced to '*' or the replacement value
 * from the input string.</p>
 *
 * @param string $str Input string to be processed
 * @param array $censorWords List of words to be censored
 * @param string $replacement Optional replacement string for the censored word
 * @param bool $word True to be an exact word, False to censor within word 'Youprofanity'
 * @return string
 */
function strReplaceWord($str, $censorWords, $replacement='*', $word=true){
    if(!$word) {
        return str_ireplace($censorWords, $replacement, $str);
    }

    foreach($censorWords as $c)
        $str = preg_replace("/\b(" . str_replace('\*', '\w*?', preg_quote($c)) . ")\b/i", $replacement, $str);

    return $str;
}
/**
 * 代码加亮
 * 
 * @param String $str
 *            要高亮显示的字符串 或者 文件名
 * @param Boolean $show
 *            是否输出
 * @return String
 */
function highlightCode ($str, $show = false)
{
    if (file_exists($str)) {
        $str = file_get_contents($str);
    }
    $str = stripslashes(trim($str));
    $str = str_replace(array(
            '&lt;',
            '&gt;'
    ), array(
            '<',
            '>'
    ), $str);
    $str = str_replace(array(
            '&lt;?php',
            '?&gt;',
            '\\'
    ), array(
            'phptagopen',
            'phptagclose',
            'backslashtmp'
    ), $str);
    $str = '<?php //tempstart' . "\n" . $str . '//tempend ?>'; // <?// <?
    
    $str = highlight_string($str, TRUE);
    if (abs(phpversion()) < 5) {
        $str = str_replace(array(
                '<font ',
                '</font>'
        ), array(
                '<span ',
                '</span>'
        ), $str);
        $str = preg_replace('#color="(.*?)"#', 'style="color: \\1"', $str);
    }
    
    $str = preg_replace("#\<code\>.+?//tempstart\<br />\</span\>#is", 
            "<code>\n", $str);
    $str = preg_replace("#\<code\>.+?//tempstart\<br />#is", "<code>\n", $str);
    $str = preg_replace("#//tempend.+#is", "</span>\n</code>", $str);
    
    $str = str_replace(array(
            'phptagopen',
            'phptagclose',
            'backslashtmp'
    ), array(
            '&lt;?php',
            '?&gt;',
            '\\'
    ), $str); //<?// <?
    $line = explode("<br />", rtrim(ltrim($str, '<code>'), '</code>'));
    $result = '<div class="code"><ol>';
    foreach ($line as $key => $val) {
        $result .= '<li>' . $val . '</li>';
    }
    $result .= '</ol></div>';
    $result = str_replace("\n", "", $result);
    if ($show !== false) {
        echo ($result);
    }
    else {
        return $result;
    }
}
/**
 * 文本转换为HTML
 * 将处理<>和换行符
 */
function text2html($str)
{
    return str_replace(array('<', '>', "\r\n", "\r", "\n"), array('&lt;', '&gt;', '<br />', '<br />', '<br />'), $str);
}
/**
 * 向父串中插入子串
 *
 * @param string $string  : 父串
 * @param number $sublen  : 长度
 * @param string $str     : 子串
 * @param string $code    : 编码
 * @return string
 */
function insertStr($string, $sublen=10, $str="<br/>", $code='UTF-8'){
    if($code == 'UTF-8'){
        $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
        preg_match_all($pa, $string, $t_string);

        $n = count($t_string[0]);
        $floor = ceil($n / $sublen);
        if($n > $sublen) {
            for($i=0; $i < $floor; $i++){
                array_splice($t_string[0], ($sublen * ($i+1))-1, 0, $str);
            }
            return implode('',  $t_string[0]);
        }else{
            array_splice($t_string[0], $sublen, 0);
            return implode('', $t_string[0]);
        }
    }
}