<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/CronEntry.php';
require_once __DIR__ . '/CronEntryCalendarRepository.php';
require_once __DIR__ . '/CronLoader.php';
date_default_timezone_set('Europe/Warsaw');

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

// $repo = CronEntryCalendarRepository::make('primary');
$cron = CronLoader::make();
$cron->loadFromFile('cron.dump');

foreach($cron->getEntries() as $entry){
	if ($entry->isParsed()) {
		echo $entry->getRaw()."\n";
		echo $entry->getCommand()."\n";
		echo $entry->getOutput()."\n";
		echo $entry->getScript()."\n";
		echo $entry->getLog()."\n";

		
		$interval = $entry->getRunInterval();
		if($interval > 1200){			
			$matches = $entry->getExpression()->getRunDatesUntil('now');
			print_r($matches);
			// $repo->add($entry);
		}
		echo "\n\n";
	}
}