<?php

require_once(__DIR__."/../Modules/CacheFileHelper.php");
require_once(__DIR__."/../Modules/SmiteAPIHelper/SmiteAPIHelper.php");
require_once(__DIR__."/../Modules/SmiteAPIHelper/SmiteAPISession.php");
require_once(__DIR__."/../../Apoconfig.php");
require_once(__DIR__."/../../App/Models/God.php");
require_once(__DIR__."/../../App/Models/Item.php");

// Defining cache files path and image files path
define("DIR_CACHE", __DIR__."/../Cache");
define("DIR_CACHE_API", DIR_CACHE."/API");
define("DIR_CACHE_DATA", DIR_CACHE."/Data");
define("CACHE_API_SESSION", DIR_CACHE_API."/session.cache");
define("CACHE_API_GETGODS", DIR_CACHE_API."/getgods.cache");
define("CACHE_API_GETITEMS", DIR_CACHE_API."/getitems.cache");
define("CACHE_DATA_GODS", DIR_CACHE_DATA."/gods.cache");
define("CACHE_DATA_GENERATOR", DIR_CACHE_DATA."/generator.cache");

abstract class CacheManager {
		
	private static $_log = false;
    
    public static function generateCaches($options) {
        
        function logIf($message, $cdt = true) {
            if(!$cdt)
                return;
            echo $message."\n";
        }
        
        /*
         * Options parsing
         */
        
        CacheManager::$_log = $log = isset($options["log"]) && $options["log"];
        if(!isset($options["compute"])) {
            logIf("Nothing to compute, escaping generation");
            return false;
        }
        $compute = $options["compute"];
        $computeGods = isset($compute["gods"]) && $compute["gods"];
        $computeGenerator = isset($compute["generator"]) && $compute["generator"];
        if(!($computeGods || $computeGenerator)) {
            logIf("Nothing to compute, escaping generation");
            return false;
        }
        $force = isset($options["force"]) && $options["force"];
        $getPictures = isset($options["pictures"]) && $options["pictures"];
        
        global $cfg;
        
        /*
         * SMITE API initialization
         */
        
        // Loading credentials
        SmiteAPIHelper::setCredentials($cfg["smiteAPI"]["devId"], $cfg["smiteAPI"]["authKey"]);
        SmiteAPIHelper::$_format = "json";

        // Loading session from cache
        $sSession = new SmiteAPISession();
        if($sSession->loadFromCache(CACHE_API_SESSION) === SmiteAPISession::SESSION_STATE_VALID) {
            logIf("Session loaded from cache", $log);
            SmiteAPIHelper::setSession($sSession);
        }
        
        /*
         * Computing gods
         */
        
		$generatorData = false;
		
        if($computeGods || $computeGenerator) {
            if(($gods = SmiteAPIHelper::getGods())) {
                logIf("Initializing gods list retrieving", $log);
                // Checking the existence of a getgods cache result
                $godsCache = new CacheFileHelper(CACHE_API_GETGODS);

                // TEST PURPOSES
//                $godsCache->clear();
                // END TEST
                
                if(!$godsCache->exists()) {
                    logIf("Cache file for gods didn't exist", $log);
                    if($godsCache->create())
                        logIf("Cache file for gods created", $log);
                    else
                        logIf("Couldn't create a cache file for gods", $log);
                }
                // Checking cached getgods result
                else if ($godsCache->read() === $gods) {
                    if($force)
                        logIf("Retrieved gods list is the same as the one cache, but the generation was forced", $log);
                    else {
                        $skipGodGeneration = true;
                        logIf("retrieved gods list is the same as the one cached, skipping processing", $log);
                    }
                }
                if(!(isset($skipGodGeneration) && $skipGodGeneration === true)) {

                    // Caching the new gods list
                    $godsCache->write($gods);
                    $generatorData = Array(
                        "gods" => Array()
                    );
                    $godsData = Array();

                    try {
                        $godsJson = json_decode($gods, true);
                        $totalGods = 0;
                        foreach($godsJson as $god) {
                            // Retrieving god data
                            $newGod = new God($god);
                            // Get god portrait and abilities icons
                            $godIconName = CacheManager::strImageNameGod($newGod->_name);
							$newGod->_picture = $cfg['path']['rel'].'Styles/Pics/Icons/God_Portraits/'.$godIconName.'.jpg';
							if($getPictures && !CacheManager::getImageFromUri('http://account.hirezstudios.com/smitegame/images/Icons/god_portraits/'.$newGod->_id.'.jpg', __DIR__.'/../../Styles/Pics/Icons/God_Portraits/'.$godIconName.'.jpg'))
                                logIf("Couldn't retrieve god icon for {$newGod->_name}", $log);
                            foreach($newGod->_skills as $key => $skill) {
                                $skillIconName = CacheManager::strImageNameAbility($newGod->_name, $skill->_name);
                                $newGod->_skills[$key]->_picture = $cfg['path']['rel'].'Styles/Pics/Icons/Abilities/'.$skillIconName.".jpg";
                                if($getPictures && !CacheManager::getImageFromUri('http://account.hirezstudios.com/smitegame/images/Icons/Abilities/'.$newGod->_id.'_'.$skill->_id.'.jpg', __DIR__.'/../../Styles/Pics/Icons/Abilities/'.$skillIconName.'.jpg'))
                                    logIf("Couldn't retrieve skill icon for {$newGod->_name}'s {$skill->_name}", $log);
                            }
                            unset($skillIconName, $key, $skill);
                            // Saving god
                            if($computeGods)
                                $godsData[$newGod->_id] = $newGod->json(true);
                            if($computeGenerator)
                                $generatorData["gods"][$newGod->_id] = $newGod->json();
                            ++$totalGods;
                        }
                        logIf("Retrieved $totalGods gods", $log);
                        unset($god, $newGod, $godIconName, $totalGods);
                        // Creating a cache file for the gods
                        if($computeGods) {
                            logIf("Writing cache file for gods", $log);
                            $ch = new CacheFileHelper(CACHE_DATA_GODS);
                            if(!$ch->write(json_encode($godsData)))
                                throw new Exception("Couldn't write gods cache file");
                        }
                    }
                    catch(Exception $e) {
                        logIf("Exception occurred while computing gods: ".$e->getMessage(), $log);
                    }
                    unset($godsJson, $godsData, $ch);
                }
                unset($godsCache);
            }
            else
                logIf("Couldn't load gods list from Smite API", $log);
            unset($gods);
        }
        
        /*
         * Computing items and actives
         */
        
        if($computeGenerator) {
            if(($items = SmiteAPIHelper::getItems())) {
                logIf("Initializing items list retrieving", $log);
                // Checking the existence of a getitems cache result
                $itemsCache = new CacheFileHelper(CACHE_API_GETITEMS);

                // TEST PURPOSES
//            	$itemsCache->clear();
                // END TEST
                if(!$itemsCache->exists()) {
                    logIf("Cache file for items didn't exist", $log);
                    if($itemsCache->create())
                        logIf("Cache file for items created", $log);
                    else
                        logIf("Couldn't create a cache file for items", $log);
                }
                // Checking cached getgods result
                else if ($itemsCache->read() === $items) {
                    if($force) 
                        logIf("Retrieved items list is the same as the one cached, but the generation was forced", $log);
                    else {
                        $skipItemGeneration = true;
                        logIf("Retrieved items list is the same as the one cached, skipping processing", $log);
                    }
                }
                if(!(isset($skipItemGeneration) && $skipItemGeneration === true)) {
                    // Caching the new items list
                    $itemsCache->write($items);
                    // Set the generator data
                    if(!$generatorData)
                        $generatorData = Array(
                            "items" => Array(
                                "item" => Array(),
                                "active" => Array()
                            )
                        );
                    else
                        $generatorData["items"] = Array();

                    try {
                        $itemsJson = json_decode($items, true);
                        $itemsBuffer = Array(); // Set temporary non-tier 3 items in a map with the rootItemId as a key
                        $itemsTree = Array(); // Each entry is a leaf of the tree (the root being the lower tier item), containing all its nodes

                        // Transform items array in a handier format
                        foreach($itemsJson as $item)
                            $itemsBuffer[$item["ItemId"]] = $item;
                        unset($item);
                        // Transform items array into a map of leaves
                        foreach($itemsBuffer as $key => $value) {
                            // Removing child from entries as it's not a leaf
                            $childId = $value["ChildItemId"];
                            if($childId !==	0 && isset($itemsTree[$childId]))
                                unset($itemsTree[$childId]);
                            // Inserting a new entry
                            $newLeaf = Array(
                                "leaf" => $value,
                                "nodes" => Array()
                            );
                            $current = $value;
                            while(($curChildId = $current["ChildItemId"]) !== 0) {
                                if(!isset($itemsBuffer[$curChildId])) // Problem
                                    throw new Exception("Invalid ChildItemId #".$curChildId." found for item #".$childId);
                                array_push($newLeaf["nodes"], $itemsBuffer[$curChildId]);
                                $current = $itemsBuffer[$curChildId];
                            }
                            $itemsTree[$key] = $newLeaf;
                        }
                        unset($itemsBuffer, $key, $value, $childId, $newLeaf, $current, $curChildId);
                        // Create items objects
                        $totalItems = 0;
                        foreach($itemsTree as $tree) {
                            $newItem = new Item($tree["leaf"]);
                            // Ignoring consumables
                            if($newItem->_type === Item::TYPE_CONSUMABLE)
                                continue;
                            $nodes = $tree["nodes"];
                            // Compute item prices using the nodes
                            foreach($nodes as $node) {
                                $newItem->updatePrices($node);
                            }
                            // Get god portrait and abilities icons
                            $itemIconName = CacheManager::strImageNameItem($newItem->_name);
                            $newItem->_picture = $cfg["path"]["rel"].'Styles/Pics/Icons/Items/'.$itemIconName.'.jpg';
                            if($getPictures && !CacheManager::getImageFromUri('http://account.hirezstudios.com/smitegame/images/Icons/items/'.$newItem->_iconId.'.jpg', __DIR__.'/../../Styles/Pics/Icons/Items/'.$itemIconName.'.jpg')) 
                                logIf("Couldn't retrieve item icon for {$newItem->_name}", $log);
                            // Add this item to our dataset
                            $itemType = $newItem->_type === Item::TYPE_ITEM ? "item" : ($newItem->_type === Item::TYPE_ACTIVE ? "active" : null);
                            if($itemType === null) {
                                logIf("Unknown item type \"{$newItem->_type}\" for item #{$newItem->_id}", $log);
                                continue;
                            }
                            $generatorData["items"][$itemType]["{$newItem->_id}"] = $newItem->json();
                            ++$totalItems;
                        }
                        logIf("Retrieved $totalItems items", $log);
                        unset($itemsTree, $tree, $newItem, $itemType, $nodes, $node, $itemIconName);
                        // No need to create a cache, that's all
                    }
                    catch(Exception $e) {
                        logIf("Exception occurred while computing items: ".$e->getMessage(), $log);
                    }
                    unset($itemsJson, $itemsBuffer, $itemsTree);
                }
                unset($itemsCache);
            }
            else
                logIf("Couldn't load items list from Smite API", $log);
            unset($items);

            /*
             * Computing generator cache (gods + items)
             */
            if($computeGenerator && is_array($generatorData)) {
                try {
                    $ch = new CacheFileHelper(CACHE_DATA_GENERATOR);
                    if($ch->exists()) {
                        $cachedGenerator = $ch->read();
                        if($cachedGenerator !== null)
                            $generatorJson = json_decode($cachedGenerator, true);
                        // Load from cache file and set $generatorData[gods]
                        if(!array_key_exists("gods", $generatorData)) {
                            if(!isset($generatorJson))
                                logIf("Couldn't load generator cache to retrieve gods", $log);
                            else
                                $generatorData["gods"] = $generatorJson["gods"];
                        }
                        // Load from cache file and set $generatorData[items]
                        if(!array_key_exists("items", $generatorData)) {
                            if(!isset($generatorJson))
                                logIf("Couldn't load generator cache to retrieve items", $log);
                            else
                                $generatorData["items"] = $generatorJson["items"];
                        }
                    }
                    // Creating a cache file for the generator process
                    logIf("Writing cache file for generator", $log);
                    if(!$ch->write(json_encode($generatorData)))
                        throw new Exception("Couldn't write generator cache file");
                }
                catch(Exception $e) {
                    logIf('Exception occurred while generator data: '.$e->getMessage(), $log);
                }
                unset($ch, $cachedGenerator, $generatorJson);
            }
            unset($generatorData);
        }
        
        /*
         * Session management
         */
        
        if(SmiteAPIHelper::getSession()->saveToCache(CACHE_API_SESSION))
            logIf('Session saved to cache', $log);
        else
            logIf('Couldn\'t save session to cache', $log);
    }
    
