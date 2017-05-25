<?php
namespace xdna\core;
class simpleController {
    public $uri = [];
    public $viewContent = '';
    public $layout = 'index';
    public function __get($name) {
        //load component logic
        if($f = $this->getFile('/component/'.$name)) {
            ob_start();
            include $f;
            return  ob_get_clean();
        } else {
            return;
        }
    }

    public function __construct($uri) {
        $this->uri = $uri;
    }
    public function setView($url) {
        ob_start();
        include $url;
        $this->viewContent = ob_get_clean();
    }
    public function setLayout($layout) {
        $this->layout = $layout;
    }
    public function __toString() {
        return $this->RenderLayout;
    }
    public static function getPath() {
        $class = new \ReflectionClass(static::class);
        return str_replace("\\","/",$class->getNamespaceName());
    }
    protected function getFile($file_name) {
        $path = self::getPath();
        if($f = fs::getFileFromPath($path,$file_name)) {
            return $f;
        } else {
            return;
        }
    }
}