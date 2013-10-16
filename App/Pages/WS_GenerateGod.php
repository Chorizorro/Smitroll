<?php

require_once(__DIR__."/../../Core/Page.php");
// require_once(__DIR__."/../Models/God.php");
require_once(__DIR__."/../Models/Item.php");
require_once(__DIR__."/../Modules/WebservicesHelper.php");
require_once(__DIR__.'/../Modules/SmitrollGen.php');
require_once(__DIR__."/../Cache/CacheManager.php");

class WS_GenerateGod extends Page {
	
	private $_gods = null;
	
	function __construct() {
		
		global $viewBag;
		parent::__construct("WS_generateGod");
		$viewBag["content"] = "WS/generateGod.php";
		$this->load();
	}
	
	function load() {
		
		global $viewBag;
		$errors = [];
        
        // Loading gods list
        $gods = isset($_GET["gods"]) ? $_GET["gods"] : "all";
        $invalidIds = [];
        if(SmitrollGen::checkGodIds($gods, $invalidIds)) 
            $this->_gods = $gods;
        else
            WebservicesHelper::pushError ($errors, 1, 'Invalid "gods" parameter', '"gods" must be a valid list of god ids separated by commas or "all". Invalid ids: '.(join(',', $invalidIds)).'.');
        unset($gods, $invalidIds);
		
		// Display errors list
		if(!empty($errors)) {
			$viewBag["generatedBuild"] = ["errors" => $errors];
			return;
		}
        unset($errors);
		
		// Creating the view bag
		$viewBag["generatedGod"] = [
            "success" => true,
            "data" => SmitrollGen::getRandomGod($this->_gods)
        ];
	}
}