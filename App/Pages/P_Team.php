<?php

require_once(__DIR__."/../../Core/Page.php");
require_once(__DIR__.'/../Modules/SmitrollGen.php');

class P_Team extends Page {
	
	function __construct() {
		
		parent::__construct("team");
		
		// Constructing page ViewBag
		global $viewBag, $data;
		$viewBag["content"] = "team.php";
		$viewBag["title"] = "Smitroll: Random generator for chaotic SMITE games";
		array_push($viewBag["styles"], "team_2_1_0");
		array_push($viewBag["scripts"], "team_2_1_0");

		$this->loadBuild();
		http_response_code(200); // Shitty fix
	}
        
    private function loadBuild() {
        
        if(!array_key_exists("PATH_INFO", $_SERVER))
            return;
        $fullPath = $_SERVER["PATH_INFO"];
        if(!$fullPath || $fullPath === "/")
            return;

        /*
         * Retrieving parameters
         */

        $params = [];
        if(preg_match_all("/\\/([^=\\/]+)=([^=\\/]+)/i", $fullPath, $params, PREG_SET_ORDER)) {
//            $this->loadBuild_latest ();
			// Well, do nothing
		}
        $buildParams = [];
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
        $cachedItemsActives = $cachedItems["active"];
        unset($gCache, $generatorData, $cachedItems, $ch);

        /*
         * Computing retrieved generation
         */
        
        $loadedBuilds = [
            'build' => [
                'type' => 'soft',
                'data' => []
            ],
            'teams' => [[], []],
            'permalink' => ''
        ];
        $error = false;
        foreach($buildParams as $key => $value) {
            if($error)
                break;
            // Splitting the key and the value
            $keyParams = explode('-', $key);
            $valueParams = explode('-', $value);
            $cKeyParams = count($keyParams);
            $cValueParams = count($valueParams);
            // Teams members
            if($cKeyParams == 1 && $keyParams[0] === "teams") {
                if($cValueParams !== 2) {
                    $error = true;
                    continue; // Bad number of teams
                }
                for($i = 0; $i < 2; ++$i) {
                    $team = $valueParams[$i];
                    if($team === "")
                        continue; // Nobody in this team
                    $members = explode(',', $valueParams[$i]);
                    for($j = 0, $jLength = count($members); $j < $jLength; ++$j) {
                        $member = $members[$j];
                        if($member === "")
                            continue; // Empty member
                        // Adding member into data
                        $loadedBuilds['teams'][$i][] = $member;
                    }
                }
                unset($i, $j, $jLength, $team, $members, $member);
            }
            // Builds
            else if($cKeyParams === 2 && $keyParams[0] === "builds") {
                if($keyParams[1] !== "soft" && $keyParams[1] !== "hard") {
                    $error = true;
                    continue; // Bad randomization type
                }
                $loadedBuilds['build']['type'] = $keyParams[1];
                // Retrieving code
                $source = $value; // Shitty fix
                $max = SmitrollGen::PERMALINK_ALPHABET_COUNT;
                $code = (SmitrollGen::permalinkDecode($source[0]) + $max - 32) % $max;
                // Verifying control keys
                $verifKey = [
                    SmitrollGen::permalinkDecode(substr($source, -2, 1), $code),
                    SmitrollGen::permalinkDecode(substr($source, -1, 1), $code)
                ];
                $body = substr($source, 1, -2);
                $bodyLength = strlen($body);
                if($bodyLength % 3 !== 0) {
                    $error = true;
                    continue; // Bad build body length
                }
                $key1 = 0;
                $key2 = 0;
                for($i = 0; $i < $bodyLength; $i += 3) {
                    $key1 = ($key1 + SmitrollGen::permalinkDecode($body[$i + 1])) % $max;
                    if($i % 54 < 27)
                        $key2 = ($key2 + SmitrollGen::permalinkDecode($body[$i + 2])) % $max;
                }
                if(!($key1 === $verifKey[0] && $key2 === $verifKey[1])) {
                    $error = true;
                    continue; // Incorrect verification key
                }
                unset($key1, $key2, $verifKey);
                // Decrypting data
                $player = null;
                $curShift = $code;
                $godMin = 1500;
                $itemMin = 7000;
                for($i = 0; $i < $bodyLength; $i += 3) {
                    $cluster = substr($body, $i, 3);
                    $id = SmitrollGen::permalinkDecode($cluster, $curShift);
                    $curShift = ($curShift + $code) % $max;
                    $iMod = $i % 27 / 3;
                    // New player + god retrieving
                    if($iMod === 0) {
                        $player = [
                            'god' => [],
                            'build' => [
                                'items' => [],
                                'actives' => [],
                                'totalCost' => 0
                            ]
                        ];
                        $godId = $id + $godMin;
                        if(!isset($cachedGods["{$godId}"])) {
                            $error = true;
                            continue; // Unknown god ID
                        }
                        $player['god'] = $cachedGods["{$godId}"];
                    }
                    // Item
                    else if($iMod < 7) {
                        $itemId = $id + $itemMin;
                        if(!isset($cachedItemsItems["{$itemId}"])) {
                            $error = true;
                            continue; // Unknown item ID
                        }
                        $player['build']['items'][] = $item = $cachedItemsItems["{$itemId}"];
                        $player['build']['totalCost'] += $item['totalPrice'];
                    }
                    // Active
                    else {
                        $itemId = $id + $itemMin;
                        if(!isset($cachedItemsActives["{$itemId}"])) {
                            $error = true;
                            continue; // Unknown active ID
                        }
                        $player['build']['actives'][] = $active = $cachedItemsActives["{$itemId}"];
                        $player['build']['totalCost'] += $active['totalPrice'];
                    }
                    // Saving player
                    if($iMod === 8)
                        $loadedBuilds['build']['data'][] = $player;
                }
            }
        }
        unset($buildParams, $key, $value, $cKeyParams, $cValueParams, $keyParams,
            $valueParams, $cachedGods, $cachedItemsItems, $cachedItemsActives,
            $body, $bodyLength, $cluster, $code, $curShift, $godId, $godMin, $i,
            $iMod, $id, $itemId, $itemMin, $max, $player, $source, $item, $active);
        if(count($loadedBuilds['build']['data']) !== count($loadedBuilds['teams'][0]) + count($loadedBuilds['teams'][1]))
            $error = true;
            
        // Loading build
        if($error)
            return;
        global $data, $cfg;
        $loadedBuilds["permalink"] = "http" . (array_key_exists("HTTPS", $_SERVER) && $_SERVER["HTTPS"] !== "off" ? "s" : "") . "://" . $_SERVER["SERVER_NAME"] .$cfg["path"]["rel"]."team" . $_SERVER["PATH_INFO"];
        $data["loadedBuilds"] = $loadedBuilds;
    }
}