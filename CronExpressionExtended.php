<?php
require_once __DIR__ . '/vendor/autoload.php';

class CronExpressionExtended extends \Cron\CronExpression
{
	public function __construct($expression, \Cron\FieldFactory $fieldFactory){
		parent::__construct($expression, $fieldFactory);
	}

	public function getRunDatesUntil($endTime, $currentTime = '', $invert = false, $allowCurrentDate = false)
    {
    	$endTime = new DateTime($endTime);
    	$count = 0;
        $matches = array();
    	if(empty($currentTime)){
    		$tmp = new DateTime('now');
    		
    		$currentTime = new DateTime($tmp->format('Y-m-d 00:00:01'));
    	}

    	do {
    		$count++;
    		$match = $this->getRunDate($currentTime, 0, $invert, $allowCurrentDate);
    		$matches[] = $match;
    		$currentTime = new DateTime($match->format('Y-m-d H:i:s'));
    	}
    	while ($endTime > $currentTime && $count < 3601);
    	return $matches;

    }
}
