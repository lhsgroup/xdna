<?php
namespace xdna\core;
class event {
    public $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    public function fire() {
        echo $this->data;
    }
}