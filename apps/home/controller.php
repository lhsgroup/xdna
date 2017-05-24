<?php
namespace apps\home;
use \xdna\core as xCore;
class controller extends xCore\controller {
    public $uri = [];
    public function __construct($uri) {
        $this->uri = $uri;
    }

    public function setView($url) {
        include ($url);
    }
}