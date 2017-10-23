<?php
namespace apps\home;
use \xdna\core as xCore;
class home extends xCore\appRouting {
  public function  pageNotFound() {
      header('Location: /error/');
      exit;
  }
}


