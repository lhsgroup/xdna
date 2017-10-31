<?php
namespace xdna\core;
interface iBehavior {
    static function handleGet(&$feature,$name);
    static function handleSet(&$feature,$name,$value);
}