<?php
/**
 * Classe xdna_collection
 *
 * @author luca
 */
class xdna_collection extends ArrayObject implements Ixdna {
	public static $cache;
	private static $TmpcacheArray= array();
	public static $record=false; 
	public $uid;
	public $list_name;
	public $list_id;
	public $parent_id;
	public $is_xdna;
	public $xdna_set;
	public $type_of_list="element";
	protected $xkey = array();
	
    public function __construct($array=array()) {    
        foreach($array as $key => $value) {
			$this->xkey[] = $key;
            if(is_array($value)){
                $value = new self($value);
            }
            $this->offsetSet($key, $value);
        }
    }
	
	public function subscribeParam($param,$lang=NULL) {
		if($this->uid) {
			if(!isset(self::$cache[$this->uid])) {
				self::$cache[$this->uid]= array();				
			} 
			self::$cache[$this->uid][$param] = $param;
		}
	}
   
   public function offsetGet ($index) {
		return parent::offsetGet($index);   
   }
   
   public function offsetSet ($index, $newval) {
	  
	   if(!isset($index) && $this->type_of_list=="star" && ($newval instanceof xdna)) {
	   		//TODO
			return $newval;
	   } else {
	   		return parent::offsetSet($index,$newval);
	   }
   }
	
   private static function getUniqueIdFunction($function) {
		 $refFunc = new ReflectionFunction($function);
		 return md5($refFunc);
	}	
    
    public function each($function) {
		$this->uid = xdna_collection::getUniqueIdFunction($function);		
        foreach($this as $k=>$v) {
			$v = $this[$k];
			if(isset($v->isXdna)) {
				$v->setCollection($this);
			}
            $function($k,$v);
        }		
		
    }
	
	public function __get($param) {
		if(method_exists($this,"x_".$param)){
			$function = "x_".$param;
			$classname = get_class($this);
			return call_user_func(array($classname,$function));
		}	
    }

    public function delete() {
        
    }

    public function get($param, $lang = NULL) {
        
    }
    

    public function set($param, $value, $lang = NULL) {
        foreach($this as $k => $v){
			$this[$k]->$param = $value;
		}
		return $this;
    }
	
	public function __set($param,$value){
		foreach($this as $k => $v){
			$this[$k]->$param = $value;
		}
	}
	public function remove(){
		foreach($this as $k => $v){
			$this[$k]->remove();
		}
		return $this;
	}
	public function commit(){
		foreach($this as $k => $v){
			$this[$k]->commit();
		}
	}
	
	protected static function enginePattern ($pattern){
	
		$pattern = explode(",",$pattern);
		$r = array();
		foreach($pattern as $v){
			$v= explode("=",$v);
			$k = trim($v[0]);
			$r[$k] = trim($v[1]);
		}
		return $r;
	}
	
	protected static function query($sql,$params=array()){
		return xdna_db::query($sql,$params);
	}
	protected static function fetch_object($result){
		return xdna_db::fetch_object($result);
	}
	
