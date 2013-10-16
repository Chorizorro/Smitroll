<?php

abstract class SmitrollGen {
	
    // Build constants
	const BUILD_NB_ITEMS = 6;
	const BUILD_NB_ACTIVES = 2;
	const COOLDOWN_REDUCTION_CAP = 35;
    
    // Encoded permalink char maps
    private static $_PERMALINK_CONV_MAP = [
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',    // 0 to 12
        'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',    // 13 to 25
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',    // 26 to 38
        'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',    // 39 to 51
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '-', '_'          // 52 to 63
    ];
    private static $_PERMALINK_CONV_REVERSE_MAP = [
        'a' => 0, 'b' => 1, 'c' => 2, 'd' => 3, 'e' => 4, 'f' => 5,
        'g' => 6, 'h' => 7, 'i' => 8, 'j' => 9, 'k' => 10, 'l' => 11,
        'm' => 12, 'n' => 13, 'o' => 14, 'p' => 15, 'q' => 16, 'r' => 17,
        's' => 18, 't' => 19, 'u' => 20, 'v' => 21, 'w' => 22, 'x' => 23,
        'y' => 24, 'z' => 25, 'A' => 26, 'B' => 27, 'C' => 28, 'D' => 29,
        'E' => 30, 'F' => 31, 'G' => 32, 'H' => 33, 'I' => 34, 'J' => 35,
        'K' => 36, 'L' => 37, 'M' => 38, 'N' => 39, 'O' => 40, 'P' => 41,
        'Q' => 42, 'R' => 43, 'S' => 44, 'T' => 45, 'U' => 46, 'V' => 47,
        'W' => 48, 'X' => 49, 'Y' => 50, 'Z' => 51, '0' => 52, '1' => 53,
        '2' => 54, '3' => 55, '4' => 56, '5' => 57, '6' => 58, '7' => 59,
        '8' => 60, '9' => 61, '-' => 62, '_' => 63
    ];
    const PERMALINK_ALPHABET_COUNT = 64;
    
    private static $_genData = null;
    
    public static function checkGodIds($godIds, &$invalidIds) {
        
        if($godIds === "all") 
            return true;
        
        // Getting generator data
        $gods = SmitrollGen::getGeneratorData()['gods'];
        
        // Checking gods list
        $godsIds = explode(',', $_GET["gods"]);
        $invalidIds = [];
        foreach($godsIds as $godId) {
            if(isset($gods[$godId]))
                continue;
            $invalidIds[] = $godId;
        }
        unset($godId);
        if(!empty($invalidIds))
            return false;
        
        return true;
    }
    
    public static function getRandomGod($godIds = 'all', $count = 1) {
        
        // Security checks
        if(!(is_array($godIds) || $godIds === 'all'))
            $godIds = 'all';
        if(!(is_numeric($count) && $count >= 1))
            $count = 1;
        
        // Getting generator data
        $gods = SmitrollGen::getGeneratorData()['gods'];
        
        // Pick one or more random gods in the list
        if($godIds === 'all')
            $godIds = array_keys($gods);
        if($count === 1)
            return $gods[$godIds[mt_rand(0, count($godIds) - 1)]];
        $result = [];
        do {
            $result[] = array_splice($gods, mt_rand(0, count($gods) - 1), 1)[0];
        } while (--$count !== 0);
        return $result;
    }
    
    // TODO
//    public static function getRandomSkills($type = 'hard') {
//        return null;
//    }
    
