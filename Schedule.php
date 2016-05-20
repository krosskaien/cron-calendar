<?php
require_once __DIR__ . '/vendor/autoload.php';

class Schedule implements Countable, IteratorAggregate
{
	protected $events = array();

	public function __construct(array $events=array()){
		if(!empty($events)) $this->events = $events;
	}

	public static function make(array $events=array()){
		return new static($events);
	}

	public function count(){
		return count($this->events);
	}

	public function getIterator(){
		return new ArrayIterator($this->events);
	}

	public function getEvents(){
		return $this->events;
	}

	public function addEvent($event){
		$start = $event->getStart()->format('Y-m-d H:i:s');
		$this->events[$start][] = $event;
	}
}
