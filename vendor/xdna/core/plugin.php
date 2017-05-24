<?php
namespace xdna\core;
class plugin {
    private static $plugins = [];
    public static function enable($plugin_name) {
        $class_name = 'plugin\\'.$plugin_name.'\\'.$plugin_name;
        self::$plugins[$plugin_name] = new $class_name();
    }
}