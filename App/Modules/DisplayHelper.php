<?php
abstract class DisplayHelper {
    
	static function htmlentities($string, $flags = null, $encoding = 'UTF-8', $double_encode = true) {
        
		if($flags === null)
            $flags = ENT_QUOTES | ENT_HTML5;
        
		return htmlentities($string, $flags, $encoding, $double_encode);
	}
}