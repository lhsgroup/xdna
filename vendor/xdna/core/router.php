<?php
namespace xdna\core;
class router {
    private static $app;
    public static $_ENV = [];
    public static function init($url) {
        self::$_ENV = fs::getEnv();
        if (isset($url)) {
            if(substr($url,-1) == '/') {
                $url = substr($url,0,-1);
            }
        } else {
            $url = '/';
        }        
        if($url != '/') {
            $uri = explode('/',$url);
            array_shift($uri); // remove first empty value of /
        } else {
            //uri is /, default application, default index
            $uri = [];
        }
        self::_startApp($uri);
    }
    private static function _handleApp($app_path,$uri) {
        $app = '\\apps\\'.$app_path.'\\'.$app_path;
        if($route_function = forward_static_call_array(array($app, 'get_route'),[$uri])) {
            if(is_callable($route_function)) {
                self::$app = new $app($uri);
                return $route_function($uri);
            }
        } else {
            //TODO: view index not found
        }
    }

    private static function _startApp($uri) {
        if(self::_handleApp(self::$_ENV['apps']['/'],$uri)) { // case /
           return;
        } else if(count($uri) >0 && isset(self::$_ENV['apps']['/'.$uri[0]])) { // case /app/
            array_shift($uri);
            foreach(self::$_ENV['apps'] as $path=>$app_name) {
                if($path != '/' && self::_handleApp($app_name,$uri)) {
                    return;
                }
            }
        } else { // case /app/view
            die("Router not found");
        }
    }
}