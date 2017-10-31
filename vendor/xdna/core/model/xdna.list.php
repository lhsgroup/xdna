<?php

/**
 * Classe xdna
 *
 * @author luca
 */
class xdna_list {
    var $id;
    var $id_parent;
    var $name;
    var $xdna_set;
	var $customClass;
    private $parent=NULL;
	
    public function __construct($listNameOrId) {
        if(is_string($listNameOrId)) {
            $this->id = (int) self::getListByName($listNameOrId,$this->xdna_set,$this->customClass);
        } else if(is_int($listNameOrId)) {
            $this->id = $listNameOrId;
        } else {
            throw new Exception("The list name must be String or Int!");
        }
        $this->loadInfo();               
    }
    public function addElement() {
		return xdna::getObject($this->name);
    }
	
	public function each($function) {
		
	}
	
	public static function createList($name,$xDnaSet){
		$sql = "SELECT XDNA_LIST_ADD(NULL,'".$name."','".$xDnaSet."') as id";
		$result = self::query($sql);
		if($row = self::fetch_object($result)){
			if(isset($row->id)){
				return TRUE;
			}
		}
		return FALSE;
	
	}
	
	public function addChildList($name,$xDnaSet) {
		$sql = "SELECT XDNA_LIST_ADD('".$this->id."','".$name."','".$xDnaSet."') as id";
		$result = self::query($sql);
		if($row = self::fetch_object($result)){
			if(isset($row->id)){
				return TRUE;
			}
		}
		return FALSE;
	}
	
