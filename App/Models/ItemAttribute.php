<?php
final class ItemAttribute {
	
	public $_description = "";
	public $_value = "";
	public $_realValue = 0;
	
	// Constructs Item from a dataset loaded from official SMITE API
	public function __construct($data) {
		
		$this->_description = $data["Description"];
		$this->_value = $data["Value"];
		
		// Computing real numeric value
		$matches = Array();
		if(preg_match("/\+(\d+)%?/i", $data["Value"], $matches) && count($matches) >= 2)
			$this->_realValue = intval($matches[1]);
	}
	
	public function json() {
		return Array(
			"description" => $this->_description,
			"value" => $this->_value,
			"realValue" => $this->_realValue
		);
	}
}