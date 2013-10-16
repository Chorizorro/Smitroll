<?php

require_once(__DIR__."/../../Core/Page.php");
require_once(__DIR__."/../Models/God.php");
require_once(__DIR__."/../Modules/CacheFileHelper.php");

class P_Generator extends Page {
	
	function __construct() {
		
		parent::__construct("generator");
		
		// Constructing page ViewBag
		global $viewBag, $data;
		$viewBag["content"] = "generator.php";
		$viewBag["title"] = "Smitroll: Random generator for chaotic SMITE games";
		array_push($viewBag["styles"], "generator_2_0_3");
		// array_push($viewBag["scripts"], "dataset_1_3_0", "generator_1_3_0");
		array_push($viewBag["scripts"], "generator_2_0_1");
		
		// Retrieved gods list from cache
		$ch = new CacheFileHelper(__DIR__.'/../Cache/Data/gods.cache');
		$godsData = null;
		if(!$ch->exists() || ($godsCache = $ch->read()) === null) {
			CacheManager::generateCaches([
                "compute" => ["gods" => 1],
                "force" => 1
            ]);
			$godsCache = $ch->read();
		}
		else
			$godsData = json_decode($godsCache, true);
		
		// Useless - Sort the god list alphabetically
//		function cmp($a, $b) {
//			return strcmp(strtolower($a["name"]), strtolower($b["name"]));
//		}
//		usort($godsData, "cmp");
		
		$data["godsList"] = $godsData;
		$this->loadBuild();
		http_response_code(200); // Shitty fix
	}
        
