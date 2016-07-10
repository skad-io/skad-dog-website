<?php

$name = $_GET["name"];

$connection = new MongoClient("mongodb://localhost:27017");
$dbname = $connection->selectDB('skad');
$attempts = $dbname->attempts;
$query = array("key" => "$name");

if ($name !== "") {
	$results = $attempts->find($query)->sort(array('timestamp'=>-1));
	foreach ($results as $result) {
		echo "<div>\n";
		$rhost = $result["rhost"];
		$user = $result["user"];
		echo "XXX ($rhost) tried to logon as [$user] from XXX in XXX #alerted =XXX=";
		echo "</div>\n";
	}
}	

?>
