<?php
abstract class DatabaseHelper {
	
	static function securize($param) {
		if(!is_string($param))
			$param = (string) $param;
		return str_replace("'", "''", $param);
	}
	
	static function getConnection() {
		global $cfg;
		$dbCfg = $cfg["db"];
		try {
			$connection = mysqli_connect($dbCfg["host"], $dbCfg["user"], $dbCfg["password"], $dbCfg["database"]);
		}
		catch(Exception $e) {
			error_log(__CLASS__ . "::" . __FUNCTION__ . ": Error ".  mysqli_connect_errno().": \"".mysqli_connect_error()."\"");
			return null;
		}
		return $connection;
	}
	
	static function isIdValid($id) {
		if(empty($id)) return false;
		if(!is_array($id)) $id = Array($id);
		foreach($id as $item) {
			if(gettype($item) == "string") $item = intval($item);
			if(!(gettype($item) === "integer" && $item > 0)) return false;
		}
		return true;
	}
}