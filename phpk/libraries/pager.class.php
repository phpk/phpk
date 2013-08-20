<?php
/**
 * pager class file
 *
 * @author LiuRuiTao xpbb@qq.com
 * @version v1.0
 * @copyright Copyright (c) 2013,phpk.cn
 * @filesource
 *
 */
defined('IN_ROOT') || exit('Access Denied');

class pager extends Base {

    /**
     * pager的css文件.
     *
     * @var string
     */
    private $style;

    /**
     * 连接网址
     *
     * @var string
     */
    public $url;

    /**
     * 当前页
     *
     * @var integer
     */
    public $page;

    /**
     * list总数
     *
     * @var integer
     */
    public $total;

    /**
     * 分页总数
     *
     * @var integer
     */
    public $totalPages;

    /**
     * 每个页面显示的post数目
     *
     * @var integer
     */
    public $num;

    /**
     * list允许放页码数量,如:1.2.3.4就这4个数字,则$perCircle为4
     *
     * @var integer
     */
    public $perCircle;

    /**
     * 分页程序的扩展功能开关,默认关闭
     *
     * @var boolean
     */
    public $ext;

    /**
     * list中的坐标. 如:7,8,九,10,11这里的九为当前页,在list中排第三位,则$center为3
     *
     * @var integer
     */
    public $center;

    /**
     * 第一页
     *
     * @var string
     */
    public $firstPage;

    /**
     * 上一页
     *
     * @var string
     */
    public $prePage;

    /**
     * 下一页
     *
     * @var string
     */
    public $nextPage;

    /**
     * 最后一页
     *
     * @var string
     */
    public $lastPage;

    /**
     * 分页附属说明
     *
     * @var string
     */
    public $note;

    /**
     * 是否为ajax分页模式
     *
     * @var boolean
     */
    public $isAjax;

    /**
     * ajax分页的动作名称
     *
     * @var string
     */
    public $ajaxActionName;

    /**
     * 分页css名
     *
     * @var string
     */
    public $styleFile;

    /**
     * 分页隐藏开关
     *
     * @var boolean
     */
    public $hiddenStatus;


    /**
     * 构造函数
     *
     * @access public
     * @return boolean
     */
    public function __construct() {

        $this->ext                = false;
        $this->center             = 3;
        $this->num                = 10;
        $this->perCircle          = 10;
        $this->isAjax             = false;
        $this->hiddenStatus       = false;

        //define pager style params
        $this->firstPage         = '第一页';
        $this->prePage           = '上一页';
        $this->nextPage          = '下一页';
        $this->lastPage          = '最末页';

        return true;
    }

    /**
     * 获取总页数
     *
     * @return integer
     */
    private function getTotalPage() {

        if (!$this->total) {
            return false;
        }

        return ceil($this->total / $this->num);
    }

    /**
     * 获取当前页数
     *
     * @return integer
     */
    private function getPageNum() {

        $page = (!$this->page) ? 1 : (int)$this->page;

        //当URL中?page=5的page参数大于总页数时
        return ($page > $this->totalPages) ? (int)$this->totalPages : $page;
    }

    /**
     * 返回$this->num=$num.
     *
     * @param integer $num
     * @return $this
     */
    public function num($num = null) {

        //参数分析
        if (is_null($num)) {
            $num = 10;
        }

        $this->num = (int)$num;

        return $this;
    }

    /**
     * 返回$this->total=$totalPost.
     *
     * @param integer $totalPost
     * @return $this
     */
    public function total($totalPost = null) {

        $this->total = (!is_null($totalPost)) ? (int)$totalPost : 0;

        return $this;
    }

    /**
     * 开启分页的隐藏功能
     *
     * @access public
     * @param boolean $item    隐藏开关 , 默认为true.
     * @return $this
     */
    public function hide($item = true) {

        if ($item === true) {
            $this->hiddenStatus = true;
        }

        return $this;
    }

    /**
     * 返回$this->url=$url.
     *
     * @param string $url
     * @return $this
     */
    public function url($url = null) {

        //当url为空时,自动获取url参数. 注:默认当前页的参数为page
        if (is_null($url)) {

            //当网址没有参数时
            $url = (!$_SERVER['QUERY_STRING']) ? $_SERVER['REQUEST_URI'] . ((substr($_SERVER['REQUEST_URI'], -1) == '?') ? 'page=' : '?page=') : '';

            //当网址有参数时,且有分页参数(page)时
            if (!$url && (stristr($_SERVER['QUERY_STRING'], 'page='))) {
                $url = str_ireplace('page=' . $this->page, '', $_SERVER['REQUEST_URI']);

                $end_str = substr($url, -1);
                if ($end_str == '?' || $end_str == '&') {
                    $url .= 'page=';
                } else {
                    $url .= '&page=';
                }
            }

            //当网址中未发现含有分页参数(page)时
            if (!$url) {
                $url = $_SERVER['REQUEST_URI'] . '&page=';
            }
        }

        //自动获取都没获取到url...额..没有办法啦, 趁早返回false
        if (!$url) {
            halt('The argument of method : url() invalid in pager class!');
        }

        $this->url = trim($url);

        return $this;
    }

    /**
     * 返回$this->page=$page.
     *
     * @param integer $page
     * @return $this
     */
    public function page($page = null) {

        //当参数为空时.自动获取GET['page']
        if (is_null($page)) {
            $page = (int)Controller::get('page');
            $page = (!$page) ? 1 : $page;
        }

        if(!$page) {
            halt('The argument of method : page() invalid in pager class!');
        }

        $this->page = $page;

        return $this;
    }

