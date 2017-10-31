<?php
namespace xdna\core;
class model {
    public function __construct(){
        $features = $this->features;
        $this->features = [];
        foreach($features as $feature_name) {
            $feature_class = '\\app\\features\\'.$feature_name;
            $this->features[$feature_name] = new $feature_class();
        }
    }

    protected $features= [];
    public function __get($name) {
        if(isset($this->features[$name])) {
            return $this->features[$name]->get($name);
        } 
    }
    public function __debugInfo() {
        $obj = [];
        foreach($this->features as $k=>$feature) {
                $obj[$k] = $feature->get($k);
        }
        return $obj;
    }

}