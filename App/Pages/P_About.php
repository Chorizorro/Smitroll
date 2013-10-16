<?php

require_once(__DIR__."/../../Core/Page.php");

// About Page
class P_About extends Page {
	
	function __construct() {
		
		parent::__construct("about");
		
		// Constructing page ViewBag
		global $viewBag;
		$viewBag["title"] = "Smitroll: Dafuq is that shit?";
		$viewBag["content"] = "about.php";
		// array_push($viewBag["styles"], "about_1_0_0");
	}
}