<?php
require_once __DIR__ . '/vendor/autoload.php';
xdna\core\plugin::enable("debug");
xdna\core\router::init($_SERVER['REQUEST_URI']); //$_SERVER['REDIRECT_URL']
// starting router