    private function loadBuild() {
		
		function computePrices(&$o) {
			$oPrices = $o["prices"];
			$prices = [];
			for($i = 3, $pi = 0, $sum = 0; $i > 0; --$i) {
				if(!isset($oPrices["$i"]))
					continue;
				$price = $oPrices["$i"];
				for($j = 0, $jLength = $pi; $j < $jLength; ++$j)
					if(isset($prices[$j]))
						$prices[$j] += $price;
				$prices[$pi++] = $price;
			}
			$o["prices"] = $prices;
		}
            
        if(!array_key_exists("PATH_INFO", $_SERVER))
            return;
        $fullPath = $_SERVER["PATH_INFO"];
        if(!$fullPath || $fullPath === "/")
            return;

        /*
         * Retrieving parameters
         */

        $params = Array();
        if(preg_match_all("/\\/([^=\\/]+)=([^=\\/]+)/i", $fullPath, $params, PREG_SET_ORDER)) {
//            $this->loadBuild_latest ();
			// Well, do nothing
		}
        $buildParams = Array();
        foreach($params as $p) {
            $buildParams[$p[1]] = $p[2];
        }
		unset($params, $p, $fullPath);

        /* 
         * Loading generator cache data
         */

        $ch = new CacheFileHelper(__DIR__."/../Cache/Data/generator.cache");
        if(!$ch->exists() || ($gCache = $ch->read()) === null) {
			CacheManager::generateCaches([
                "compute" => ["generator" => 1],
                "force" => 1
            ]);
			$gCache = $ch->read();
        }
        $generatorData = json_decode($gCache, true);
        $cachedGods = $generatorData["gods"];
        $cachedItems = $generatorData["items"];
        $cachedItemsItems = $cachedItems["item"];
        $cachedItemsActive = $cachedItems["active"];
        unset($gCache, $generatorData, $cachedItems, $ch);

        /*
         * Computing retrieved generation
         */

        $loadedBuild = Array();
        foreach($buildParams as $key => $value) {
            // Splitting the key and the value
            $keyParams = explode('-', $key);
            $valueParams = explode('-', $value);
            $cKeyParams = count($keyParams);
            $cValueParams = count($valueParams);
            // God
            if($cKeyParams == 1 && $keyParams[0] === "god") {
                if($cValueParams != 1)
                    continue; // Bad parameter count
                $godId = intval($valueParams[0]);
                if(!$godId)
                    continue; // God ID not an integer
                // Loading generated god
                if(!isset($cachedGods["{$godId}"]))
                    continue; // Unknown god ID
                $loadedBuild["god"] = $cachedGods["{$godId}"];
                unset($godId, $cachedGods);
            }
            // Skills list
            else if($cKeyParams == 2 && $keyParams[0] === "skills") {
                if($cValueParams != 1)
                    continue; // Bad parameter count
                $skillOrder = explode(",", $valueParams[0]);
                $cSkillOrder = count($skillOrder);
                // Verifying skill order values
                $shit = false;
                for($i = 0, $iLength = min(Array($cSkillOrder, 20)) ; $i < $iLength ; $i++) {
                    $skill = $skillOrder[$i];
                    if(!in_array($skill, Array("1", "2", "3", "u"))) {
                        $shit = true;
                        break;
                    }
                }
                if($shit)
                    continue; // Invalid value in the skill order
                unset($shit, $i, $iLength, $skill);
                // Loading generated skill order
                if($keyParams[1] !== "soft" && $keyParams[1] !== "hard") 
                    continue; // Bad randomization type
                if($cSkillOrder != 20)
                    continue; // Bad number of skills
                $loadedBuild["skills"] = Array(
                    "type" => $keyParams[1],
                    "order" => $skillOrder
                );
                unset($skillOrder, $cSkillOrder);
            }
            // Build items and actives
            else if($cKeyParams == 2 && $keyParams[0] === "build") {
                if($cValueParams != 2)
                    continue; // Bad parameter count
                if($keyParams[1] !== "soft" && $keyParams[1] !== "hard")
                    continue; // Bad randomization type
                // Loading generated build
                $items = [];
                $itemIds = explode(',', $valueParams[0], 6);
                foreach($itemIds as $itemIdStr) {
                    $itemId = intval($itemIdStr);
                    if(!$itemId)
                        continue; // Item ID not an integer
                    // Loading generated item
                    if(!isset($cachedItemsItems["{$itemId}"]))
                        continue; // Unknown item ID
                    $items[] = $cachedItemsItems["{$itemId}"];
                }
                unset($itemIds, $itemIdStr, $itemId);
                // Actives
                $actives = [];
                $activeIds = explode(',', $valueParams[1], 2);
                foreach($activeIds as $activeIdStr) {
                    $activeId = intval($activeIdStr);
                    if(!$activeId)
                        continue; // Active ID not an integer
                    // Loading generated item
                    if(!isset($cachedItemsActive["{$activeId}"]))
                        continue; // Unknown active ID
                    $actives[] = $cachedItemsActive["{$activeId}"];
                }
                unset($activeIds, $activeIdStr, $activeId);
                // Computing prices and saving build
                $cost = 0;
//                $allItems = array_merge($items, $actives);
                foreach($items as $key => $item) {
					computePrices($items[$key]);
                    $cost += $item["totalPrice"];
				}
                foreach($actives as $key => $active) {
					computePrices($actives[$key]);
                    $cost += $active["totalPrice"];
				}
                $loadedBuild["build"] = Array(
                    "type" => $keyParams[1],
                    "items" => $items,
                    "actives" => $actives,
                    "cost" => $cost
                );
                unset($allItems, $items, $item, $actives, $cost, $cachedItemsActive, $cachedItemsItems);
            }
        }
		unset($buildParams, $key, $value, $cKeyParams, $cValueParams, $keyParams, $valueParams);

        /*
         * Adding retrieved data to the viewbag
         */

        if(empty($loadedBuild))
            return;
        global $data, $cfg;
        $loadedBuild["permalink"] = "http" . (array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] !== "off" ? "s" : "") . "://" . $_SERVER["SERVER_NAME"] .$cfg["path"]["rel"]."generator" . $_SERVER["PATH_INFO"];
        $data["loadedBuild"] = $loadedBuild;
    }
}