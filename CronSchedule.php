<?php
require_once __DIR__ . '/vendor/autoload.php';

class CronSchedule
{
	protected $events = array();

	public function __construct(array $events=array()){
		if(!empty($events)) $this->events = $events;
	}

	public static function make(array $events=array()){
		return new static($events);
	}

	public function getEvents(){
		return $this->events;
	}

	public function addEvent($str=''){
		$entry = new CronEntry($str);
		if(!$entry->isEmpty()) $this->events[] = $entry;
	}

	public function findEntriesStartedBetween($end, $begin){
		$entries = array();
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
