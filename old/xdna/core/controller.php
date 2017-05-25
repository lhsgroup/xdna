<?php
namespace xdna\core;
class controller {
    public $uri = [];
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

    public function __toString() {
        return '';//MUST IMPLEMENT RENDER CONTROLLER
    }
}