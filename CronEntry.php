<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CronExpressionExtended.php';

class CronEntry
{
	protected $raw = '';
	protected $expression = '';
	protected $command = '';
	protected $output = '';
	protected $parsed = false;
	protected $schedule = array();
	protected $errors = array();

	public function __construct($str=''){
		$this->raw = $this->clean($str);
		$this->parse();
	}

	private function parse(){
		// przyadłoby się coś lepszego
		if($this->isEmpty() || $this->isComment() || $this->isVariable()) return;
		
		if(preg_match("/^([^\s]+ [^\s]+ [^\s]+ [^\s]+ [^\s]+ )(.*)$/", $this->raw, $matches) == 1){			
			if(is_array($matches) && count($matches) == 3){
				try{					
					$this->expression = CronExpressionExtended::factory($matches[1]);
					$this->parsed = true;			
				}
				catch(InvalidArgumentException $e){
					$this->errors[] = $e->getMessage();
				}
				catch(Exception $e){
					$this->errors[] = $e->getMessage();
				}
				$this->parseDetails($matches[2]);
			}
		}
	}

	private function parseDetails($str=''){
		if(preg_match("/^([^>]*) ([1-2]?>.*)$/", $str, $matches) == 1){
			if(is_array($matches) && count($matches) == 3){
				$this->command = $matches[1];
				$this->output = $matches[2];
			}
		}
		else{
			$this->command = $str;
		}		
	}

	public function getRaw(){
		return $this->raw;
	}

	public function getExpression(){
		return $this->expression;
	}

	public function getCommand(){
		return $this->command;
	}

	public function getOutput(){
		return $this->output;
	}

	public function getScript(){
		$script = $this->command;
		$parts = explode(' ', $this->command);

		while(count($parts) > 0){
			$part = array_pop($parts);
			if(preg_match("/\.(sh|php)$/", $part)){
				$script = basename($part);
				break;
			}
		}
		return $script;
	}

	public function getLog(){
		$log = $this->output;
		$parts = explode(' ', $this->output);

		while(count($parts) > 0){
			$part = array_pop($parts);
			if(preg_match("/\.log$/", $part)){
				$log = basename($part);
				break;
			}
		}
		return $log;
	}

	private function clean($str=''){
		return trim(mb_ereg_replace("\s+", " ", $str));		
	}

	public function isEmpty(){
		return empty($this->raw);
	}

	public function isComment(){
		return (mb_stripos($this->raw, '#') === 0);
	}

	public function isVariable(){
		return (preg_match("/^[A-Z]+=/", $this->raw) === 1);
	}

	public function isParsed(){
		return $this->parsed;
	}

	/**
	 * Get interval in seconds between next 2 runs
	 * @return integer
	 */
	public function getRunInterval(){
		// przyadłoby się coś lepszego
		if(!$this->isParsed()) return;
		
		$schedule = $this->getExpression()->getMultipleRunDates(2);
		// przyadłoby się coś lepszego
		if(empty($schedule) || !is_array($schedule) || count($schedule) != 2) return;

		return strtotime($schedule[1]->format('Y-m-d H:i:s')) - strtotime($schedule[0]->format('Y-m-d H:i:s'));
	}

	public function getRunDatesUntil($endTime, $currentTime = null, $invert = false, $allowCurrentDate = false){
		
	}
}
