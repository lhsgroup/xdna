<?php
namespace plugin\debug;
class debug extends \xdna\core\plugin {
    public static function _on_file_not_found($data) {
        echo "------------------";
        var_dup($data);
    }
}