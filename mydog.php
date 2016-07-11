
Hello

<?php

$name = $_GET["name"];
$key = $_GET["key"];

if ($name !== "" || $key !== "") {
	$connection = new MongoClient("mongodb://localhost:27017");
	$dbname = $connection->selectDB('skad');
	$attempts = $dbname->attempts;
	$dogs = $dbname->dogs;
	$rhosts = $dbname->rhosts;

	if (empty($name)) {
		$names = iterator_to_array($dogs->find(array("key" => "$key")));
		$name = array_values($names)[0]["name"];
	}
	else if (empty($key)) {
		$names = iterator_to_array($dogs->find(array("key" => "$key")));
		$keys = iterator_to_array($dogs->find(array("name" => "$name")));
		$key = array_values($keys)[0]["key"];
	}


	$query = array("key" => "$key");
	$results = $attempts->find($query)->sort(array('timestamp'=>-1));
//	$names = iterator_to_array($dogs->find($query));
//	$name = array_values($names)[0]["name"];
	$apicount = 0;
	$sourcesCache = array();
	foreach ($results as $result) {
		$rhost = $result["rhost"];

		if (array_key_exists($rhost, $sourcesCache)) {
			$source = $sourcesCache[$rhost];
		}
		else
		{
			$rhostDetails = iterator_to_array($rhosts->find(array("rhost" => "$rhost")));

			if (!empty($rhostDetails)) {
//				echo "Have got remote host details from database<br>\n";
//				var_dump($rhostDetails);
//				echo "<br>\n";
				$source = array_values($rhostDetails)[0];
				$sourcesCache[$rhost] = $source;
			}
			else {
//				echo "Calling REST API for remote host details<br>\n";
				$sourceJson = file_get_contents("http://ip-api.com/json/".$rhost);
				$source = json_decode($sourceJson, true);
				$source["rhost"] = $rhost;
				$rhosts->insert($source);
				$sourcesCache[$rhost] = $source;
				$apicount++;
				if (apicount > 100) {
					echo "Have hit 100 API count calls so breaking loop. Try again after a minute<br>\n";
					break;
				}
			}
		}

		echo "<div>\n";
		$timestamp = $result["timestamp"];
		$org = $source["org"];
		$city = $source["city"];
		$country = $source["country"];
		$rhost = $result["rhost"];
		$user = $result["user"];
		echo "$timestamp: $org ($rhost) tried to logon as [$user] from $city in $country #alerted =$name=";
		echo "</div>\n";
	}
}	

?>