	public function remove(){
		$sql = "SELECT XDNA_LIST_DELETE('".$this->name."') as removed";
		$result = self::query($sql);
		if($row = self::fetch_object($result)){
			if(isset($row->removed)){
				return TRUE;
			}
		}
		return FALSE;
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
							if(end($v) == $m){
								$m =  rtrim($m,"]");
							}
							$m = explode("=",$m);
							$k = $m[0];												
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
    public static function find($pattern,$order=NULL,$by="ASC",$start = 0,$end = 50) {
		$start = (int) $start;
		$end = (int) $end;
		$lists=array();
    	$engined = self::enginePattern($pattern,$lists);
		$sql = "SELECT * FROM `xdna_lists` WHERE `name` IN ('".implode("','",$lists)."')";
		$result = self::query($sql);
		$rifLists = array();
		while($risposta = self::fetch_object($result)) {
			$rifLists[$risposta->name] = $risposta->id;
		}
		$lists = array_reverse($lists);
		$arrayFinder = array();
		$masterQueries = array();	
		$firstList = array();
		foreach($lists as $list){
			$tables = array();
			$idList = $rifLists[$list];
			if(reset($lists) == $list){
				$firstList["idList"] = $idList;
				$firstList["name"] = $list;
				if($order){
				$sql = "SELECT rowid FROM  `xdna_entities` WHERE `xdna_list` = '".$idList."'"; 
				} else {
					$sql = "SELECT rowid as entity_rowid, id_parent,id FROM  `xdna_entities` WHERE `xdna_list` = '".$idList."'"; 
				}
				$tables[] = $sql;
			} else {
				$tables[] = "SELECT rowid as entity_rowid FROM  `xdna_entities` WHERE `xdna_list` = '".$idList."'"; 
			}
			if(isset($engined[$list])){ 
				$params = $engined[$list];
				foreach($params as $uri => $value){
					$element = new xdna_element($uri);
					$operator = (strstr($value,'%')) ? "LIKE(".$value.")" : "=".$value;
					$query = "(SELECT entity_rowid FROM ".$element->table." WHERE `element_id` = ".$element->id." AND `search`".$operator.")";
					$tables[] = $query;
				}
			}
			$masterQueries[$list] = implode(" AND rowid IN",$tables);			
		}		
		$sqllone ="";
		$lastList="";
		foreach($masterQueries as $l=>$s) {
			if($sqllone) {
				$sqllone .= " AND id_parent IN(".$s.")";
			} else {
				$lastList= $l;
				$sqllone .=$s;
			}
		}
		if($order){
			$element = new xdna_element($order);
			$sqllone = "SELECT E.`rowid` as `entity_rowid`, E.id_parent,E.id FROM ".$element->table." as S INNER JOIN `xdna_entities` as E ON E.`rowid` = S.`entity_rowid` WHERE S.`element_id` = ".$element->id." AND E.`rowid` IN (".$sqllone.") ORDER BY S.`search` ".$by; 
		}else {
			$sqllone .= " ORDER BY `position` ".$by;
		}
		$sqllone .= " LIMIT ".$start." , ".$end."";
	
		$result = self::query($sqllone);
		while($row = self::fetch_object($result)){
			$arrayFinder[$row->entity_rowid] = new xdna($lastList,$row->id,$row->id_parent);
			$arrayFinder[$row->entity_rowid] = $arrayFinder[$row->entity_rowid]->getObject();
		}
		
		
		$searchResult = new xdna_collection($arrayFinder);
		$searchResult->is_xdna = TRUE;
		$searchResult->list_name = $firstList["name"];
		$searchResult->list_id = $firstList["idList"];
		return $searchResult;
		
    }
	
	
	
	protected static function query($sql,$params=array()){
		return xdna_db::query($sql);
	}
	
	protected static function fetch_object($result){
		return xdna_db::fetch_object($result);
	}
	
    public function __toString() {
        return $this->name;
    }
    public function __get($name) {
        if($name==='parent') {          
            // inizializzo il parent se esiste
            if($this->id_parent && $this->parent==NULL) {
                $this->parent = new xdna_list($this->id_parent);                 
            } 
            return $this->parent;            
        }
    }

    protected function loadInfo() {
        if($this->id) {
            if(isset(xdna_cache::$list_cache[$this->id])) {
                $this->id_parent =  xdna_cache::$list_cache[$this->id]['id_parent'];
                $this->name =  xdna_cache::$list_cache[$this->id]['name'];  
                $this->xdna_set = xdna_cache::$list_cache[$this->id]['xdna_set'];
            } else {
                // la cerco sul db
                $result = self::query("SELECT * FROM `xdna_lists` WHERE `id` = ".$this->id." LIMIT 0, 1");
                if($risposta = xdna_db::fetch_object($result)) {
                    xdna_cache::$list_cache[$risposta->id]['id'] = (int) $risposta->id;
                    xdna_cache::$list_cache[$risposta->id]['id_parent'] = (int) $risposta->id_parent;
                    xdna_cache::$list_cache[$risposta->id]['name'] = $risposta->name;
                    xdna_cache::$list_cache[$risposta->id]['xdna_set'] = (int) $risposta->xdna_set;
                    xdna_cache::$name_cache_list[$risposta->name]= (int) $risposta->id;
                    $this->id_parent =  xdna_cache::$list_cache[$this->id]['id_parent'];
                    $this->name =  xdna_cache::$list_cache[$this->id]['name']; 
                    $this->xdna_set = xdna_cache::$list_cache[$risposta->id]['xdna_set'];
                } else {
                    throw new Exception("List id ".$this->id." not found!");
                }
            }
        }
    }

    public static function getListByName($name,&$xdna_set=NULL,&$customClass=NULL,$live = NULL,&$defEl=NULL) {
        $name = trim($name);
       // controllo nella cache
        if(isset(xdna_cache::$name_cache_list[$name])) {
            $id = xdna_cache::$name_cache_list[$name];
			$xdna_set =  xdna_cache::$list_cache[$id]['xdna_set'];
			@$customClass = xdna_cache::$list_cache[$id]['customClass'];
			@$live = xdna_cache::$list_cache[$id]['live'];
			if(isset(xdna_cache::$list_cache[$id]['toStringElement'])) {
				$defEl = xdna_cache::$list_cache[$id]['toStringElement'];
			}
			return $id;
        } else {
            // la cerco sul db
            $result = self::query("SELECT xl.id,xl.id_parent,xl.name,xl.xdna_set,xs.customClass,xl.live,xs.toStringElement FROM `xdna_lists` AS xl INNER JOIN  `xdna_set` AS xs ON xl.xdna_set = xs.id  WHERE xl.`name` = '".$name."' LIMIT 0, 1");
            if($risposta = xdna_db::fetch_object($result)) {
                xdna_cache::$list_cache[$risposta->id]['id'] = (int) $risposta->id;
                xdna_cache::$list_cache[$risposta->id]['id_parent'] = (int) $risposta->id_parent;
                xdna_cache::$list_cache[$risposta->id]['name'] = $risposta->name;
                xdna_cache::$list_cache[$risposta->id]['xdna_set'] = (int) $risposta->xdna_set;
				xdna_cache::$list_cache[$risposta->id]['customClass'] = $risposta->customClass;
				xdna_cache::$list_cache[$risposta->id]['live'] = $risposta->live;				
                xdna_cache::$name_cache_list[$name]= (int) $risposta->id;
				$xdna_set = (int) $risposta->xdna_set;
				$customClass = xdna_cache::$list_cache[$risposta->id]['customClass'];
				$live = xdna_cache::$list_cache[$risposta->id]['live'];
				if($risposta->toStringElement) {
					$r = self::query("SELECT `uri` FROM `xdna_elements` WHERE `id`=".$risposta->toStringElement);
					if($r = xdna_db::fetch_object($r)) {
						xdna_cache::$list_cache[$risposta->id]['toStringElement'] = $r->uri;
						$defEl = xdna_cache::$list_cache[$risposta->id]['toStringElement'];
					}
				}
                return $risposta->id;
            } else {
                throw new Exception("List named '".$name."' not found!");
            }
        }
    }

}

?>
