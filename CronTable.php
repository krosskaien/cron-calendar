<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CronSchedule.php';

class CronTable
{
	protected $entries = array();

	public function __construct(array $entries=array()){
		if(!empty($entries)) $this->entries = $entries;
	}

	public static function make(array $entries=array()){
		return new static($entries);
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

	public function findEntriesStartedBetween($end, $begin){
		$schedule = new CronSchedule();
		foreach($this->getEntries() as $entry){
			if ($entry->isParsed()) {
				echo $entry->getRaw()."\n";
				echo $entry->getCommand()."\n";
				echo $entry->getOutput()."\n";
				echo $entry->getScript()."\n";
				echo $entry->getLog()."\n";
				
				$interval = $entry->getRunInterval();
				if($interval > 1200){
					$schedule = $entry->getRunDatesUntil($end, $begin);
					if(!empty($schedule)) $entries[] = $entry;
				}
			}
		}
		return $entries;
	}
}
