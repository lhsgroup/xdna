<?php
namespace xdna\core;
class controller {
    public function __construct() {
        $e = new \xdna\event\notify("hello");
        $e->fire();
    }
}