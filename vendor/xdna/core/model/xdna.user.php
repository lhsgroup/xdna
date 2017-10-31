<?php

/**
 * Classe xdna
 *
 * @author luca
 */
class xdna_user {
   
	public static function doLogin($username,$password){
		$sql = "SELECT * FROM  `xdna_users` WHERE  `username` =  :username AND  `password` = :password";
		$result = xdna_db::query($sql,array("username" => $username, "password" => $password));
		if($row = xdna_db::fetch_object($result)){
			$_SESSION['username'] = $username;
		}
	}
	
}
?>
