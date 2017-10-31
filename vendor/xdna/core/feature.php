<?php
namespace xdna\core;
class feature {
    public $value;
    public $behaviors = [];
    public function __construct() {
        $this->behaviors = $this->getBehaviors();
    }
    public function get($name) {
        $return = null;
        foreach($this->behaviors as $behavior_class) {
            if(method_exists($behavior_class,'handleGet')) {
                forward_static_call_array(array($behavior_class, 'handleGet'),[&$this,$name]);
            }

        }
        return $this->value;
    }
    public function getBehaviors() {
        $behaviors = [];
        foreach($this->behaviors as $behavior) {
            $behaviors[] = '\\app\\behaviors\\'.$behavior;
        }
        return $behaviors;
    }
}