<?php
final class Skill {
	
	public $_id = "";
	public $_name = "";
	public $_abstract = "";
	public $_picture = "";
	public $_type = ""; // 1, 2, 3, u or p
	
	// Constructs Skill with parameters
	public function __construct($id, $name, $abstract, $type) {
		
		$this->_id = $id;
		$this->_name = $name;
		$this->_abstract = $abstract;
		
		switch($type) {
			case 5:
				$this->_type = "p";
				break;
			case 4:
				$this->_type = "u";
				break;
			default:
				$this->_type = $type < 1 || $type > 3 ? "" : $type;
				break;
		}
	}
	
	// Generates an array that can be easily JSON-encoded
	public function json() {
		
		return Array(
			"name" => $this->_name,
			"abstract" => $this->_abstract,
			"picture" => $this->_picture,
			"type" => $this->_type
		);
	}
}