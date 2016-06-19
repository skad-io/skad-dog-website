<?php

// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];
$pathInfo = $_SERVER['PATH_INFO'];
$request = explode('/', trim($_SERVER['PATH_INFO'],'/'));
$jsonInput = file_get_contents('php://input');
$input = json_decode(file_get_contents('php://input'),true);

$connection = new MongoClient("mongodb://localhost:27017");
$dbname = $connection->selectDB('skad');
$attempts = $dbname->attempts;
$attempts->insert($input);

//TODO Also need to get the ip address of the calling client

?>
