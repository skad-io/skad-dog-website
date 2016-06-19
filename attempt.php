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
        break;
    case 'DELETE':
        break;
}

?>
