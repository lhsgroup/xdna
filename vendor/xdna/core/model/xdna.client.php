<?php
/**
 * Classe xdna
 *
 * @author luca
 */
class xdna_client extends xdna_db {

	public static $client = NULL;
	public $browser;
	public $language;
	public $user;
	public $isLogged = NULL;
		
	public function __construct(){
		$this->browser = $_SERVER['HTTP_USER_AGENT'];
		$this->language = "ita";
		if(isset($_SESSION)){
			if(array_key_exists("user",$_SESSION)){
				$this->user = new xdna("users",$_SESSION['user']);
				$this->isLogged = TRUE;
			}
		}
	}
	
	public static function init(){
		if(self::$client){
			return self::$client;
		}
		self::$client = new xdna_client();
		return self::$client;
	}
	
	public function __set($param,$value){
		$this->$param = $value;
	}
	
}
xdna_client::init();
?>
