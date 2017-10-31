<?php

/**
 * Classe xdna
 *
 * @author luca
 */
class xdna extends ArrayObject implements Ixdna {
	public static $enableInherit = true;
	public $isXdna=true;
	public $parent_collection;
	public $elements=array();
	public $id;
	public $rowid;
	public $parent_id = NULL;
	public $virtualParent = FALSE;
	public $list_id;
	public $list_name;
    public $xdna_set;
	public $customClass;
	public $list_is_live = FALSE;
	public $xdna_elemets = array();
	public $_default_xdna_element="id";
	protected $xdna_lists = array();
	protected $xdna_native_lists = array();
	public $lists = array();
	protected $commit_elements = array();
	protected $commit_lists = array();
	protected $commit_native_lists = array();
	
	public function __construct($listName,$id = NULL,$paren_id=NULL) {
        $this->id = $id;
		$this->parent_id = $paren_id;
		$this->list_name = $listName;				
		$this->list_id = xdna_list::getListByName($listName,$this->xdna_set,$this->customClass,$this->list_is_live,$this->_default_xdna_element);
		$this->loadStructure();
		if(isset($this->id)){
			$this->loadRowId();
		}		
    }	
	
	protected function loadRowId(){
		if(isset(xdna_cache::$xdna_entities_cache[$this->list_id][$this->id]) && 0){
			$this->rowid = xdna_cache::$xdna_entities_cache[$this->list_id][$this->id];
		} else {
			$sql = "SELECT `rowid` FROM  `xdna_entities` WHERE  `id` = ".$this->id." AND  `xdna_set` = ".$this->xdna_set." AND  `xdna_list` = ".$this->list_id;
			$result = self::query($sql);
			if($row = self::fetch_object($result)){
				$this->rowid = $row->rowid;
				xdna_cache::$xdna_entities_cache[$this->list_id][$this->id] = $this->rowid;
			}
		}
	}
	
	protected function loadIdFromRowId(){
		if(isset(xdna_cache::$xdna_entities_cache[$this->list_id][$this->id])){
			$this->rowid = xdna_cache::$xdna_entities_cache[$this->list_id][$this->id];
		} else {		
			$sql = "SELECT `id` FROM  `xdna_entities` WHERE  `rowid` = :rowid ;";
			$result = self::query($sql,array("rowid" => $this->rowid));
			if($row = self::fetch_object($result)){
				$this->id = $row->id;
				xdna_cache::$xdna_entities_cache[$this->list_id][$this->id] = $this->rowid;
			}
		}
	
	}
	
	protected function loadParent(){
		$sql = "SELECT  `xdna_entities`.`id` ,  `xdna_lists`.`name` AS  `xdna_list` 
FROM  `xdna_entities` 
INNER JOIN  `xdna_lists` ON  `xdna_lists`.id =  `xdna_entities`.`xdna_list` 
WHERE `rowid` = (SELECT `id_parent` FROM `xdna_entities` WHERE `id_parent` is not null AND rowid = :rowid  LIMIT 0, 1)";
		$result = self::query($sql,array("rowid" => $this->rowid));
			if($row = self::fetch_object($result)){
				$p = new xdna($row->xdna_list,$row->id);
				return $p->getObject();
			}
			return NULL;
	}
	
	public function setCollection(&$collection) {
		$this->parent_collection = $collection;
	}
		
    public function __set($name, $value) {
		return $this->set($name,$value);
    }
	
    protected static function query($sql,$params=array()) {
        return xdna_db::query($sql,$params);
    }
	
	protected static function fetch_object($result){
		return xdna_db::fetch_object($result);
	}

    public function delete() {
        
    }

    public function __get($param) {
        return $this->get($param);
    }
	
