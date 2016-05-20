<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Schedule.php';
require_once __DIR__ . '/ScheduleEvent.php';

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
		$entry = CronEntry::make($str);
		if(!$entry->isEmpty()) $this->entries[$entry->getHash()] = $entry;
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

	public function findEntriesStartedBetween($end, $begin=''){
		$schedule = Schedule::make();
		foreach($this->getEntries() as $entry){
			if ($entry->isParsed()) {
				
				$interval = $entry->getRunInterval();
				if($interval > 1200){
					$matches = $entry->getRunDatesUntil($end, $begin);					
					if(!empty($matches)){
						while($match = array_pop($matches)){
							$event = ScheduleEvent::make();
							$event->setStart($match);
							$event->setLocation('biuro');
							$event->setSummary($entry->getScript());
							$event->setDescription($entry->getRaw());
							$event->addAttachment($entry);
							$schedule->addEvent($event);
						}
					}
				}
			}
		}
		return $schedule;
	}
}
