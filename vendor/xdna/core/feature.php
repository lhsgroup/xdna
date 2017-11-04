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