	private static function getImageFromUri($source, $dest) {
        
        $state = true;
        $ch = curl_init($source);
        curl_setopt_array($ch, Array(
            CURLOPT_TIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_HTTPHEADER => array('Content-type: '.(SmiteAPIHelper::$_format === SmiteAPIHelper::SMITE_API_FORMAT_JSON ? 'application/json' : 'application/xml'))
        ));
        $image = curl_exec($ch);
        if(($httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE)) >= 400)
            return false;
        try {
            // Creating and opening destination file 
            $checkChmod = !file_exists($dest);
            $file = fopen($dest, "w+"); // Erase and write
			if(!$file)
				throw new Exception("Couldn't create a file at \"$dest\"");
            // Writing image to the file
            if(!flock($file, LOCK_EX))
                throw new Exception("Couldn't create an EXCLUSIVE lock on the cache file \"$dest\"");
            fwrite($file, $image);
            flock($file, LOCK_UN);
            if(isset($file) && is_resource($file))
                fclose($file);
            // Check chmod if needed
            if($checkChmod && !chmod($dest, 0755)) {
                // Do nothing
            }
        }
        catch(Exception $e) {
            $state = false;
			if(CacheManager::$_log)
				echo "Exception occurred while retrieving an image: {$e->getMessage()}";
        }

        // Cleaning and returning result
        curl_close($ch);
        return $state;
    }
    
    private static function strImageNameBase($str) {
        return str_replace('/', '-', str_replace('!', '', $str));
    }
    
    private static function strImageNameGod($god) {
        return str_replace(' ', '_', CacheManager::strImageNameBase($god));
    }
    
    private static function strImageNameAbility($god, $ability) {
        return str_replace(' ', '', CacheManager::strImageNameBase($god)).'_'.str_replace(' ', '', CacheManager::strImageNameBase($ability));
    }
	
	static function strImageNameItem($item) {
		return CacheManager::strImageNameBase($item);
	}
}
