<?php

/**
 * Classe xdna
 *
 * @author luca
 */


class xdna_set {

	private $id;
	private $name;
	private $customClass;
	private $toStringElement;
	private $elements = array();
	private $binds = array();
	
	public function __construct($id){
		$sql = "SELECT * FROM  `xdna_set` WHERE  `id` = :id";
		$result = xdna_db::query($sql,array('id' => $id));
		if($row = xdna_db::fetch_object($result)){
			$this->id = $row->id;
			$this->name = $row->name;
			$this->customClass = $row->customClass;
			$this->toStringElement = $row->toStringElement;
		} 
	}
	
	public function __get($param) {
		if($param == "elements"){
			return $this->xElementsList();
		} else if($param == "toStringElement"){
			$elements = $this->xElementsList();
			return isset($elements[$this->toStringElement]) ? $elements[$this->toStringElement]->name : NULL;
		} else if(isset($this->$param)){
			return $this->$param;
		}
	}
	
	private function xElementsList(){
		$sql = "SELECT `xdna_elements`.`uri`,`xdna_elements`.`id` FROM  `xdna_bind_elements` INNER JOIN `xdna_elements` ON `xdna_bind_elements`.`id_element` = `xdna_elements`.`id`  WHERE  `id_set` = :id";
		$result = xdna_db::query($sql,array('id' => $this->id));
		while($row = xdna_db::fetch_object($result)){
			$this->elements[$row->id] = new xdna_element($row->uri);
		}
		return $this->elements;
	}
	
	public function xBinds() {
		$sql = "SELECT `xdna_bind_lists`.*,`xdna_lists`.`name` FROM  `xdna_bind_lists` INNER JOIN `xdna_lists` ON `xdna_bind_lists`.`target_list` = `xdna_lists`.`id` WHERE  `xdna_bind_lists`.`id_set` = :id";
		$result = xdna_db::query($sql,array('id' => $this->id));		
		while($row = xdna_db::fetch_object($result)){
			if($row->type == "star"){	
				$this->binds[$row->type][$row->bind_name] = $row;
			} else {
				$this->binds[$row->type][$row->name] = $row;
			}
		}		
		return $this->binds;
	}
	
	public function addBindStar($name,$targetList){
		$binds = $this->xBinds();		
		if(!isset($binds['star'][$name])){
			$sql = "INSERT INTO `xdna_bind_lists` (`id`, `id_set`, `target_list`, `type`, `bind_name`, `live`) VALUES (NULL, :xDnaSet, :target, 'star', :name, '0');";		
			$result = xdna_db::query($sql,array('xDnaSet' => $this->id,"target" => $targetList,"name" => $name));			
			if($result){
				return TRUE;
			}
		}
		
		return FALSE;
	}	
	
	public function removeBindStar($name){
		$sql = "DELETE FROM  `xdna_bind_lists` WHERE `bind_name` = :name AND type = 'star' AND id_set = :xdnaSet;";
		$result = xdna_db::query($sql,array('name' => $name, "xdnaSet" => $this->id));
		if($result){
			return TRUE;
		}
		return FALSE;
	}

	
	public function addBindElement($name,$targetList){
		$binds = $this->xBinds();
		if(!isset($binds['element'][$name])){
			$sql = "INSERT INTO `xdna_bind_lists` (`id`, `id_set`, `target_list`, `type`, `bind_name`, `live`) VALUES (NULL, :xDnaSet, :target, 'element', NULL, '0');";
			$result = xdna_db::query($sql,array('xDnaSet' => $this->id,"target" => $targetList));
			if($result){
				return TRUE;
			}
		}
	
		return FALSE;
	}
	
	public function removeBindElement($id){
		$sql = "DELETE FROM  `xdna_bind_lists` WHERE `target_list` = :id AND type = 'element' AND id_set = :xdnaSet ;";
		$result = xdna_db::query($sql,array('id' => $id , "xdnaSet" => $this->id));
		if($result){
			return TRUE;
		}
		return FALSE;
	}
	
	public function addElement($element_id){
		$sql = "INSERT INTO `xdna_bind_elements` (`id_set` ,`id_element`,`position`) VALUES ( :xdnaSet, :element_id,  '');";
		$result = xdna_db::query($sql,array('element_id' => $element_id , "xdnaSet" => $this->id));
		if($result){
			return TRUE;
		}
		return FALSE;
	}
	
