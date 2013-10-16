<?php

require_once(__DIR__."/../../Apoconfig.php");
require_once(__DIR__."/../Pages/P_Error.php");
require_once(__DIR__."/../Pages/P_Generator.php");
require_once(__DIR__."/../Pages/P_Team.php");
require_once(__DIR__."/../Pages/P_About.php");
require_once(__DIR__."/../Pages/P_Changelog.php");
require_once(__DIR__."/../Pages/WS_GenerateBuild.php");
require_once(__DIR__."/../Pages/WS_GenerateBuildTeam.php");
require_once(__DIR__."/../Pages/WS_GenerateGod.php");

abstract class MainController {
	
	public static function Generator() {
		
		// Constructing page
		global $page;
		$page = new P_Generator();
		$page->render();
	}
	
	public static function Team() {
		
		// Constructing page
		global $page;
		$page = new P_Team();
		$page->render();
	}
	
	public static function Error() {
		
		// Retrieving error code
		$code = isset($_GET["code"]) ? intval($_GET["code"]) : 500 ;
		
		// Constructing page
		global $page;
		$page = new P_Error($code);
		$page->render();
	}
	
	public static function About() {
		
		// Constructing page
		global $page;
		$page = new P_About();
		$page->render();
	}
	
	public static function Changelog() {
		
		// Constructing page
		global $page;
		$page = new P_Changelog();
		$page->render();
	}
	
	public static function WS_GenerateBuild() {
		
		// Constructing page
		global $page;
		$page = new WS_GenerateBuild();
		$page->render(null);
	}
	
	public static function WS_GenerateBuildTeam() {
		
		// Constructing page
		global $page;
		$page = new WS_GenerateBuildTeam();
		$page->render(null);
	}
	
	public static function WS_GenerateGod() {
		
		// Constructing page
		global $page;
		$page = new WS_GenerateGod();
		$page->render(null);
	}
}
