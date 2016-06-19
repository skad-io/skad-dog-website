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
	$debugfile = '/tmp/skad_dog.debug.txt';
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

	$sourceJson = file_get_contents("http://ip-api.com/json/".$ipaddress);
	$source = json_decode($sourceJson, true);

	if ($source['message'] !== 'invalid query') {
		$twitterMessage = $source['org']." ($remoteAddr) tried to logon as [".$input['user']."] from ".$source['city'];
		
		if ($input['user'] === 'skadtest') {
			file_put_contents($debugfile, $twitterMessage."\n", FILE_APPEND | LOCK_EX);			
		}
		else {
			file_put_contents($debugfile, "About to tweet -> ".$twitterMessage."\n", FILE_APPEND | LOCK_EX);			
			shell_exec("/home/pi/php_root /usr/local/bin/t update '".$twitterMessage."'");
		}
	}
	else {
		file_put_contents($debugfile, "Login made from local network: ".$ipaddress."\n", FILE_APPEND | LOCK_EX);			
	}

        break;
    case 'DELETE':
        break;
}

?>
