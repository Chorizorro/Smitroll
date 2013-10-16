<?php

require_once(__DIR__."/../../Core/Page.php");
// require_once(__DIR__."/../Models/God.php");
require_once(__DIR__."/../Models/Item.php");
require_once(__DIR__."/../Modules/WebservicesHelper.php");
require_once(__DIR__.'/../Modules/SmitrollGen.php');
require_once(__DIR__."/../Cache/CacheManager.php");

class WS_GenerateBuildTeam extends Page {
	
	private $_teams = [];
	private $_build = null;
	
	function __construct() {
		
		global $viewBag;
		parent::__construct("WS_generateBuildTeam");
		$viewBag["content"] = "WS/generateBuildTeam.php";
		$this->load();
	}
	
	function load() {
		
		global $viewBag, $cfg;
		$errors = [];
		
		/*
		 * Loading parameters
		 */
        
        // Loading teams
		if(!isset($_GET["teams"]) || $_GET['teams'] === '')
            WebservicesHelper::pushError ($errors, 1, "'teams' parameter is mandatory.", "'teams' parameter must contain the name of team members.");
        else {
            $teams = explode('-', $_GET['teams'], 3);
            if(count($teams) > 2)
				WebservicesHelper::pushError ($errors, 2, "Invalid 'teams' parameter", "'teams' parameter must contain 1 to 2 teams.");
            else {
                $result = [[], []];
                foreach($teams as $i => $team) {
                    if($team === '')
                        continue;
                    $members = explode(',', $team, 6);
                    if(count($members) > 5)
                        WebservicesHelper::pushError ($errors, 3, "Invalid 'teams' parameter", "A team must be composed of at most 5 members.");
                    else {
                        $invalidMembers = [];
                        foreach($members as $member) {
                            if(preg_match("/[a-z0-9]/i", $member)) {
                                if(array_search($member, array_merge($result[0], $result[1]), true) === false) {
                                    $result[$i][] = $member;
                                    continue;
                                }
                                WebservicesHelper::pushError ($errors, 6, "Invalid 'teams' parameter", "Duplicated member name \"$member\".");
                                continue;
                            }
                            $invalidMembers[] = $member;
                        }
                        if(!empty($invalidMembers))
                            WebservicesHelper::pushError ($errors, 4, "Invalid 'teams' parameter", 'Team '.($i +  1).' contains invalid members: '.join(', ', $invalidMembers));
                    }
                }
                if(empty($result[0]) && empty($result[1]))
                    WebservicesHelper::pushError ($errors, 5, "Invalid 'teams' parameter", 'The aren\'t any members in both teams');
                else
                    $this->_teams = $result;
            }
            unset($members, $member, $teams, $team, $i, $result);
        }
		
		// Loading build randomization type
		if(isset($_GET["build"])) {
			$this->_toCompute["build"] = true;
			$build = $_GET["build"];
			if($build == 0 || $build == 1)
				$this->_build = $build == 1 ? "hard" : "soft";
			else
				WebservicesHelper::pushError ($errors, 7, "Invalid 'build' parameter", "'build' parameter must be 0 (soft) or 1 (hard). Received \"".$build."\"");
			unset($build);
		}
		
		// Display errors list
		if(!empty($errors)) {
			$viewBag["generatedBuild"] = ["errors" => $errors];
			return;
		}
        unset($errors);
        
		/*
		 * Randomization process
		 */
		
		$result = [
            'teams' => [[], []],
            'permalink' => ''
        ];
        
        // Processing teams
        foreach($this->_teams as $i => $team) {
            
            // Avoiding empty teams
            $count = count($team);
            if($count === 0)
                continue;
            
            // Generating gods for all team members
            $gods = SmitrollGen::getRandomGod("all", $count);
            if($count === 1)
                $gods = [$gods];
            unset($count);
            
            // Generating members with build one by one
            $godId = 0;
            $teamResult = [];
            foreach($team as $memberName) {
                $teamResult[$memberName] = [
                    "god" => $gods[$godId++],
                    "build" => SmitrollGen::getRandomBuild($this->_build)
                ];
            }
            $result['teams'][$i] = $teamResult;
        }
        unset($i, $godId, $team, $memberName);
		$permalink = "http" . (array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] !== "off" ? "s" : "") . "://" . $_SERVER["SERVER_NAME"] .$cfg["path"]["rel"]."team";
        
        // Build
        $teams = $result['teams'];
        $godMin = 1500;
        $itemMin = 7000;
        $curShift = $code = mt_rand(0, 63);
        $verifKey = [0, 0];
        $memberI = 0;
        $max = SmitrollGen::PERMALINK_ALPHABET_COUNT;
        $permalinkData = [
            'teams' => '',
            'builds' => SmitrollGen::permalinkEncode(($code + 32) % $max, 0, 1)
        ];
        foreach($teams as $team) {
            foreach($team as $name => $member) {
                // Team
                $permalinkData['teams'] .= $name.',';
                $verifSecondKey = ++$memberI % 2 === 1;
                // God
                $cGod = SmitrollGen::permalinkEncode($member['god']['id'] - $godMin, $curShift, 3);
                $curShift = ($curShift + $code) % $max;
                $permalinkData['builds'] .= $cGod;
                $verifKey[0] = ($verifKey[0] + SmitrollGen::permalinkDecode($cGod[1])) % $max;
                if($verifSecondKey)
                    $verifKey[1] = ($verifKey[1] + SmitrollGen::permalinkDecode($cGod[2])) % $max;
                unset($cGod);
                // Build
                $build = $member["build"];
                $buildAll = array_merge($build['items'], $build['actives']);
                unset($build);
                foreach($buildAll as $item) {
                    $cItem = SmitrollGen::permalinkEncode($item['id'] - $itemMin, $curShift, 3);
                    $curShift = ($curShift + $code) % $max;
                    $permalinkData['builds'] .= $cItem;
                    $verifKey[0] = ($verifKey[0] + SmitrollGen::permalinkDecode($cItem[1])) % $max;
                    if($verifSecondKey)
                        $verifKey[1] = ($verifKey[1] + SmitrollGen::permalinkDecode($cItem[2])) % $max;
                }
                unset($cItem);
            }
            $permalinkData['teams'] = substr($permalinkData['teams'], 0, -1).'-';
        }
        $permalinkData['teams'] = substr($permalinkData['teams'], 0, -1);
        $permalinkData['builds'] .= SmitrollGen::permalinkEncode($verifKey[0], $code, 1).SmitrollGen::permalinkEncode($verifKey[1], $code, 1);
        
        // Finalize permalink
        $permalink .= '/teams='.$permalinkData['teams'];
        $permalink .= '/builds-'.($this->_build).'='.$permalinkData['builds'];
        $result["permalink"] = $permalink;  
        unset($code, $permalink, $permalinkData, $godMin, $itemMin, $teams, $team, $member, $item, $cGod, $cItem);
        
		// Creating the view bag
		$viewBag["generatedBuild"] = Array(
			"data" => $result,
			"success" => true
		);
	}
}