	public function __toString(){
		$p = $this->toString;
		return $p;
	}
	public function __toMessage() {
		$p = $this->toString;
		return $p;
	}
	protected function getChannleForList($listName) {

		return "-".$listName;
	}
    public function commit() {
		$regenerate_cache = FALSE;
		$newAdd = FALSE;
		if(!$this->id){			
			$parent = ($this->parent_id) ? $this->parent_id : 0;
			$sql = "SELECT XDNA_ADD(".$this->xdna_set.",'".$this->list_name."',".$parent.") AS id";
			$result = self::query($sql);
			if($row = self::fetch_object($result)){
				$this->id = $row->id;
				$this->loadRowId();
				$regenerate_cache = TRUE;				
				$newAdd = TRUE;
			}
		}
		foreach($this->commit_elements as $table => $values){
			foreach($values as $lang => $params){
				foreach($params as $param => $value){
					if($regenerate_cache){
						xdna_cache::$xdna_entities_params_cache[$lang][$this->rowid][$param] = $value;
					}
					$sql = "SELECT *  FROM `".$table."` WHERE `entity_rowid` = :rowid AND `element_id` = :elementid AND `lang` = :lang";
					$result = self::query($sql,array("rowid" => $this->rowid,"elementid" => $param,"lang" => $lang));
					if($row = self::fetch_object($result)){
						$sqlUpdate = "UPDATE `".$table."` SET  `value` =  :value, `search` =  :value WHERE  `rowid` = :rowid;"; 
						$resultUpdate = self::query($sqlUpdate,array("value" => $value,"rowid" => $row->rowid));
					} else {
						$sqlInsert = "INSERT INTO `".$table."` (`rowid`, `entity_rowid`, `element_id`, `value`, `search`, `lang`) VALUES (NULL, ".$this->rowid.", :elementid, :value, :value, :lang);";
						$resultInsert = self::query($sqlInsert,array("elementid" => $param,"value" => $value,"lang" => $lang));
					}
				}
			}
		}
		foreach ($this->commit_lists as $rowid => $values){
			foreach($values as $key => $value){
				$list = $this->xdna_lists[$key];
				if($list->type == "element"){
					$sql = "SELECT * FROM  `xdna_bind_etities` WHERE  `rowid_parent` = :parentid AND `id_list` = :listid";
					$result = self::query($sql,array("parentid" => $rowid,"listid" => $list->id));
					if($row = self::fetch_object($result)){
						$sqlUpdate = "UPDATE `xdna_bind_etities` SET `rowid_entity` =  :value WHERE  `rowid_parent` = :parentid AND `rowid_entity` = :entityid AND `id_list` = :listid AND `virtual_id` = :virtual_id;";
						$resultUpdate = self::query($sqlUpdate,array("value" => $value->rowid,"parentid" => $this->rowid,"listid" => $list->id,"entityid" => $row->rowid_entity,"virtual_id" => $list->virtual_id)); 
					} else {
						$sqlInsert = "INSERT INTO `xdna_bind_etities` (`rowid_parent`, `rowid_entity`, `id_list`, `virtual_id`) VALUES (".$this->rowid.", ".$value->rowid.", ".$list->id.",".$list->virtual_id.");";
						$resultInsert = self::query($sqlInsert);
					}
				} else if($list->type == "star") {					
					foreach($value as $k => $v){
						$sql = "SELECT count(*) as C FROM  `xdna_bind_etities` WHERE  `rowid_parent` = ".$this->rowid." AND `rowid_entity` = ".$v->rowid." AND `id_list` = ".$list->id." AND `virtual_id` = ".$list->virtual_id;
						$result = self::query($sql);
						$row = self::fetch_object($result);
						if($row->C ==0){
							$sqlInsert = "INSERT INTO `xdna_bind_etities` (`rowid_parent`, `rowid_entity`, `id_list`,`virtual_id`) VALUES (".$this->rowid.", ".$v->rowid.", ".$list->id." ,".$list->virtual_id.");";
							$resultInsert = self::query($sqlInsert);
						}
					}
				}
			}
		}
		if($newAdd){
			$obj = $this->getObject();
			$obj->sendLiveMessage($this->parent_id.$obj->getChannleForList($this->list_name),$obj->__toMessage());
		}
    }

