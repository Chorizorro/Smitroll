<?php

require_once(__DIR__."/../../Core/Page.php");
// require_once(__DIR__."/../Models/God.php");
require_once(__DIR__."/../Models/Item.php");
require_once(__DIR__."/../Modules/WebservicesHelper.php");
require_once(__DIR__.'/../Modules/SmitrollGen.php');
require_once(__DIR__."/../Cache/CacheManager.php");

class WS_GenerateBuild extends Page {
	
	private $_toCompute = Array();
	private $_gods = null;
	private $_skills = null;
	private $_build = null;
	
	function __construct() {
		
		global $viewBag;
		parent::__construct("WS_generateBuild");
		$viewBag["content"] = "WS/generateBuild.php";
		$this->load();
	}
	
	function load() {
		
		global $viewBag, $cfg;
		$errors = [];
		
		/*
		 * Loading parameters
		 */
		
		// Loading gods list
		if(isset($_GET["gods"])) {
			$this->_toCompute["gods"] = true;
            $gods = $_GET["gods"];
			$invalidIds = [];
			if(SmitrollGen::checkGodIds($gods, $invalidIds)) 
                $this->_gods = $gods;
            else
                WebservicesHelper::pushError ($errors, 1, 'Invalid "gods" parameter', '"gods" must be a valid list of god ids separated by commas or "all". Invalid ids: '.(join(',', $invalidIds)).'.');
            unset($gods, $invalidIds);
		}
		
		// Loading skill randomization type
		if(isset($_GET["skills"])) {
			$this->_toCompute["skills"] = true;
			$skills = $_GET["skills"];
			if($skills == 0 || $skills == 1)
				$this->_skills = $skills == 1 ? "hard" : "soft";
			else
				WebservicesHelper::pushError ($errors, 2, "Invalid 'skills' parameter", "'skills' parameter must be 0 (soft) or 1 (hard). Received \"".$skills."\"");
			unset($skills);
		}
		
		// Loading build randomization type
		if(isset($_GET["build"])) {
			$this->_toCompute["build"] = true;
			$build = $_GET["build"];
			if($build == 0 || $build == 1)
				$this->_build = $build == 1 ? "hard" : "soft";
			else
				WebservicesHelper::pushError ($errors, 3, "Invalid 'build' parameter", "'build' parameter must be 0 (soft) or 1 (hard). Received \"".$build."\"");
			unset($build);
		}
		
		// Checking if we have any kind of randomization
		if(empty($this->_toCompute))
			WebservicesHelper::pushError ($errors, 0, "No data to randomize", "No parameter was found for randomization. At least one parameter in 'gods', 'skills' or 'build' must be defined");
		
		// Display errors list
		if(!empty($errors)) {
			$viewBag["generatedBuild"] = ["errors" => $errors];
			return;
		}
        unset($errors);
        
		/*
		 * Randomization process
		 */
		
		$result = [];
		
		// Processing god
		if(isset($this->_toCompute["gods"]) && $this->_toCompute["gods"] === true)
            $result["god"] = SmitrollGen::getRandomGod($this->_gods);
        
		// Processing skill order
		if(isset($this->_toCompute["skills"]) && $this->_toCompute["skills"] === true) {
            // TODO Externalize in SmitrollGen class
			$skillList = Array(
				"list" => Array(),
				"type" => "hard"
			);
			// Soft random generation
			if($this->_skills == "soft") {
				$skillList["type"] = "soft";
				$p = Array("1", "2", "3");
				$order = Array(array_splice($p, mt_rand(0, 2) , 1)[0]);
				array_push($order, array_splice($p, mt_rand(0, 1) , 1)[0]);
				array_push($order, $p[0]);
				$skillList["order"] = $order;
				$skillList["list"] = Array(
					$order[0], $order[1], $order[0], $order[2], "u",
					$order[0], $order[0], $order[1], "u", $order[0],
					$order[1], $order[1], "u", $order[1], $order[2],
					$order[2], "u", $order[2], $order[2], "u",
				);
				unset($p, $order);
			}
			// Hard random generation
			else {
				$limitations = Array(
					"n" => Array(1, 3, 5, 7, 9),
					"u" => Array(5, 9, 13, 17, 20)
				);
				$ranks = Array(
					"1" => 0,
					"2" => 0,
					"3" => 0,
					"u" => 0
				);
				// Randomizing skill list
				for($i = 1 ; $i <= 20 ; $i++) {
					$possibilities = Array();
					// Computing possibilities
					foreach($ranks as $key => $r) {
						$lim = $limitations[$key === "u" ? "u" : "n"];
						if($r !== 5 && $lim[$r] <= $i) array_push($possibilities, $key);
					}
					unset($key, $r, $lim);
					// Getting one random value from possibilities
					$count = count($possibilities);
					$skillResult = $count == 1 ? $possibilities[0] : $possibilities[mt_rand(0, $count - 1)];
					array_push($skillList["list"], (string)$skillResult);
					$ranks[$skillResult]++;
				}
				unset($i, $skillResult, $ranks, $limitations, $possibilities, $count);
			}
			$result["skills"] = $skillList;
			unset($skillList);
		}
		
		// Processing build
		if(isset($this->_toCompute["build"]) && $this->_toCompute["build"] === true) {
            $result["build"] = SmitrollGen::getRandomBuild($this->_build);
		}
        
		// Setting the result as a data
        // TODO Externalize in SmitrollGen class
		$result["permalink"] = "http" . (array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] !== "off" ? "s" : "") . "://" . $_SERVER["SERVER_NAME"] .$cfg["path"]["rel"]."generator"
			.(isset($result["god"]) ? "/god=".$result["god"]["id"] : "")
			.(isset($result["skills"]) ? "/skills-".($this->_skills)."=".join(",", $result["skills"]["list"]) : "");
		if(isset($result["build"])) {
			$result["permalink"] .= "/build-".($this->_build)."=";
			foreach($result["build"]["items"] as $item)
				$result["permalink"] .= $item['id'].",";
			$result["permalink"] = substr($result["permalink"], 0, -1)."-";
			foreach($result["build"]["actives"] as $active)
				$result["permalink"] .= $active['id'].",";
			$result["permalink"] = substr($result["permalink"], 0, -1);
		}
        
		// Creating the view bag
		$viewBag["generatedBuild"] = Array(
			"data" => $result,
			"success" => true
		);
	}
}