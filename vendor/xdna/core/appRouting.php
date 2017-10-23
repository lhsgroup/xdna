<?php
namespace xdna\core;
class appRouting implements iApp {
    public static function handleRouter($array_route=[],$prefix = null) {
        var_dump($array_route);
        echo '<hr />'.$prefix;

        die();
    }
    public static function start($array_route=[],$prefix=null) {
        static::handleRouter($array_route,$prefix);
    }

}