    public function set($name, $value, $lang = NULL) {
        if(array_key_exists($name,$this->xdna_elemets)){
			$table = $this->xdna_elemets[$name]->table;
			$element_id = $this->xdna_elemets[$name]->id;
			$lang = ($lang) ? $lang : xdna_client::$client->language;
			$this->commit_elements[$table][$lang][$element_id] = $value;
			if(isset($this->id)){
				xdna_cache::$xdna_entities_params_cache[$lang][$this->rowid][$name] = $value;
			}
		} else if(array_key_exists($name,$this->xdna_lists)){
			$bindList = $this->xdna_lists[$name];
			if($bindList->type == "element"){				
				$this->commit_lists[$this->rowid][$name] = $value;
				if(isset($this->id)){
					xdna_cache::$xdna_entities_bind_cache[$this->rowid][$name] = $value;
				}
			} else if($bindList->type == "star"){				
				$this->commit_lists[$this->rowid][$name][] = $value;
				if(isset($this->id)){
					xdna_cache::$xdna_entities_bind_cache[$this->rowid][$name] = $value;
				}
			}
		}  else if(array_key_exists($name,$this->xdna_native_lists)){
			$this->commit_native_lists[$this->rowid][$name][] = $value;
			if(isset($this->id)){
				xdna_cache::$xdna_entities_child_cache[$this->rowid][$name] = $value;
			}
		} else {
			throw new Exception("Uncaught exception. Param ".$name." dosen't exist.");
		}
    }
	private function getInherit($param,$lang=NULL) {		
		if($parent = $this->_parent) {
			if(array_key_exists($param,$parent->xdna_elemets)){
				$value = $parent->get($param,$lang);
				xdna_cache::$xdna_entities_params_cache[$lang][$this->rowid][$param] = $value;
				return $value;
			}
		} 
		return NULL;
		
		
	}
	public function get($param,$lang = NULL) {			
		if(is_object($this->parent_collection)) {
			if(get_class($this->parent_collection)=="xdna_collection") {
				$this->parent_collection->subscribeParam($param);
			}
		}
		if(method_exists($this,"x_".$param)){
			$function = "x_".$param;
			$classname = get_class($this);
			return call_user_func(array($classname,$function));
		}	
		if(isset($this->id) && $param=="_parent") {
			return $this->loadParent();
		}
		if(array_key_exists($param,$this->xdna_elemets)){
			
			if(isset($this->id)){
				$lang = xdna_client::$client->language;
				if(isset(xdna_cache::$xdna_entities_params_cache[$lang][$this->rowid][$param])){
					return xdna_cache::$xdna_entities_params_cache[$lang][$this->rowid][$param];
				} else {
					$element = $this->xdna_elemets[$param]; 
					$sql = "SELECT `value` FROM  `".$element->table."` WHERE `entity_rowid` = :entity AND `element_id` = :elementid AND `lang` = :lang ";
					$result = self::query($sql,array("entity" => $this->rowid, "elementid" => $element->id,'lang' => $lang));										
					if($row = self::fetch_object($result)){
						if($row->value) {							
							xdna_cache::$xdna_entities_params_cache[$lang][$this->rowid][$param] = $row->value;
							return $row->value;
						} 
					}else if (self::$enableInherit && $element->inherit) {							
						return $this->getInherit($param,$lang);							
					} else if (self::$enableInherit){
						return $element->defaultValue;
					} else {
						return NULL;
					}
				}
			} else {
				foreach($this->commit_elements as $table => $values){
					foreach($values as $tmp_lang => $params){
						foreach($params as $tmp_param => $tmp_value){
							$tmp_el = $this->xdna_elemets[$param];
							if($tmp_el->id == $tmp_param){
								return $tmp_value;
							}
						}
					}
				}
			}
			$element = $this->xdna_elemets[$param]; 
			return $element->defaultValue;
		} else if(array_key_exists($param,$this->xdna_lists)){				
			if(isset(xdna_cache::$xdna_entities_bind_cache[$this->rowid][$param])){
				return xdna_cache::$xdna_entities_bind_cache[$this->rowid][$param];
			} else {
				$bindList = $this->xdna_lists[$param];				
				$sql = "SELECT xe.* FROM `xdna_bind_etities` as xbl INNER JOIN `xdna_entities` as xe ON xe.rowid = xbl.rowid_entity WHERE xbl.`rowid_parent` = ".$this->rowid." AND xbl.`id_list` = ".$bindList->id." AND xbl.`virtual_id` = ".$bindList->virtual_id;							
				$result = self::query($sql);
				//Sigle bind;				
				if($bindList->type == "element"){					
					if($row = self::fetch_object($result)){
						$obj = new xdna($bindList->name,$row->id,$this->rowid);						
						$obj->getObject();
						xdna_cache::$xdna_entities_bind_cache[$this->rowid][$param] = $obj;
						xdna_cache::$xdna_entities_bind_cache[$this->rowid][$param]->virtualParent = TRUE;
						return xdna_cache::$xdna_entities_bind_cache[$this->rowid][$param];
					}
				} else if($bindList->type == "star") {
					$arrayBinds = array();				
					while($row = self::fetch_object($result)){
						$obj = new xdna($bindList->name,$row->id,$this->rowid);
						$obj->getObject();
						$arrayBinds[$row->rowid] = $obj;
						$arrayBinds[$row->rowid]->virtualParent = TRUE;
					}
					xdna_cache::$xdna_entities_bind_cache[$this->rowid][$param] = new xdna_collection($arrayBinds);
					xdna_cache::$xdna_entities_bind_cache[$this->rowid][$param]->type_of_list ='star';
					xdna_cache::$xdna_entities_bind_cache[$this->rowid][$param]->list_name = $bindList->name;
					xdna_cache::$xdna_entities_bind_cache[$this->rowid][$param]->list_id = $bindList->id;
					xdna_cache::$xdna_entities_bind_cache[$this->rowid][$param]->parent_id = $this->rowid;
					xdna_cache::$xdna_entities_bind_cache[$this->rowid][$param]->is_xdna = TRUE;
					xdna_cache::$xdna_entities_bind_cache[$this->rowid][$param]->xdna_set = $this->xdna_set;
					return xdna_cache::$xdna_entities_bind_cache[$this->rowid][$param];
				}
			}
		} else if(array_key_exists($param,$this->xdna_native_lists)){
			$list = $this->xdna_native_lists[$param];
			$child = array();
			$sql = "SELECT xe.* FROM `xdna_entities` AS xe INNER JOIN `xdna_lists` AS xl ON xl.id = xe.xdna_list WHERE xe.id_parent = ".$this->rowid." AND xe.xdna_list = ".$list->id;
			$result = self::query($sql);
			while($row = self::fetch_object($result)){
				$obj = new xdna($list->name,$row->id,$this->rowid);
				$obj->getObject();
				$child[$row->rowid] = $obj;
			}
			xdna_cache::$xdna_entities_child_cache[$this->rowid][$param] = new xdna_collection($child);
			xdna_cache::$xdna_entities_child_cache[$this->rowid][$param]->list_name = $param;
			xdna_cache::$xdna_entities_child_cache[$this->rowid][$param]->list_id = $list->id;
			xdna_cache::$xdna_entities_child_cache[$this->rowid][$param]->parent_id = $this->rowid;
			xdna_cache::$xdna_entities_child_cache[$this->rowid][$param]->is_xdna = TRUE;
			xdna_cache::$xdna_entities_child_cache[$this->rowid][$param]->xdna_set = $this->xdna_set;
			return xdna_cache::$xdna_entities_child_cache[$this->rowid][$param];
		} else {
			throw new Exception("Uncaught exception. Param ".$param." dosen't exist.");
		}		
		return NULL;
	}
	public function offsetGet ($index) {
		return $this->__get($index);   
   }
   public function offsetSet ($index, $newval) {
		return $this->__set($index,$newval);
   }
   public function x_preview(){
   		return $this->toString;
   }
   public function x_toString(){
	  	$defaultParam = $this->_default_xdna_element;
		return $this->$defaultParam;
   }
	
