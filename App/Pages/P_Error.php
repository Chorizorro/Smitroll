<?php

require_once(__DIR__."/../../Core/Page.php");

class P_Error extends Page {
	
	public $_code;
	public $_message;
	
	function __construct($code) {
		
		parent::__construct("error");
		
		// Constructing page ViewBag
		global $viewBag;
		$viewBag["content"] = "error.php";
		array_push($viewBag["styles"], "error_1_0_0");
		
		// Constructing page data
		if(gettype($code) !== "integer") $code = 500;
		$error = [
			"code" => $code,
			"message" => "OMG!1!!11! Apocalypse on the server! RUN, YOU FOOLS"
		];
		switch($code) {
			case 400:
				$error["message"] = "Dafuq is that shit? It's not something my server understands";
				break;
			case 401:
				$error["message"] = "Get the fuck out of here";
				break;
			case 403:
				$error["message"] = "Get the fuck out of here";
				break;
			case 404:
				$error["message"] = "Nothing here, may I ask you to go fuck yourself?";
				break;
			case 408:
				$error["message"] = "Lagging like shit, and stuff...";
				break;
			case 501:
				$error["message"] = "Wowowowow my server can't do that";
				break;
			case 502:
				$error["message"] = "Some shitty servers are making shit";
				break;
			case 503:
				$error["message"] = "Time to wipe a bit that shit!";
				break;
			default: // Assuming code 500
				$error["code"] = 500;
				break;
		}
		http_response_code($error["code"]);
		$viewBag["title"] = "Smitroll: Error " + $error["code"] + " - " + $error["message"];
		$viewBag["error"] = $error;
	}
}