	public function removeElement($element_id){
		$sql = "DELETE FROM `xdna_bind_elements` WHERE `id_set` = :xDnaSet AND `id_element` = :element_id";
		$result = xdna_db::query($sql,array('element_id' => $element_id , "xDnaSet" => $this->id));
		if($result){
			return TRUE;
		}
		return FALSE;
	}
	
	public function update($name,$customClass,$elements){
		
		$xDnaSetElements = $this->xElementsList();
		foreach($elements as $element){
			if(!isset($xDnaSetElements[$element])){
				$this->addElement($element);			
			}
		}
		foreach($xDnaSetElements as $xDnaSetElement){
			if(!isset($elements[$xDnaSetElement->id])){
				$this->removeElement($xDnaSetElement->id);
			}
		}
		
		$sql = "UPDATE `xdna_set` SET  `name` =  :name , `customClass` =  :customClass WHERE  `id` = :xDnaSet;";
		$result = xdna_db::query($sql,array('name' => $name ,'customClass' => $customClass , "xDnaSet" => $this->id));
		if($result){
			return TRUE;
		}
		return FALSE;
	}
	
	public function remove(){
		$sql = "DELETE FROM `xdna_set` WHERE `xdna_set`.`id` = :id;";
		$result = xdna_db::query($sql,array('id' => $this->id));
		if($result){
			return TRUE;
		}
		return FALSE;
	}	
	
	public static function create($name,$customClass,$elements,$toString){
		$sql = "INSERT INTO `xDNA`.`xdna_set` (`id`, `name`, `customClass`, `toStringElement`) VALUES (NULL, :name, :customClass, :toString);";
		$result = xdna_db::query($sql,array('name' => $name ,'customClass' => $customClass ,"toString" => $toString));		
		if($result){
			$xDnaSet_id = xdna_db::lastId();
			$xDnaSet = new xdna_set($xDnaSet_id);
			foreach($elements as $element){
				$xDnaSet->addElement($element);				
			}
			return $xDnaSet_id;
		}
		return FALSE;
	}
}

/*
class xdna_element {
    var $id;
    var $name;
    var $type='string';
    var $index=FALSE;
    var $required=FALSE;
    var $uri;
    var $note;
    var $defaultValue;
    var $table;
    var $multilanguage=FALSE;
    var $inherit=FALSE;
    public static function create($name, $uri, $table,  $type = 'string', $defaultValue=NULL,$index = FALSE, $required = FALSE, $multilanguage = FALSE, $inherit = FALSE,$note='') {
        if(!xdna_db::ckUnique('xdna_elements', $name, 'name')) {
            throw new Exception("The name '".$name." already exist on table xdna_elements and must be unique");
        }
		if(!xdna_db::ckUnique('xdna_elements', $uri, 'uri')) {
            throw new Exception("The uri '".$uri." already exist on table xdna_elements and must be unique");
        }
		
        $sql="INSERT INTO xdna_elements (`name`,`type`,`index`,`required`,`uri`,`note`,`defaultValue`,`table`,`multilanguage`,`inherit`) VALUES ('".$name."','".$type."','".$index."','".$required."','".$uri."','".$note."','".$defaultValue."','".$table."','".$multilanguage."','".$inherit."');";
        xdna_db::query($sql);
    }
	public function edit($ob) {
		$sql = "UPDATE xdna_elements SET";
		$commit=false;
		if(isset($ob->name)) {
			if(!xdna_db::ckUnique('xdna_elements', $ob->name, 'name')) {
            	throw new Exception("The name '".$ob->name." already exist on table xdna_elements and must be unique");
        	} else {
				$sql.="name ='".mysql_real_escape_string($ob->name)."' ";
				$commit=true;
			}			
		}
		if(isset($ob->uri)) {
			if(!xdna_db::ckUnique('xdna_elements', $ob->uri, 'uri')) {
            	throw new Exception("The name '".$ob->uri." already exist on table xdna_elements and must be unique");
        	} else {
				$sql.="uri ='".$ob->uri."' ";
				$commit=true;
			}			
		}
		
	}
}*/
?>
