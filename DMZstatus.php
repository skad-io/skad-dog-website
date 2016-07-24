<?php

$debugfile = '/tmp/skad_dog.debug.txt';

//file_put_contents($debugfile, "############################\n", FILE_APPEND | LOCK_EX);

// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];

$connection = new MongoClient("mongodb://localhost:27017");
$dbname = $connection->selectDB('skad');
$dmzstatus = $dbname->dmzstatus;

switch ($method) {
  case 'GET':
	$results = $dmzstatus->find();
	foreach ($results as $result) {

		// Get the officially stored dog name and not the one that was passed in (incase someone's been tweaking with the dog)
		$dogs = $dbname->dogs;		
		$names = iterator_to_array($dogs->find(array("key" => "$key")));

		$key = $result["key"];		
//		file_put_contents($debugfile, "key: $key\n", FILE_APPEND | LOCK_EX);

		$dogs = $dbname->dogs;		
		$names = iterator_to_array($dogs->find(array("key" => "$key")));
		$name = array_values($names)[0]["name"]; 
		$timestamp = DateTime::createFromFormat('Ymd - His', $result["timestamp"])->format('d M Y  h:i:s');

		echo "$name: DMZ=".$result['DMZ'].", last seen=$timestamp<BR>\n";

		//var_dump($result);
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
	$key = $input['key'];
	
//	file_put_contents($debugfile, "key: $key\n", FILE_APPEND | LOCK_EX);

	$input['remoteAddr'] = $remoteAddr;
	$input['httpXForwardedFor'] = $httpXForwardedFor;

    // Remove the old entry associated with the dog and insert the new entry
    $dmzstatus->remove(array("key" => "$key"));
	$dmzstatus->insert($input);

        break;
    case 'DELETE':
        break;
}

?>