	public function find($pattern,$order=NULL,$by="ASC"){
		if(count($this->xkey) == 0){
			return array();
		}
		$params = self::enginePattern($pattern);	
		$sql = "SELECT * FROM `xdna_lists` WHERE `name` = '".$this->list_name."'; ";
		$result = self::query($sql);
		if($risposta = self::fetch_object($result)) {
			$list = $risposta;
		}
		$arrayFinder = array();
		$masterQueries = array();			
		$tables = array();	
		if($order) {	
			$sql = "SELECT rowid FROM  `xdna_entities` WHERE `xdna_list` = '".$list->id."' AND rowid IN (".implode(",",$this->xkey).")"; 	
		} else {
			$sql = "SELECT rowid as entity_rowid, id_parent,id FROM  `xdna_entities` WHERE `xdna_list` = '".$list->id."' AND rowid IN (".implode(",",$this->xkey).")"; 	
		}
		foreach($params as $uri => $value){
			$element = new xdna_element($uri);
			$operator = (strstr($value,'%')) ? "LIKE(".$value.")" : "=".$value;
			$sql .= " AND rowid IN (SELECT entity_rowid FROM ".$element->table." WHERE `element_id` = ".$element->id." AND `search` ".$operator.")";
		}
		if($order){
			$element = new xdna_element($order);
			$sql = "SELECT E.`rowid` as `entity_rowid`, E.id_parent,E.id FROM ".$element->table." as S INNER JOIN `xdna_entities` as E ON E.`rowid` = S.`entity_rowid` WHERE S.`element_id` = ".$element->id." AND E.`rowid` IN (".$sql.") ORDER BY S.`search` ".$by; 
		}else {
			$sql .= " ORDER BY `position` ".$by;
		}
		$result = self::query($sql);
		while($row = self::fetch_object($result)){
			$arrayFinder[$row->entity_rowid] = new xdna($this->list_name,$row->id,$row->id_parent);
			$arrayFinder[$row->entity_rowid] = $arrayFinder[$row->entity_rowid]->getObject();
		}
		$searchResult = new xdna_collection($arrayFinder);
		$searchResult->is_xdna = TRUE;
		$searchResult->list_name = $this->list_name;
		$searchResult->list_id = $this->list_id;
		return $searchResult;	
	}
	
	public function __toString(){
		$p = $this->toString;
		return $p;
	}
	
	public function x_header(){
		return "";
	}
	
	public function x_footer(){
		return "";
	}
	
	public function x_preview(){
		$s = $this->header;
		foreach($this as $xdna_object){
			if(@$xdna_object->isXdna){
				$s .= $xdna_object->preview;
			}
		}
		$s .= $this->footer;
		return $s;
	}
	
	public function x_toString(){
		$s = $this->header;
		foreach($this as $xdna_object){
			if(@$xdna_object->isXdna){
				$s .= $xdna_object->preview;
			}
		}
		$s .= $this->footer;
		return $s;
	}
	
	
	public function limit($start,$end){
		if(count($this->xkey) == 0){
			return array();
		}
		$sql = "SELECT rowid,id FROM  `xdna_entities` WHERE `xdna_list` = '".$this->list_id."' AND rowid IN (".implode(",",$this->xkey).") ORDER BY FIELD (rowid, ".implode(",",$this->xkey).") LIMIT ".(int) $start." , ".(int) $end;
		$result =self::query($sql);
		while($row = self::fetch_object($result)){
			$arrayFinder[$row->rowid] = new xdna($this->list_name,$row->id,$this->id_parent);
			$arrayFinder[$row->rowid] = $arrayFinder[$row->rowid]->getObject();
		}
		$searchResult = new xdna_collection($arrayFinder);
		$searchResult->is_xdna = TRUE;
		$searchResult->list_name = $this->list_name;
		$searchResult->list_id = $this->list_id;
		return $searchResult;
	
	}
	
	public function orderBy($param,$by = "ASC"){
		if(count($this->xkey) == 0){
			return array();
		}
		$element = new xdna_element($param);
		$sql = "SELECT E.`rowid` as `entity_rowid`, E.id_parent,E.id FROM ".$element->table." as S INNER JOIN `xdna_entities` as E ON E.`rowid` = S.`entity_rowid` WHERE S.`element_id` = ".$element->id." AND E.`rowid` IN (".implode(",",$this->xkey).") ORDER BY S.`search` ".$by; 
		$result =self::query($sql);
		while($row = self::fetch_object($result)){
			$arrayFinder[$row->entity_rowid] = new xdna($this->list_name,$row->id,$row->id_parent);
			$arrayFinder[$row->entity_rowid] = $arrayFinder[$row->entity_rowid]->getObject();
		}
		$searchResult = new xdna_collection($arrayFinder);
		$searchResult->is_xdna = TRUE;
		$searchResult->list_name = $this->list_name;
		$searchResult->list_id = $this->list_id;
		return $searchResult;	
	}
}

?>
