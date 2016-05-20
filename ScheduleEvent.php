<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CronExpressionExtended.php';

class ScheduleEvent
{
	protected $summary;
	protected $location;
	protected $description;
	protected $start;
	protected $end;
	protected $attachments = array();

	public function __construct(array $details = array()){
		if(!empty($details)){
			foreach($details as $k => $v){
				$method = "set".ucfirst($k);
				if(method_exists($this, $method)){
					$this->$method($v);
				}
				elseif(property_exists($this, $k)){
					$this->$k = $v;
				}
			}
		}
	}

	public static function make(array $details = array()){
		return new static($details);
	}

	public function getSummary(){
		return $this->summary;
	}

	public function getLocation(){
		return $this->location;
	}

	public function getDescription(){
		return $this->description;
	}

	public function getStart(){
		return $this->start;
	}

	public function getEnd(){
		return $this->end;
	}

	public function setSummary($str=''){
		$this->summary = trim($str);
	}

	public function setLocation($str=''){
		$this->location = trim($str);
	}

	public function setDescription($str=''){
		$this->description = trim($str);
	}

	public function setStart($v){
		if($v instanceof DateTime){
			$this->start = $v;
		}
		else{
			$this->start = new DateTime($v);
		}
		return $this;
	}

	public function setEnd($v){
		if($v instanceof DateTime){
			$this->end = $v;
		}
		else{
			$this->end = new DateTime($v);
		}
		return $this;
	}

	public function addAttachment($attachment){
		$this->attachments[] = $attachment;
	}
}
