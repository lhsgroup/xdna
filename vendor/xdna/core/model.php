<?php
/**
 * Copyright 2008 LHS Group s.r.l.
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software a
 * nd associated documentation files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions
 * of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
 * LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
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