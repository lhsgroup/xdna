<?php

/**
 * Classe xdna
 *
 * @author luca/alberto
 */
function NOW(){
	return date("Y-m-d H:i:s");
}

class preventDistruct {

	public function __destruct(){
		xdna_db::disconnet();
	}

}

class xdna_db {
    private static $db;
    private static $isConnected=FALSE;
	
    public static function disconnet(){
    	self::$db = NULL;
    }
    
    public static function connect($dbUser,$dbPassword,$dbName=NULL,$host='localhost') {
        
        if($dbName==NULL) {
            $dbName=$dbUser;
        }       
        if(self::$db = new PDO("mysql:host=$host; dbname=$dbName", $dbUser,$dbPassword)) {
            self::$isConnected=TRUE;
        } else {
            throw new Exception("Unable to connect to database:".$dbName." - ".self::$db->error);
        }
    }
	public static function exec($sql,$params=array()) {
		$result = self::query($sql,$params);
		return new xdna_db_iterator($result);
	}
    public static function query($sql,$params=array()) {
		 if(!self::$isConnected){
            self::connect('xdna', 'password', 'xdna');
        }
		$result = self::$db->prepare($sql);
		if($params) {
			if(!$result->execute($params)) {
				$e = $result->errorInfo();				
				throw new Exception("SQL Error [".$sql."]<br>'".$e[2]);
			}
		} else {
			if(!$result->execute()) {
				$e = $result->errorInfo();
				throw new Exception("SQL Error [".$sql."]<br>'".$e[2]);
			}
		}
		
		return $result;       
    } 
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
		
		
	}
}
class xdna_db_iterator {
	var $dbobj;
	public function xdna_db_iterator($dbobj) {
		$this->dbobj;
	}
	 public function each($function) {
		while($risposta = xdna_db::fetch_object($this->dbobj)) {
			$function($risposta);
		}
    }
}
$preventDistruct = new preventDistruct();
?>
