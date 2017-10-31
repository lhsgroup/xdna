<?php

/**
 * Classe xdna
 *
 * @author luca
 */
class cxdna {
    public $table;
    public static $cachaTable= array();
    public static $cachePrimaryKeys = array();
    public $id;
    public $XdnaPrimaryKey=NULL;
    private $cacheToSave=array();
    public function __construct($table,$id=NULL) {
        $this->table=$table;
        $this->id = $id;
        $this->loadInfo();
    }
    private function loadInfo() {
        if(!isset(self::$cachaTable[$this->table])) {
            $sql = "SHOW COLUMNS FROM  `".$this->table."`";
            $result = xdna_db::query($sql);            
            while ($risposta = xdna_db::fetch_object($result)) {
                if(!isset(self::$cachaTable[$this->table])) {
                    self::$cachaTable[$this->table]=array();
                }
                self::$cachaTable[$this->table][$risposta->Field] = $risposta;
                if($risposta->Key=='PRI') {
                   self::$cachePrimaryKeys[$this->table] = $risposta->Field;                   
                }
            }
        }        
    }
    public function isParam($name) {
        if(!isset(self::$cachePrimaryKeys[$this->table])) {
            throw new Exception("Primary key is not defined in table ".$this->table);
        }
        return isset(self::$cachaTable[$this->table][$name]);
    }
    
    public function __set($name, $value) {
       if($this->isParam($name)) {
            $this->cacheToSave[$name] = $value;
        } else {
            throw new Exception("The field ".$name." is not available in table ".$this->table);
        }        
    }
    public function __get($name) {
        
        if(!$this->isParam($name)) {            
            throw new Exception("The field ".$name." is not available in table ".$this->table);
        }
        if(is_null($this->id)) {
            throw new Exception("the driver key is null in table ".$this->table);
        }
        if(isset($this->cacheToSave[$name])) {
            return $this->cacheToSave[$name];
        } else {
            $sql = "SELECT `".$name."` FROM `".$this->table."` as Field WHERE `".$this->__primaryKey."` = '".$this->id."'";
            $result = xdna_db::query($sql);
            if($risposta=  xdna_db::fetch_object($result)) {
                $this->cacheToSave[$name] = $risposta->Field;
                return $risposta->Field;
            }
        }
        
    }
   
     
    public function save() {
        $prepare = array();
        
        if(is_null($this->id)) {
            $prepare_values = array();
            foreach($this->cacheToSave as $k=>$v) {
                $prepare[] = "`".$k."`";
                $prepare_values[] = "'".addslashes($v)."'";
            }
            $sql = "INSERT INTO `".$this->table."` (".implode(" , ",$prepare).") VALUES (".implode(" , ",$prepare_values).");";
            xdna_db::query($sql);
            $this->id = xdna_db::lastId();
        } else {
            foreach($this->cacheToSave as $k=>$v) {
                $prepare[] = "`".$k."` = '".addslashes($v)."'";
            }
            $this->cacheToSave = array();
            $sql = "UPDATE `".$this->table."` SET ".implode(" , ",$prepare)." WHERE `".self::$cachePrimaryKeys[$this->table]."` = '".$this->id."'";
            xdna_db::query($sql);
        }
    }
    public function delete() {
        if($this->id && isset(self::$cachePrimaryKeys[$this->table])){
            $sql = "DELETE FROM `".$this->table."` WHERE `".self::$cachePrimaryKeys[$this->table]."` ='".$this->id."' LIMIT 1";
            xdna_db::query($sql);
        } else {
            throw new Exception("Impossible to remove this object fomr the database");
        }
    }
}

?>
