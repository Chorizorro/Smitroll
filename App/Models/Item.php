<?php

require_once(__DIR__."/ItemAttribute.php");

final class Item {
	
	// Unique stats types
	const UNIQUE_BOOTS = 0;
	const UNIQUE_RESOLVE = 1;
	
	// Item types
	const TYPE_ITEM = 0;
	const TYPE_ACTIVE = 1;
	const TYPE_CONSUMABLE = 2;
	
	// Item stats
	const STAT_COOLDOWN_REDUCTION = "Cooldown Reduction";
	
	public $_id = "";
	public $_rootId = "";
	public $_iconId = "";
	public $_name = "";
	public $_type = 0;
	public $_maxTiers = 1;
	public $_picture = "";
	public $_description = "";
	public $_attributes = Array();
	public $_hiddenAttributes = Array();
	public $_additionalAttributes = "";
	public $_prices = Array();
	public $_totalPrice = 0;
	public $_unique = null;
	
	// Constructs Item from a dataset loaded from official SMITE API
	public function __construct($data) {
		
		$this->_id = $data["ItemId"];
		$this->_rootId = $data["RootItemId"];
		$this->_iconId = $data["IconId"];
		$this->_name = $data["DeviceName"];
		$this->_maxTiers = $data["ItemTier"];
		$itemDesc = $data["ItemDescription"];
		$this->_description = $itemDesc["Description"];
		$this->_prices = Array(
			"{$data["ItemTier"]}" => $data["Price"]
		);
		$this->_totalPrice = $data["Price"];
		
		// Retrieving visible attributes
		$this->_additionalAttributes = $itemDesc["SecondaryDescription"];
		$this->_attributes = Array();
		$menuItems = $itemDesc["Menuitems"];
		foreach($menuItems as $item) {
			array_push($this->_attributes, new ItemAttribute($item));
		}
		
		// Computing unique and hidden attributes
		$this->computeAttributes();
		
		// Computing type
		$this->computeType();
	}
	
	// Updates the price range using the given data
	public function updatePrices($data) {
		
		$price = $data["Price"];
		$this->_prices["{$data["ItemTier"]}"] = $price;
		$this->_totalPrice += $price;
		
		return $this->_totalPrice;
	}
	
	public function json() {
		
		$result = Array(
			"id" => $this->_id,
			"rootId" => $this->_rootId,
			"type" => $this->_type,
			"name" => $this->_name,
			"picture" => $this->_picture,
			"description" => $this->_description,
			"prices" => $this->_prices,
			"totalPrice" => $this->_totalPrice
		);
		switch($this->_type) {
			case Item::TYPE_ITEM:
				$result = array_merge($result, Array(
					"attributes" => $this->attributesJson($this->_attributes),
					"hiddenAttributes" => $this->attributesJson($this->_hiddenAttributes),
					"additionalAttributes" => $this->_additionalAttributes,
					"unique" => $this->_unique
				));
				break;
			case Item::TYPE_ACTIVE:
				// Do nothing
				break;
			case Item::TYPE_CONSUMABLE:
				// Do nothing
				break;
			default:
				error_log(__FILE__.' ('.__LINE__.') Unknown item type "'.$this->_type.'"');
				break;
		}
		return $result;
	}
	
	// Compute unique and hidden attributes using the additional attributes
	private function computeAttributes() {
		
		// Already computed
		if($this->_unique !== null)
			return;
		
		$this->_unique = Array();
		$attr = $this->_additionalAttributes;
		// Unique - Boots movement speed
		if(preg_match("/UNIQUE.+\+\d+% (Movement )?Speed.+\(Speed does not stack with other boots\)/i", $attr))
			array_push($this->_unique, Item::UNIQUE_BOOTS);
		// Unique Resolve
		if(preg_match("/UNIQUE PASSIVE - Resolve - \d+% reduction to all crowd control durations/i", $attr))
			array_push($this->_unique, Item::UNIQUE_RESOLVE);
		// Hidden - Cooldown reduction
		$matches = Array();
		if(preg_match("/PASSIVE - Your ability cooldowns are reduced by (\d+)%/i", $attr, $matches) && count($matches) >= 2)
			array_push($this->_hiddenAttributes, new ItemAttribute(Array(
				"Description" => "Cooldown Reduction",
				"Value" => "+{$matches[1]}%"
			)));
	}
	
	// Retrieves item type from its data
	private function computeType() {
		
		// Active and consumable: no attributes at all
		if(count($this->_attributes) === 0) {
			
			// Consumable: maximum tier is 1
			if($this->_maxTiers === 1) {
				$this->_type = Item::TYPE_CONSUMABLE;
				return;
			}
			
			// Active: multiple tiers
			$this->_type = Item::TYPE_ACTIVE;
			return;
		}
		
		// Item: default
		$this->_type = Item::TYPE_ITEM;
	}
	
	private function attributesJson($array) {
		
		if(count($array) === 0)
			return Array();
		
		$result = Array();
		
		foreach($array as $key => $value)
			$result[$key] = $value->json();
		
		return $result;
	}
}