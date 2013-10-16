<?php

require_once(__DIR__."/../../Core/Page.php");

// About Page
class P_Changelog extends Page {
	
	function __construct() {
		
		parent::__construct("changelog");
		
		// Constructing page ViewBag
		global $viewBag;
		$viewBag["title"] = "Smitroll: Website changelog";
		$viewBag["content"] = "changelog.php";
		array_push($viewBag["styles"], "changelog_1_0_0");
	}
}