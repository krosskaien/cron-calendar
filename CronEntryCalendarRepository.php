<?php
require_once __DIR__ . '/vendor/autoload.php';

define('APPLICATION_NAME', 'Google Calendar API PHP Quickstart');
define('CREDENTIALS_PATH', '~/.credentials/calendar-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/calendar-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Calendar::CALENDAR)
));

class CronEntryCalendarRepository
{
	protected $calendarId;
	protected $client;
	protected $service;

	public function __construct($calendarId=''){
		$this->calendarId = $calendarId;
		$this->initClient();
		$this->service = new Google_Service_Calendar($this->getClient());
	}

	public static function make($calendarId=''){
		return new static($calendarId);
	}

	/**
	 * Returns an authorized API client.
	 * @return Google_Client the authorized client object
	 */
	private function initClient() {
	  $this->client = new Google_Client();
	  $this->client->setApplicationName(APPLICATION_NAME);
	  $this->client->setScopes(SCOPES);
	  $this->client->setAuthConfigFile(CLIENT_SECRET_PATH);
	  $this->client->setAccessType('offline');

	  // Load previously authorized credentials from a file.
	  $credentialsPath = $this->expandHomeDirectory(CREDENTIALS_PATH);
	  if (file_exists($credentialsPath)) {
	    $accessToken = file_get_contents($credentialsPath);
	  } else {
	    // Request authorization from the user.
	    $authUrl = $this->client->createAuthUrl();
	    printf("Open the following link in your browser:\n%s\n", $authUrl);
	    print 'Enter verification code: ';
	    $authCode = trim(fgets(STDIN));

	    // Exchange authorization code for an access token.
	    $accessToken = $this->client->authenticate($authCode);

	    // Store the credentials to disk.
	    if(!file_exists(dirname($credentialsPath))) {
	      mkdir(dirname($credentialsPath), 0700, true);
	    }
	    file_put_contents($credentialsPath, $accessToken);
	    printf("Credentials saved to %s\n", $credentialsPath);
	  }
	  $this->client->setAccessToken($accessToken);

	  // Refresh the token if it's expired.
	  if ($this->client->isAccessTokenExpired()) {
	    $this->client->refreshToken($this->client->getRefreshToken());
	    file_put_contents($credentialsPath, $this->client->getAccessToken());
	  }
	  return $this;
	}

	public function getClient(){
		return $this->client;
	}

	/**
	 * Expands the home directory alias '~' to the full path.
	 * @param string $path the path to expand.
	 * @return string the expanded path.
	 */
	private function expandHomeDirectory($path) {
	  $homeDirectory = getenv('HOME');
	  if (empty($homeDirectory)) {
	    $homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
	  }
	  return str_replace('~', realpath($homeDirectory), $path);
	}

	public function findAllEvents(){
		$optParams = array(
		  'maxResults' => 10,
		  'orderBy' => 'startTime',
		  'singleEvents' => TRUE,
		  'timeMin' => date('c'),
		);
		$results = $this->service->events->listEvents($this->calendarId, $optParams);

		if (count($results->getItems()) == 0) {
		  print "No upcoming events found.\n";
		} else {
		  print "Upcoming events:\n";
		  foreach ($results->getItems() as $event) {
		    $start = $event->start->dateTime;
		    if (empty($start)) {
		      $start = $event->start->date;
		    }
		    printf("%s (%s)\n", $event->getSummary(), $start);
		  }
		}
	}

	public function add($entry){
		$event = new Google_Service_Calendar_Event(array(
		'summary' => $entry->getScript(),
		'location' => 'biuro',
		'description' => $entry->getCommand()."\n".$entry->getOutput(),
		'start' => array(
			'dateTime' => $entry->getExpression()->getNextRunDate()->format('Y-m-d\TH:i:s'),
			'timeZone' => 'Europe/Warsaw',
		),
		'end' => array(
			'dateTime' => $entry->getExpression()->getNextRunDate()->add(new DateInterval('PT10M'))->format('Y-m-d\TH:i:s'),
			'timeZone' => 'Europe/Warsaw',
		)
		));
		return $this->service->events->insert($this->calendarId, $event);
	}

}
