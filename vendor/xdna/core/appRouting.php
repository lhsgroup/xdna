<?php
namespace xdna\core;
class appRouting implements iRouter {
    public static function get_route($uri = []){
        return function() use ($uri) {
            $class = new \ReflectionClass(static::class);
            $namespace =  str_replace("\\","/",$class->getNamespaceName());
            // looking for a view
            if($f = fs::getFileFromPath($namespace,'/view/'.implode('/',$uri))) {
                $controller_class = str_replace("/","\\",$namespace)."\\controller";
                $controller = new $controller_class($uri);
                $controller->setView($f);
                echo $controller;
                return $controller;
            }
        };
    }
    public static function getView($uri) {
        $url = implode("/",$uri);
        die($url);
    }
}