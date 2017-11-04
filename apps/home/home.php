<?php
namespace apps\home;
use \xdna\core as xCore;

class home extends xCore\appRouting {
  public function  pageNotFound() {
      header('Location: /error/');
      exit;
  }
    public static function handleRouter($array_route=[],$prefix = null) { // remove _ to update router function
        xCore\xmodel\xFactory::createList('prova');
    }

}