	protected static function enginePattern($pattern,&$arrayKeys) {
		$pattern = trim($pattern);
		$pattern = str_replace("\\.",chr(7),$pattern); // \. = (BELL)
		$pattern = explode(".",$pattern);		
		$idLists = array();
		$par = array();		
		foreach($pattern as $str) {
			if(trim($str)) {	
				$str = str_replace(chr(7),'.',$str);
				$list = explode("[",$str);
				if(isset($list[1])) {
					$par = array();				
					$keywords = preg_split("/\[(.*?)\]/", $list[1]);										
					foreach($keywords as $v) {
						$v = explode(",",$v);
						foreach($v as $m) {
							$m = explode("=",$m);
							$k=$m[0];
							$par[$k] = $m[1];
						}
					}					
				} else {
					$par = array();				
				}
				$list = $list[0];			
				$idLists[$list] =$par;
				$arrayKeys[] = $list;
			}
		}
		return $idLists;	
	}
	
	public function getObject($listName=NULL){
		if(!$listName){
			if(class_exists($this->customClass) && $this->customClass){
				return new $this->customClass($this->list_name,$this->id);
			} else {
				return $this;
			}
		} else {
			$customClass = NULL;
			$dnaSet = NULL;
			$list = xdna_list::getListByName($listName,$dnaSet,$customClass);		
			if(isset($customClass) && class_exists($customClass)){
				$obj = new $customClass($listName);		
			} else {
				return new xdna($listName);
			}
		}
		return $obj;
	}
	