    public static function getRandomBuild($type = 'hard') {
        
        // Security checks
        if($type !== "soft")
            $type = "hard";
        
        // Getting generator data
        $data = SmitrollGen::getGeneratorData()['items'];
        $items = $data['item'];
        $actives = $data['active'];
        unset($data);
        
        // Generation initialization
        $buildList = [
            "items" => [],
            "actives" => [],
            // "stats" => [],
            "cost" => 0,
            "type" => "hard"
        ];
        $watchedStats = [];
        $watchedStats[Item::STAT_COOLDOWN_REDUCTION] = 0;
        $watchedStats["unique"] = [];

        $totalCost = 0;
        // $itemsId = StoreItem::pullIds("item");
        $potentialItems = $items;
        $pickedItems = [];
        $rootItemIds = [];
        // Soft random generation for items
        if($type == "soft") {
            $buildList["type"] = "soft";
            // Picking one boot item
            $boots = [];
            $item = null;
            foreach($potentialItems as $id => $item) {
                if(in_array(Item::UNIQUE_BOOTS, $item["unique"])) {
                    array_push($boots, $item);
                    unset($potentialItems[$id]);
                }
            }
            do {
                $item = $boots[mt_rand(0, count($boots) - 1)];
            }
            while(!SmitrollGen::watchStats($watchedStats, $item));
            array_push($pickedItems, $item);
            $totalCost += $item["totalPrice"];
            unset($item, $id, $boots);
        }
        // Random generation for items
        /*
         * TEST PURPOSES
         * A reduced data set to check watchStats
         */
//			$watchedStats = [];
//			$watchedStats[Item::STAT_COOLDOWN_REDUCTION] = 0;
//			$watchedStats["unique"] = [];
//			$pickedItems = [];
//			$reinforcedGreaves = json_decode('{"id":7612,"type":0,"name":"Reinforced Greaves","picture":"\/Apocalypshit\/2_0_0\/Styles\/Pics\/Icons\/Items\/Reinforced Greaves.jpg","description":"These boots grant the owner crowd control reduction.","prices":{"3":870,"2":625,"1":600},"totalPrice":2095,"attributes":[{"description":"Physical Protection","value":"+25","realValue":25},{"description":"Magical Protection","value":"+25","realValue":25},{"description":"HP5","value":"+15","realValue":15}],"hiddenAttributes":[],"additionalAttributes":"UNIQUE - +18% Speed \/ +26% Speed while out of combat (Speed does not stack with other boots). UNIQUE PASSIVE - Resolve - 30% reduction to all crowd control durations.","unique":[0,1]}', true);
//			SmitrollGen::watchStats($watchedStats, $reinforcedGreaves);
//			array_push($pickedItems, $reinforcedGreaves);
//			$totalCost = $reinforcedGreaves["totalPrice"];
//			$tmpPotentialItems = [];
//			$forcedIds = [
//				7584, // Hide of Leviathan
////				7904, // Jotunn's Wrath
////				7784, // Chronos' Pendant
//				8550, // Hydra's Lament
//				8183, // Fatalis
//				7869, // Warrior Tabi
//				9010, // Ninja tabi
//				7597, // Polynomicon
//				7575, // The Executioner
//				8538 // Mark of the Vanguard
//			];
//			foreach($potentialItems as $pItem) {
//				if(in_array($pItem["id"], $forcedIds))
//					$tmpPotentialItems[] = $pItem;
//			}
//			$potentialItems = $tmpPotentialItems;
        /*
         * END TEST
         */
        $maxItems = count($potentialItems) - 1;
        // Getting random items
        for($i = count($pickedItems) ; $i < SmitrollGen::BUILD_NB_ITEMS ; ++$i) {
            $item = array_splice($potentialItems, mt_rand(0, $maxItems--), 1)[0];
            // Check stats and uniques in soft build
            $rootId = $item["rootId"];
            if(($type == "soft" && !SmitrollGen::watchStats($watchedStats, $item)) || in_array($rootId, $rootItemIds, true)) {
                --$i;
                continue;
            }
            array_push($pickedItems, $item);
            array_push($rootItemIds, $rootId);
            $totalCost += $item["totalPrice"];
        }
        // Retrieving correspondant items
        $buildList["items"] = $pickedItems;
        unset($i, $item, $maxItems, $potentialItems, $pickedItems, $rootItemIds, $rootId);
        // Getting random active items
        $maxActives = count($actives) - 1;
        $pickedActives = [];
        $rootActiveIds = [];
        for($i = 0 ; $i < SmitrollGen::BUILD_NB_ACTIVES ; ++$i) {
            $active = array_splice($actives, mt_rand(0, $maxActives--), 1)[0];
            // Check root id
            $rootId = $active["rootId"];
            if(in_array($rootId, $rootActiveIds, true)) {
                --$i;
                continue;
            }
            array_push($pickedActives, $active);
            array_push($rootActiveIds, $rootId);
            $totalCost += $active["totalPrice"];
        }
        $buildList["actives"] = $pickedActives;
        unset($i, $active, $actives, $maxActives, $pickedActives);
        $buildList["cost"] = $totalCost;
        
        return $buildList;
    }
    
