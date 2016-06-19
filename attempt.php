<?php

// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];

$connection = new MongoClient("mongodb://localhost:27017");
$dbname = $connection->selectDB('skad');
$attempts = $dbname->attempts;

switch ($method) {
  case 'GET':
	$results = $attempts->find();
	foreach ($results as $result) {
		foreach ($result as $item) {
			echo "$item,";
		}		
		echo "<BR>\n";
	}	
        break;
  case 'PUT':
        break;
  case 'POST':
	$pathInfo = $_SERVER['PATH_INFO'];
	$remoteAddr = $_SERVER['REMOTE_ADDR'];
	$httpXForwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'];
	$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
	$jsonInput = file_get_contents('php://input');
	$input = json_decode(file_get_contents('php://input'),true);

	$input['remoteAddr'] = $remoteAddr;
	$input['httpXForwardedFor'] = $httpXForwardedFor;

	$attempts->insert($input);

	// Right now I'm just going to tweet everything out but in the future this will be a bit more selective

	$ipaddress = $input['rhost'];

	// The next lines is for testing purposes only
	//if (!filter_var($ipaddress, FILTER_VALIDATE_IP)) {
	//	$ipaddress = "208.80.152.201";
	}//

	if (filter_var($ipaddress, FILTER_VALIDATE_IP)) {

		$sourceJson = file_get_contents("http://ip-api.com/json/".$ipaddress);
		$source = json_decode($sourceJson, true);
		$twitterMessage = $source['org']." ($remoteAddr) tried to logon as [".$input['user']."] from ".$source['city'];

		shell_exec("/home/pi/php_root /usr/local/bin/t update '".$twitterMessage."'");
	}

	//$file = '/tmp/delme.txt';
	//file_put_contents($file, $twitterMessage);

        break;
    case 'DELETE':
        break;
}

?>
