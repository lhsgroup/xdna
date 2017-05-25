<?php
$path = self::getPath();
if($f = $this->getFile('/layout/'.$this->layout)) {
    ob_start();
    include_once $f;
    echo ob_get_clean();
} else {
    //TODO: layout not found
}