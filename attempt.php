<?php

// Get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$pathInfo = $_SERVER['PATH_INFO'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$jsonInput = file_get_contents('php://input');
$input = json_decode(file_get_contents('php://input'),true);

// NOTE: at the moment this is storing the IP address of kit-encrypt proxy - what can be done about this?
$input['remoteAddr'] = $remoteAddr;
$input['httpXForwardedFor'] = $httpXForwardedFor

$connection = new MongoClient("mongodb://localhost:27017");
$dbname = $connection->selectDB('skad');
$attempts = $dbname->attempts;
$attempts->insert($input);

?>
