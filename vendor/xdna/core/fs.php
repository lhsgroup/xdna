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
class fs {
    public static function getFullPath($class_name) {
        return explode("\\",$class_name);
    }
    public static function getFile($app, $type,$name) {       
        if(file_exists('apps/'.$app.'/'.$type.'/'.$name.'.php')) {
            return 'apps/'.$app.'/'.$type.'/'.$name.'.php';
        } else if(file_exists('apps/'.$app.'/'.$type.'/'.$name.'/index.php')) {
            return 'apps/'.$app.'/'.$type.'/'.$name.'/index.php';
        } else if(file_exists(self::$shared_path.'/'.$app.'/'.$type.'/'.$name.'.php')) {
            return self::$shared_path.'/'.$app.'/'.$type.'/'.$name.'.php';
        } else if(file_exists(self::$shared_path.'/'.$app.'/'.$type.'/'.$name.'/index.php')) {
            return self::$shared_path.'/'.$app.'/'.$type.'/'.$name.'/index..php';
        } else {
            $event = new event("file_not_found");
            $event->fire(["app"=>$app,"type"=>$type,"name"=>$name]);
        }
    }
    public static function getFileFromPath($path,$name) {
        //echo realpath($path.$name.'.php').'<br />';
        //echo realpath($path.$name.'/index.php').'<br />';
        //echo realpath(self::$shared_path.$name.'.php').'<br />';
        //echo realpath(self::$shared_path.$name.'/index.php').'<br />';
        if(file_exists($path.$name.'.php')) {
            return $path.$name.'.php';
        } else if(file_exists($path.'/'.$name.'/index.php')) {
            return $path.$name.'/index.php';
        } else if(file_exists(self::$shared_path.$name.'.php')) {
            return self::$shared_path.$name.'.php';
        } else if(file_exists(self::$shared_path.'/'.$name.'/index.php')) {
            return self::$shared_path.$name.'/index.php';
        } else {
          return;
        }
    }
    public static function getEnv() {
        if(file_exists('.xdna')) {
            return parse_ini_file('.xdna');
        }
    }
}