	public function addElement($listName){
	 	$obj = $this->getObject($listName);
		$obj->parent_id = $this->rowid;
		return $obj;
	}
	
	public static function truncate(){
		$sql = "TRUNCATE TABLE xdna_entities";
		self::query($sql);
		
		$sql = "TRUNCATE TABLE xdna_silos_string";
		self::query($sql);
		
		$sql = "TRUNCATE TABLE xdna_silos_text";
		self::query($sql);
		
		$sql = "TRUNCATE TABLE xdna_sequences";
		self::query($sql);
		
		$sql = "TRUNCATE TABLE xdna_silos_date";
		self::query($sql);
		$sql = "TRUNCATE TABLE xdna_silos_boolean";
		self::query($sql);
	}
	    
	protected function loadStructure(){				
		if(isset(xdna_cache::$xdna_sets_cache[$this->xdna_set])){
			$this->xdna_elemets = xdna_cache::$xdna_sets_cache[$this->xdna_set];
		} else {
			//Elements
			$sql = "SELECT xe.* FROM  `xdna_bind_elements` AS xbd INNER JOIN `xdna_elements` as xe ON xbd.`id_element` = xe.`id` WHERE xbd.`id_set` = :dnaset";		
			$result = self::query($sql,array("dnaset" => $this->xdna_set));
			if(!isset(xdna_cache::$xdna_sets_cache[$this->xdna_set])){
				xdna_cache::$xdna_sets_cache[$this->xdna_set] = array();
			}
			while($row = self::fetch_object($result)){
				xdna_cache::$xdna_sets_cache[$this->xdna_set][$row->uri] = $row;
			}
			$this->xdna_elemets = xdna_cache::$xdna_sets_cache[$this->xdna_set];
		}
		if(isset(xdna_cache::$xdna_bind_cache[$this->xdna_set])){
			$this->xdna_lists = xdna_cache::$xdna_bind_cache[$this->xdna_set];
		} else {				
			//Lists Element
			$sql = "SELECT xl.*,xbl.`type`,xbl.`id` AS virtual_id,xbl.`bind_name` FROM  `xdna_bind_lists` AS xbl INNER JOIN `xdna_lists` as xl ON xbl.`target_list` = xl.`id` WHERE xbl.`id_set` = ".$this->xdna_set."";	
			$result = self::query($sql);
			if(!isset(xdna_cache::$xdna_bind_cache[$this->xdna_set])){
				xdna_cache::$xdna_bind_cache[$this->xdna_set] = array();
			}
			while($row = self::fetch_object($result)){
				if($row->type == "element"){					
					xdna_cache::$xdna_bind_cache[$this->xdna_set][$row->name] = $row;
					$this->lists['element'][$row->name] = $row;
				} else if($row->type == "star"){					
					xdna_cache::$xdna_bind_cache[$this->xdna_set][$row->bind_name] = $row;
					$this->lists['star'][$row->name] = $row;
				}
			}		
			$this->xdna_lists = xdna_cache::$xdna_bind_cache[$this->xdna_set];
			
		}
		if(isset(xdna_cache::$xdna_childList_cache[$this->xdna_set])){
			$this->xdna_native_lists = xdna_cache::$xdna_childList_cache[$this->xdna_set];	
		} else {
			//Child Lists
			if(!isset(xdna_cache::$xdna_childList_cache[$this->xdna_set])){
				xdna_cache::$xdna_childList_cache[$this->xdna_set] = array();
			}
			$sql = "SELECT * FROM  `xdna_lists` WHERE  `id_parent` = ".$this->list_id;
			$result = self::query($sql);
			while($row = self::fetch_object($result)){
				xdna_cache::$xdna_childList_cache[$this->xdna_set][$row->name] = $row;
				$this->lists['native'][$row->name] = $row;
			}			
			$this->xdna_native_lists = xdna_cache::$xdna_childList_cache[$this->xdna_set];
		}
		
	}
	
