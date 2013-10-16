<?php
#!/usr/local/bin/php 
set_time_limit(0);

header("Content-type: text/plain");

require_once(__DIR__."/../../App/Cache/CacheManager.php");

CacheManager::generateCaches(Array(
    "log" => 1,
    "compute" => Array(
		"gods" => 1,
		"generator" => 1
	),
    "pictures" => 1
));

// Finally
set_time_limit(30);