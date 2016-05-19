<?php
require_once __DIR__ . '/vendor/autoload.php';

class CronLoader
{
	protected $entries = array();

	public static function make(){
		return new static();
	}

	public function getEntries(){
		return $this->entries;
	}

	public function addEntry($str=''){
		$entry = new CronEntry($str);
		if(!$entry->isEmpty()) $this->entries[] = $entry;
	}

	public function loadFromFile($path=''){
		$handle = fopen($path, "r");
		if ($handle) {
			while (($line = fgets($handle)) !== false) {
				$this->addEntry($line);
			}

			fclose($handle);
		} else {
			// error opening the file.
		}
	}
}
