<?php

// Website page
abstract class Page {
	
	public $_id;
	
	function __construct($id) {
		
		$this->_id = $id;
		
		// Initializing Viewbag
		global $viewBag;
		$viewBag["scripts"] = ["Other/jquery_1_8_2", "utilities_1_3_0"];
		$viewBag["styles"] = ["layout_2_0_0"];
		$viewBag["project"] = [
			"name" => "SMITROLL",
			"version" => "2.0.0"
		];
		$viewBag["page"] = $id;
	}
			
	function render($layout = "standard") {
		if(!isset($layout) || $layout !== "standard") $layout = "empty";
		include_once(__DIR__."/../App/Views/Layouts/$layout.php");
	}
}