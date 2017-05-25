<?php
namespace xdna\core;
abstract class app extends appRouting {
    public $array_routing = [];
    public function __construct($array_routing = []) {
        $this->array_routing = $array_routing;
    }
}