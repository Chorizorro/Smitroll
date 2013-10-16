<?php
abstract class WebservicesHelper {
	
	static function pushError(&$err, $code, $message, $details) {
		$error = Array(
			"code" => $code,
			"message" => $message,
			"details" => $details
		);
		array_push($err, $error);
		return $error;
	}
}