    private static function getGeneratorData($force = false) {
        
        // If possible, we will use previously retrieved data
        $genData = SmitrollGen::$_genData;
        if(!($force || $genData === null))
            return $genData;
        
        // Retrieving generator cached data
        $ch = new CacheFileHelper(__DIR__."/../Cache/Data/generator.cache");
		if(!$ch->exists() || ($genData = $ch->read()) === null) {
			CacheManager::generateCaches([
                "compute" => ["generator" => 1],
                "force" => 1
            ]);
			$genData = $ch->read();
		}
        return SmitrollGen::$_genData = json_decode($genData, true);
    }
    
    private static function watchStats(&$watchedStats, $item) {
        
        $originWatchedStats = $watchedStats;
        $state = true;
        $uniques = $item["unique"];
        $watchedUniques = $watchedStats["unique"];
        // Checking unique stats
        if(!empty($uniques) && !empty($watchedUniques) && count(array_intersect($uniques, $watchedUniques)) !== 0)
            $state = false;
        else {
            // Computting uniques
            $watchedStats["unique"] = array_merge($watchedStats["unique"], $uniques);
            // Checking attributes overcap
            $attributes = array_merge($item["attributes"], $item["hiddenAttributes"]);
            foreach($attributes as $attribute) {
                $value = $attribute["realValue"];
                if($attribute["description"] === Item::STAT_COOLDOWN_REDUCTION) {
                    if($watchedStats[Item::STAT_COOLDOWN_REDUCTION] + $value > SmitrollGen::COOLDOWN_REDUCTION_CAP) {
                        $state = false;
                        break;
                    }
                    // Computing new stats
                    $watchedStats[Item::STAT_COOLDOWN_REDUCTION] += $value;
                }
            }
        }
        // Resetting stats if an error occurred
        if(!$state)
            $watchedStats = $originWatchedStats;
        return $state;
    }
    
    // TODO Set to private whenever possible
    public static function permalinkEncode($number, $shift = 0, $nbCount = 0) {
        
        if($number < 0)
            return;
            
        $result = '';
        $buffer = $number;
        $figureCount = 0;
        do {
            $result = SmitrollGen::$_PERMALINK_CONV_MAP[($buffer + $shift) % SmitrollGen::PERMALINK_ALPHABET_COUNT].$result;
            $buffer = intval(floor($buffer / SmitrollGen::PERMALINK_ALPHABET_COUNT));
            ++$figureCount;
        } while($buffer !== 0);

        if($figureCount > $nbCount)
            return null;

        while($figureCount++ != $nbCount) {
            $result = SmitrollGen::$_PERMALINK_CONV_MAP[$shift].$result;
        }

        return $result;
    }
    
    // TODO Set to private whenever possible
    public static function permalinkDecode($str, $shift = 0) {
        
        if(!preg_match("/^[a-zA-Z0-9_-]+$/", $str))
            return;
            
        $result = 0;
        $max = SmitrollGen::PERMALINK_ALPHABET_COUNT;
        for($i = 0, $iLength = strlen($str); $i < $iLength; $i++) {
            $result = $result * $max + ((SmitrollGen::$_PERMALINK_CONV_REVERSE_MAP[$str[$i]] + $max - $shift) % $max);
        }
        
        return $result;
    }
}

?>
