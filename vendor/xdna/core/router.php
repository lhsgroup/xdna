<?php
namespace xdna\core;
class router {
    private static $app;
    public static $_ENV = [];
    public static function init($url=null) {
        self::$_ENV = fs::getEnv();
        $url = isset($url) ? $url : '/';
        if($url == '/' && isset( self::$_ENV['apps']['/'])) {
            // default application
            self::_startApp(self::$_ENV['apps']['/'],[]);
        } else {
            $path_parts = pathinfo($url);
            $uri = explode("/",$url);
            // remove first /
            array_shift($uri);
            if(isset(self::$_ENV['apps']['/'.$uri[0]])) {
                $path = self::$_ENV['apps']['/'.$uri[0]];
                $prefix = array_shift($uri);
            } else {
                $path = $path = self::$_ENV['apps']['/'];
                $prefix = '';
            }
            self::_startApp($path,(array) $uri,$prefix);
        }
    }
    public static function _startApp($app_name,$uri_array=[],$prefix=null) {
        $app_class = '\\apps\\'.$app_name.'\\'.$app_name;
        $app_class::start($uri_array,$prefix);
    }
}