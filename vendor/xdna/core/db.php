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
class xdb_autodisconnect {
    public function __destruct(){
        db::disconnect();
    }
}
class db {
    private static $db;
    private static $isConnected=FALSE;

    public static function disconnect(){
        self::$db = NULL;
    }

    public static function connect() {
        if (!defined('PDO::ATTR_DRIVER_NAME')) {
            die('PDO unavailable');
        }
        if(!self::$isConnected) {
            try {
                self::$db = new \PDO("mysql:host=127.0.0.1;dbname=xdna", "root", "");
                self::$isConnected = true;
                return true;
            } catch (PDOException $e) {
                throw new Exception("Unable to connect to database:" ." - " .  $e->getMessage());
            }
        } else {
            return true;
        }
    }
    public static function EQUAL($value) {
        if($value === null) {
            return 'IS ';
        } else {
            return '= ';
        }
    }
    public static function exec($sql,$params=array()) {
        $result = self::query($sql,$params);
        return new xdna_db_iterator($result);
    }
    public static function query($sql,$params=array()) {
        if(!self::$isConnected){
            self::connect();
        }
        $result = self::$db->prepare($sql);
        if($params) {
            try {
                $result->execute($params);
            }catch (\PDOException $e) {
                throw new \Exception("Unable to execute " .$sql." - " .  $e->getMessage());
            }
        } else {
            try {
                $result->execute();
            }catch (\PDOException $e) {
                throw new \Exception("Unable to execute " .$sql." - " .  $e->getMessage());
            }
        }
        return $result->fetch(\PDO::FETCH_OBJ);
    }
    /*
    public static function fetch_object(&$result) {
        return $result->fetch(PDO::FETCH_OBJ);
    }
    public static function ckUnique($table,$name,$field) {
        $sql = "SELECT COUNT(*) as count FROM `".$table."` WHERE `".$field."` = '".$name."'";
        $result = self::query($sql);
        $risposta = self::fetch_object($result);
        if($risposta->count ==0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    public static function istance($table) {
        $result = self::query("SHOW COLUMNS FROM ".$table);
        $tmp=$tmpVars=$tmpUnique=$tmpDefaultVasr=array();
        while($risposta = self::fetch_object($result)) {
            $t="";
            echo 'var ';
            $t.='$'.$risposta->Field;
            if($risposta->Default=='NULL') {
                $t.= "=NULL";
            } else if($risposta->Type=="tinyint(1)" && $risposta->Default==0) {
                $t.= "=FALSE";
            } else if($risposta->Type=="tinyint(1)" && $risposta->Default==1) {
                $t.= "=TRUE";
            } else if($risposta->Default) {
                $t.= "='".$risposta->Default."'";
            }
            if($risposta->Key != 'PRI') {
                $tmpDefaultVasr[] = $t;
                $tmpVars[$risposta->Field] = '$'.$risposta->Field;
            }
            echo $t.";\n";
            $tmp[$risposta->Field] = $risposta;

            if($risposta->Key=='UNI') {

                $tmpUnique[] = $risposta->Field;
            }
        }
        echo 'public static function create('.  implode(',', $tmpDefaultVasr).") {\n";
        // verifico gli unique
        foreach($tmpUnique as $field) {
            echo ' if(!xdna_db::ckUnique(\''.$table.'\', $'.$field.', \''.$field.'\')) {
        throw new Exception("The '.$field.' \'".$name." already exist on table '.$table.' and must be unique");
    }
    ';
        }

        echo '$sql="INSERT INTO '.$table.' (`'.str_replace('$','',implode("`,`", $tmpVars)).'`) VALUES (\'".';
        echo implode(".\"','\".",$tmpVars).".\"');\";\n";
        echo 'xdna_db::query($sql);'."\n";
        echo '}';
    }
    public static function lastId() {
        return self::$db->lastInsertId();
    }
    public static function nextval($idList,$idNode=NULL) {
        $sql = "SELECT nextval FROM xdna_sequences ";
        $where = "WHERE xdna_list=:idList";
        $bind = array("idList"=>$idList,"idNode"=>$idNode);
        if($idNode) {
            $where .=" AND id_node =:idNode";
        } else {
            $where .=" AND id_node IS NULL";
            $bind = array("idList"=>$idList);
        }
        $result = self::query($sql.$where,$bind);
        if($risposta = self::fetch_object($result)) {
            self::query("UPDATE `xdna_sequences` SET `nextval`= `nextval`+1 ".$where,$bind);
            return ($risposta->nextval+1);
        } else {
            self::query("INSERT INTO `xdna_sequences` (`xdna_list`, `nextval`, `id_node`) VALUES (:idList, 1, :idNode);",array("idList"=>$idList,"idNode"=>$idNode));
            return 1;
        }


    }*/
}

$dbautodisconnect = new xdb_autodisconnect(); // when this istance will removed, the database connection will closed