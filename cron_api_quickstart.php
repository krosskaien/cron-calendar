<?php
require_once __DIR__ . '/vendor/autoload.php';
date_default_timezone_set('Europe/Warsaw');
/*
$expr = Cron\CronExpression::factory('0 22,3 * * *');
$next = $expr->getNextRunDate()->format('Y-m-d H:i:s');
echo $next.PHP_EOL;

$all = $expr->getMultipleRunDates(10);
var_dump($all);
*/

class CronLoader{
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

class CronEntry{
	protected $raw;
	protected $expression;
	protected $command;
	protected $parsed = false;
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
					$this->expression = Cron\CronExpression::factory($matches[1]);
					$this->parsed = true;			
				}
				catch(InvalidArgumentException $e){
					$this->errors[] = $e->getMessage();
				}
				catch(Exception $e){
					$this->errors[] = $e->getMessage();
				}
				$this->command = $matches[2];
			}
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
		return (preg_match("/^[A-Z]+=/", $this->raw) === 1)
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
}

$cron = CronLoader::make();
$cron->loadFromFile('cron.dump');
foreach($cron->getEntries() as $entry){
	if($entry->isParsed()){
		echo $entry->getRaw()."\n";
		echo $entry->getExpression()."\n";
		echo $entry->getCommand()."\n";
		echo $entry->getRunInterval()."\n";
		echo "\n\n";
	}
}
