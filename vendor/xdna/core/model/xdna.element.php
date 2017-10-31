<?php

/**
 * Classe xdna
 *
 * @author luca
 */
class xdna_element {
	
    private $id;
    private $name;
    private $type;
    private $index=FALSE;
    private $required=FALSE;
    private $uri;
    private $note;
    private $defaultValue;
    private $table;
    private $multilanguage=FALSE;
    private $inherit=FALSE;
    
	public function __construct($uri){		
		$sql = "SELECT `xdna_elements`.*,`xdna_types`.`type` FROM `xdna_elements` INNER JOIN `xdna_types` ON `xdna_types`.`tabella` = `xdna_elements`.`table` WHERE  `uri` = :uri";
		$result = xdna_db::query($sql,array('uri' => $uri));
		if($row = xdna_db::fetch_object($result)){
			$this->name = $row->name;
			$this->id = $row->id;
			$this->uri = $row->uri;
			$this->required = $row->required;
			$this->defaultValue = $row->defaultValue;
			$this->multilanguage = $row->multilanguage;
			$this->inherit = $row->inherit;
			$this->table = $row->table;
			$this->type = $row->type;
		}
	
	}
	
	public function __get($param){
		if(isset($this->$param)){
			return $this->$param;
		}
		
	}
	
	public static function create($name, $uri, $type , $defaultValue=0 , $index = 0, $required = 0, $multilanguage = 0, $inherit = 0,$note=""){		
		$table = self::tableFromType($type); 
		$sql = "SELECT XDNA_ELEMENTS_ADD('".$name."','".$type."','".$index."','".$required."','".$uri."','".$note."','".$defaultValue."','".$table."','".$multilanguage."','".$inherit."') AS id";
		$result = xdna_db::query($sql);
		if($row = xdna_db::fetch_object($result)){
			if(isset($row->id)){
				return TRUE;
			}
		}
		return FALSE;
		
	}
	
	private static function tableFromType($type_id){	
		$sql = "SELECT `tabella` FROM  `xdna_types` WHERE `id` = :type_id";
		$result = xdna_db::query($sql,array('type_id' => $type_id));		
		if($row = xdna_db::fetch_object($result)){
			return $row->tabella;
		}
		return NULL;
	}
	
	/*
	public static function create($name, $uri, $table,  $type = 'string', $defaultValue=NULL,$index = FALSE, $required = FALSE, $multilanguage = FALSE, $inherit = FALSE,$note='') {
        if(!xdna_db::ckUnique('xdna_elements', $name, 'name')) {
            throw new Exception("The name '".$name." already exist on table xdna_elements and must be unique");
        }
		if(!xdna_db::ckUnique('xdna_elements', $uri, 'uri')) {
            throw new Exception("The uri '".$uri." already exist on table xdna_elements and must be unique");
        }
		
        $sql="INSERT INTO xdna_elements (`name`,`type`,`index`,`required`,`uri`,`note`,`defaultValue`,`table`,`multilanguage`,`inherit`) VALUES ('".$name."','".$type."','".$index."','".$required."','".$uri."','".$note."','".$defaultValue."','".$table."','".$multilanguage."','".$inherit."');";
        xdna_db::query($sql);
    }*/
	
	
	public function remove(){
		$sql = "DELETE FROM `xdna_elements` WHERE `id` = :id;";
		$result = xdna_db::query($sql,array('id' => $this->id));
		if($result){
			return TRUE;
		}
		return FALSE;
	}
	
	public function edit($name,$uri,$defaultValue,$required,$multilanguage,$inherit) {		
		//TODO: da modificare il prima possibile
		$sql = "UPDATE `xdna_elements` SET  `name` =  '".$name."', `uri` =  '".$uri."', `defaultValue` =  '".$defaultValue."',`required` = ".$required." ,`multilanguage` =  '".$multilanguage."', `inherit` =  '".$inherit."' WHERE `id` = ".$this->id;
		$result = xdna_db::query($sql);
		if($result){
			return TRUE;
		}
		return FALSE;
	}
}
?>