	public function remove(){		
		if($this->virtualParent){
			$this->unbind();
		} else {
			$this->destroy();
		}
	}
	
	protected function unbind() {
		$sql = "DELETE FROM `xdna_bind_etities` WHERE `rowid_parent` = ".$this->parent_id." AND `rowid_entity` = ".$this->rowid." AND `id_list` = ".$this->list_id;		
		self::query($sql);
	}
	
	protected function destroy() {
		$elements = $this->xdna_elemets;
		$tables = array();
		
		/**
		 * Native list
		 */
		$list = $this->xdna_native_lists;		
		foreach($list as $list){
			$listName = $list->name; 
			$nativeList = $this->$listName;
			foreach ($nativeList as $el){
				//echo $el."<br>";
				$el->remove();
			}	
		}
		
		/**
		 * Bind Element List
		 */
		if(isset($this->lists['element'])){
			$bindElementList = $this->lists['element'];			
			foreach($bindElementList as $k => $list){
				$name = $list->name;
				$el = $this->$name;
				if($el){				
					$el->remove();
				}
			}
		}		
		
		/**
		 * Bind Star Element
		 */
		if(isset($this->lists['star'])){
			$bindStarList = $this->lists['star'];
			foreach($bindStarList as $k => $list){
				$listName = $list->bind_name;
				foreach($this->$listName as $el){
					echo $el->remove();
				}
			}
		}
	
		
		foreach($elements as $k => $element){
			if(array_key_exists($element->table,$tables)){
				$tables[$element->table] = $element->table;				
				$sqlElement = "DELETE FROM `".$element->table."` WHERE `entity_rowid` = ".$this->rowid;				
				self::query($sqlElement);
			}
		}		
		
		
		
		$sql = "DELETE FROM `xdna_entities` WHERE `rowid` = ".$this->rowid;
		self::query($sql);
	}
	
	protected function sendLiveMessage($channel,$msg){		
		/*$thread = new xdna_liveMessage($channel,$msg);
		$thread->send();*/
	}
	
}
class XdnaBindOnTheFly extends ArrayObject {
	public function offsetSet($offset, $value) {
        if (is_null($offset)) {
        
        }
    }
}

?>