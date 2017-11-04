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

namespace xdna\core\xmodel;
use \xdna\core\db as db;

class xFactory {
    public static function createList($list_name, $id_parent=null) {
        if(!static::_listExists($list_name,$id_parent)) {

        } else {
            throw new \Exception("Unable to create list ".$list_name.' with parent_id = '.$id_parent.'! the list name already exists');
        }
    }
    private static function _listExists($list_name, $id_parent=null) {
        $result = db::query("SELECT count(*) as c FROM `xdna_lists` WHERE `name` = :list_name AND id_parent ".db::EQUAL($id_parent).' :id_parent',array("list_name" => $list_name, "id_parent" =>$id_parent));
        return ($result->c===0);
    }
}