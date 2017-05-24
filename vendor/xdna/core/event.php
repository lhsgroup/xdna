<?php
namespace xdna\core;
class event {
    public $data;
    protected $event_name;
    public function __construct($event_name)
    {
        $this->event_name = $event_name;
    }
    public function fire($data = []) {
        var_dump($data);
    }
}