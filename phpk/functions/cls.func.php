<?php
/**
 * 获取类中的所有方法
 * @param string $class
 */
function getAllMethods($class){
    $r = new ReflectionClass($class);
    $m = $r->getMethods();
    $methods = array();
    foreach($m as $k => $v) {
        if($v->isPrivate()){
            $methods[$k]['type'] = 'private';
        }
        elseif($v->isProtected()){
            $methods[$k]['type'] = 'protected';
        }
        else{
            $methods[$k]['type'] = 'public';
        }
        $methods[$k]['name'] = $v->name;
        $methods[$k]['class'] = $v->class;
    }
}