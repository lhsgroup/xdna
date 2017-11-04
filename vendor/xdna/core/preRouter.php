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
class preRouter {
    private static $app;
    public static $_ENV = [];
    public static function init($url=null) {
        self::$_ENV = fs::getEnv();
        $url = isset($url) ? $url : '/';
        if($url == '/' && isset( self::$_ENV['apps']['/'])) {
            // default application
            self::_startApp(self::$_ENV['apps']['/'],[]);
        } else {
            $uri = explode("/",$url);
            // remove first /
            array_shift($uri);
            if(isset(self::$_ENV['apps']['/'.$uri[0]])) {
                $path = self::$_ENV['apps']['/'.$uri[0]];
                $prefix = array_shift($uri);
            } else {
                $path = $path = self::$_ENV['apps']['/'];
                $prefix = '';
            }
            self::_startApp($path,(array) $uri,$prefix);
        }
    }
    public static function _startApp($app_name,$uri_array=[],$prefix=null) {
        $app_class = '\\apps\\'.$app_name.'\\'.$app_name;
        $app_class::start($uri_array,$prefix);
        // ok
    }
}