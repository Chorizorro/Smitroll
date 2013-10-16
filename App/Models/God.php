<?php

require_once(__DIR__."/Skill.php");

final class God {
	
	public $_id = 0;
	public $_name = "";
	public $_title = "";
	public $_pantheon = "";
	public $_picture = "";
	public $_skills = Array();
	
	// Constructs God from a dataset loaded from official SMITE API
	public function __construct($data) {
	
		$this->_id = $data["id"];
		$this->_name = $data["Name"];
		$this->_title = $data["Title"];
		$this->_pantheon = $data["Pantheon"];
		
		// Skills
		for($i = 1; $i <= 5; ++$i) {
			$newSkill = new Skill($data['AbilityId'.$i], $data['Ability'.$i], $data['abilityDescription'.$i]['itemDescription']['description'], $i);
			$this->_skills[$newSkill->_type] = $newSkill;	
		}
	}
	
	// Generates an array that can be easily JSON-encoded
	public function json($small = false) {
	
		if($small !== true)
			$small = false;
		
		// Small JSON object
		if($small)
			return Array(
				"id" => $this->_id,
				"name" => $this->_name,
				"title" => $this->_title,
				"picture" => $this->_picture,
			);
		// Complete JSON object
		return Array(
			"id" => $this->_id,
			"name" => $this->_name,
			"title" => $this->_title,
			"pantheon" => $this->_pantheon,
			"picture" => $this->_picture,
			"skills" => $this->skillsJson(),
		);
	}
	
	private function skillsJson() {
		
		$result = Array();
		$skills = $this->_skills;
		
		foreach($skills as $key => $value)
			$result[$key] = $value->json();
		
		return $result;
	}
}