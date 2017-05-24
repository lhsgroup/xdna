<?php
namespace xdna\core;
class controller {
    protected $view;
    protected static function me() {
        return ;
    }
    protected function fire($event,$data) {
        
        $e = new \xdna\event\notify("hello");
        $e->fire();
    }
    protected function getView() {
        if(empty($this->view)) {
            //default autoload view
            $controller_path = fs::getFullPath(static::class);
            $view = fs::getFile($controller_path[0],'view',$controller_path[1]);
            die($view);
        } else {
            //TODO: calculating view

        }
    }
}