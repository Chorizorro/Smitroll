<?php

class CacheFileHelper {
	
	public $_filename = "";
	
	public function __construct($filename) {
		$this->_filename = $filename;
	}
	
	public function exists() {
		if(!isset($this->_filename) || empty($this->_filename))
			return null;
		return is_file($this->_filename);
	}
	
	public function create() {
		if(!isset($this->_filename) || empty($this->_filename))
			return null;
		if($this->exists())
			return false;
		try {
			// Creating a file (error if the file already exists)
			$file = fopen($this->_filename, "x");
			if(!$file)
				throw new Exception("Couldn't create cache file \"{$this->_filename}\"");
			if(!$this->chmod(0404))
				throw new Exception("Couldn't change chmod on cache file \"{$this->_filename}\"");
			$state = true;
		}
		catch(Exception $e) {
			error_log($e->getFile()." (".$e->getLine().") ".$e->getMessage());
			$state = false;
		}
		ob_start();
		return $state;
		if(isset($file) && $file)
			fclose($file);
		ob_end_clean();
	}
	
	public function read() {
		if(!isset($this->_filename) || empty($this->_filename))
			return null;
		try {
			// Opening the cache file
			$file = fopen($this->_filename, "r"); // Read-only
			if(!$file)
				throw new Exception("Couldn't retrieve cache file \"{$this->_filename}\"");
			// Read the file's content (using lock to avoid reading while the server is writing in the file)
			if(!flock($file, LOCK_SH))
				throw new Exception("Couldn't create a SHARE lock on the cache file \"{$this->_filename}\"");
			$content = file_get_contents($this->_filename);
			flock($file, LOCK_UN);
		}
		catch(Exception $e) {
			$content = null;
			error_log($e->getFile()." (".$e->getLine().") ".$e->getMessage());
		}
		ob_start();
		return $content;
		if(isset($file) && $file)
			fclose($file);
		ob_end_clean();
	}
	
	public function write($data) {
		if(!isset($this->_filename) || empty($this->_filename))
			return null;
		try {
			// Opening the cache file
			if(!is_writable($this->_filename))
			{
				if(!file_exists($this->_filename)) {
					if(!($tmpFile = fopen($this->_filename, "w")))
						throw new Exception("Couldn't create cache file \"{$this->_filename}\"");
					fclose($tmpFile);
				}	
				if(!$this->chmod(0604))
					throw new Exception("Couldn't change chmod on cache file \"{$this->_filename}\"");
			}
			$file = fopen($this->_filename, "c"); // Write file
			if(!flock($file, LOCK_EX))
				throw new Exception("Couldn't create an EXCLUSIVE lock on the cache file \"{$this->_filename}\"");
			if(!ftruncate($file, 0))
				throw new Exception("Couldn't clear cache file \"{$this->_filename}\"");
			if(fwrite($file, $data) === 0)
				throw new Exception("Couldn't write in the cache file \"{$this->_filename}\"");
			flock($file, LOCK_UN);
			$state = true;
		}
		catch(Exception $e) {
			$state = false;
			error_log($e->getFile()." (".$e->getLine().") ".$e->getMessage());
		}
		ob_start();
		return $state;
		$this->chmod(0404);
		if(isset($file) && $file)
			fclose($file);
		ob_end_clean();
	}
	
	public function clear() {
		if(!isset($this->_filename) || empty($this->_filename))
			return null;
		try {
			// Checking file authorizations and setting it to writeable if needed
			if(!is_writable($this->_filename))
				if(!$this->chmod(0604))
					throw new Exception("Couldn't change chmod on cache file \"{$this->_filename}\"");
			// Opening the cache file
			$file = fopen($this->_filename, "c"); // Write/Create if not exists
			if(!flock($file, LOCK_EX))
				throw new Exception("Couldn't create an EXCLUSIVE lock on the cache file \"{$this->_filename}\"");
			if(!ftruncate($file, 0))
				throw new Exception("Couldn't clear cache file \"{$this->_filename}\"");
			flock($file, LOCK_UN);
			$state = true;
		}
		catch(Exception $e) {
			$state = false;
			error_log($e->getFile()." (".$e->getLine().") ".$e->getMessage());
		}
		ob_start();
		return $state;
		$this->chmod(0404);
		if(isset($file) && $file)
			fclose($file);
		ob_end_clean();
	}
	
	public function erase() {
		if(!isset($this->_filename) || empty($this->_filename))
			return null;
		try {
			// Checking is the file exists
			if(!is_file($this->_filename)) 
				return false;
			// Checking file authorizations and setting it to writeable if needed
			if(!is_writeable($this->_filename))
				if(!$this->chmod(0604))
					throw new Exception("Couldn't change chmod on cache file \"{$this->_filename}\"");
			$file = fopen($this->_filename, "c"); // Write/Create if not exists
			if(!flock($file, LOCK_EX))
				throw new Exception("Couldn't create an EXCLUSIVE lock on the cache file \"{$this->_filename}\"");
			// Deleting the file
			unlink($this->_filename);
			flock($file, LOCK_UN);
			$state = true;
		}
		catch(Exception $e) {
			$state = false;
			error_log($e->getFile()." (".$e->getLine().") ".$e->getMessage());
		}
		ob_start();
		return $state;
		if(isset($file) && $file)
			fclose($file);
		ob_end_clean();
	}
	
	private function chmod($mod) {
		if(!isset($this->_filename) || empty($this->_filename))
			return false;
		return chmod($this->_filename, $mod);
	}
	
	private function chown($owner) {
		if(!isset($this->_filename) || empty($this->_filename))
			return false;
		return chown($this->_filename, $owner);
	}
}