    /**
     * 返回$this->ext=$ext.
     *
     * @param boolean $ext
     * @return $this
     */
    public function ext($ext = true) {

        //将$ext转化为小写字母.
        $this->ext = ($ext) ? true : false;

        return $this;
    }

    /**
     * 返回$this->center=$num.
     *
     * @param integer $num
     * @return $this
     */
    public function center($num) {

        if (!$num) {
            return false;
        }

        $this->center = (int)$num;

        return $this;
    }

    /**
     * 返回$this->perCircle=$num.
     *
     * @param integer $num
     * @return $this
     */
    public function circle($num) {

        if (!$num) {
            return false;
        }

        $this->perCircle = (int)$num;

        return $this;
    }

    /**
     * 处理第一页,上一页
     *
     * @return string
     */
    private function getFirstPage() {

        if ($this->page == 1 || $this->totalPages <= 1) {
            return false;
        }

        if ($this->isAjax === true) {
            $string = '<a href="' . $this->url . '1" onclick="' . $this->ajaxActionName . '(\'' . $this->url . '1\'); return false;">' . $this->firstPage . '</a><a href="' . $this->url . ($this->page - 1). '" onclick="' . $this->ajaxActionName . '(\'' . $this->url . ($this->page - 1). '\'); return false;">' . $this->prePage . '</a>';
        } else {
            $string = '<a href="' . $this->url . '1" target="_self">' . $this->firstPage . '</a><a href="' . $this->url . ($this->page - 1). '" target="_self">' . $this->prePage . '</a>';
        }

        return $string;
    }

    /**
     * 处理下一页,最后一页
     *
     * @return string
     */
    private function getLastPage() {

        if ($this->page == $this->totalPages || $this->totalPages <= 1) {
            return false;
        }

        if ($this->isAjax === true) {
            $string = '<a href="' . $this->url . ($this->page + 1) . '" onclick="' . $this->ajaxActionName . '(\'' . $this->url . ($this->page + 1) . '\'); return false;">' . $this->nextPage . '</a><a href="' . $this->url . $this->totalPages . '" onclick="' . $this->ajaxActionName . '(\'' . $this->url . $this->totalPages . '\'); return false;">' . $this->lastPage . '</a>';
        } else {
            $string = '<a href="' . $this->url . ($this->page + 1) . '" target="_self">' . $this->nextPage . '</a><a href="' . $this->url . $this->totalPages . '" target="_self">' . $this->lastPage . '</a>';
        }

        return $string;
    }

    /**
     * 处理注释内容
     *
     * @return string
     */
    private function getNote() {

        if (!$this->ext || !$this->note) {
            return false;
        }

        return str_replace(array('{$totalNum}', '{$total_page}', '{$num}'), array($this->total, $this->totalPages, $this->num), $this->note);
    }

    /**
     * 处理list内容
     *
     * @return string
     */
    private function getList() {

        if (empty($this->totalPages) || empty($this->page)) {
            return false;
        }

        if ($this->totalPages > $this->perCircle) {
            if ($this->page + $this->perCircle >= $this->totalPages + $this->center) {
                $list_start   = $this->totalPages - $this->perCircle + 1;
                $list_end     = $this->totalPages;
            } else {
                $list_start   = ($this->page>$this->center) ? $this->page - $this->center + 1 : 1;
                $list_end     = ($this->page>$this->center) ? $this->page + $this->perCircle-$this->center : $this->perCircle;
            }
        } else {
            $list_start       = 1;
            $list_end         = $this->totalPages;
        }

        $pageListQueue = '';
        for ($i=$list_start; $i<=$list_end; $i++) {
            $pageListQueue  .= ($this->page == $i) ? '<a class="number current">' . $i . '</a>' : (($this->isAjax === true) ? '<a href="' . $this->url . $i . '" onclick="' . $this->ajaxActionName . '(\'' . $this->url . $i . '\'); return false;">' . $i . '</a>' : '<a href="' . $this->url . $i . '" target="_self" class="number">' . $i . '</a>');
        }

        return $pageListQueue;
    }

    /**
     * 开启ajax分页模式
     *
     * @param string $action    动作名称
     * @return $this
     */
    public function ajax($action) {

        if ($action) {
            $this->isAjax           = true;
            $this->ajaxActionName   = $action;
        }

        return  $this;
    }

    /**
     * 输出处理完毕的HTML
     *
     * @return string
     */
    public function output() {

        //支持长的url.
        $this->url         = trim(str_replace(array("\n","\r"), '', $this->url));

        //获取总页数.
        $this->totalPages  = $this->getTotalPage();

        //获取当前页.
        $this->page        = $this->getPageNum();

        return (($this->hiddenStatus === true) && ($this->total <= $this->num)) ? '' : '<div class="pagination">' . $this->getNote() . $this->getFirstPage() . $this->getList() . $this->getLastPage() . '</div>';
    }

    /**
     * 输出下拉菜单式分页的HTML(仅限下拉菜单)
     *
     * @return string
     */
    public function select() {

        //支持长的url.
        $this->url         = trim(str_replace(array("\n","\r"), '', $this->url));

        //获取总页数.
        $this->totalPages  = $this->getTotalPage();

        //获取当前页.
        $this->page        = $this->getPageNum();

        $string = '<select name="phpk_select_pagelist" class="pagelist_select_box" onchange="self.location.href=this.options[this.selectedIndex].value">';
        for ($i = 1; $i <= $this->totalPages; $i ++) {
            $string .= ($i == $this->page) ? '<option value="' . $this->url . $i . '" selected="selected">' . $i . '</option>' : '<option value="' . $this->url . $i . '">' . $i . '</option>';
        }
        $string .= '</select>';

        return $string;
    }

}