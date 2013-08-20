<?php
class IndexController extends Controller
{
    function index ()
    {
        $welcome = "欢迎使用phpk框架！";
        $this->assign('welcome', $welcome);
        $this->display('welcome');
    }
}