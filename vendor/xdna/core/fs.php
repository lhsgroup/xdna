<?php
namespace xdna\core;
class fs {
    private static $shared_path = 'shared_app';
    public static function getFullPath($class_name) {
        return explode("\\",$class_name);
    }
    public static function getFile($app, $type,$name) {       
        if(file_exists('apps/'.$app.'/'.$type.'/'.$name.'.php')) {
            return 'apps/'.$app.'/'.$type.'/'.$name.'.php';
        } else if(file_exists('apps/'.$app.'/'.$type.'/'.$name.'/index.php')) {
            return 'apps/'.$app.'/'.$type.'/'.$name.'/index.php';
        } else if(file_exists(self::$shared_path.'/'.$app.'/'.$type.'/'.$name.'.php')) {
            return self::$shared_path.'/'.$app.'/'.$type.'/'.$name.'.php';
        } else if(file_exists(self::$shared_path.'/'.$app.'/'.$type.'/'.$name.'/index.php')) {
            return self::$shared_path.'/'.$app.'/'.$type.'/'.$name.'/index..php';
        } else {
            $event = new event("file_not_found");
            $event->fire(["app"=>$app,"type"=>$type,"name"=>$name]);
        }
    }
    public static function getFileFromPath($path,$name) {
        //echo realpath($path.$name.'.php').'<br />';
        //echo realpath($path.$name.'/index.php').'<br />';
        //echo realpath(self::$shared_path.$name.'.php').'<br />';
        //echo realpath(self::$shared_path.$name.'/index.php').'<br />';
        if(file_exists($path.$name.'.php')) {
            return $path.$name.'.php';
        } else if(file_exists($path.'/'.$name.'/index.php')) {
            return $path.$name.'/index.php';
        } else if(file_exists(self::$shared_path.$name.'.php')) {
            return self::$shared_path.$name.'.php';
        } else if(file_exists(self::$shared_path.'/'.$name.'/index.php')) {
            return self::$shared_path.$name.'/index.php';
        } else {
          return;
        }
    }
    public static function getEnv() {
        if(file_exists('.xdna')) {
            return parse_ini_file('.xdna');
        }
    }
}