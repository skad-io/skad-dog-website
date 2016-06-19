<?php

// get the HTTP method, path and body of the request
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
  case 'GET':
        echo "Hello GET World";
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

        $connection = new MongoClient("mongodb://localhost:27017");
        $dbname = $connection->selectDB('skad');
        $attempts = $dbname->attempts;
        $attempts->insert($input);
        break;
    case 'DELETE':
        break;
}

?>

