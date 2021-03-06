<html>

<head>
  <title>My Dog</title>
  <style>

body {
	padding: 50px;
	background: white;
}

.column {
	width: 500px;	
	margin: 0 auto;
}

.alert {
	height: 150px;
}  	

.icon {
	width: 70px;
	height: 100%;
	float: left;
}

#dog-image {
	width: 50px;
}

.title {
	height: 20px;
	font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
	font-size: 13px;
	color: rgb(136, 153, 166);
}

.message {
	height: 130px;	
	font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
	font-size: 26px;
	font-weight: 300;
	color: rgb(41, 47, 51);
}

.solid-separator {
	background-color: black;
	height: 2px;
}

.clear-separator {
	height: 10px;
}
  	
</style>
  
</head>

<!--<H1>The attempted attacks, as they happen</H1>-->

<?php

$name = $_GET["name"];
$key = $_GET["key"];
$limit = $_GET["limit"];

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

// Title of page:
        echo "<H1>The attempted attacks on $name, as they happen</H1>\n";
        
	$query = array("key" => "$key");
	
	if (empty($name)) {
		$results = $attempts->find($query)->sort(array('timestamp'=>-1));
	}
	else {
		$results = $attempts->find($query)->sort(array('timestamp'=>-1))->limit((int)$limit);
	}

//	echo "<div class='outer-column'>\n";
	echo "<div class='column'>\n";

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

	//	$timestamp = date_format(date_create($result["timestamp"]), 'U = Y-m-d H:i:s');
	//	$timestamp = date_create_from_format("Ymd-His", $result["timestamp"]);
	        $timestamp = DateTime::createFromFormat('Ymd - His', $result["timestamp"])->format('d M Y  h:i:s');
		//$timestamp = $result["timestamp"];
		$org = $source["org"];
		$city = $source["city"];
		$country = $source["country"];
		$rhost = $result["rhost"];
		$user = $result["user"];

/*
		echo "<div id='div1'>\n";
		echo "<img id='img1' src='skaddog_small.jpg'></img>\n";
		echo "<span id='span1'>&rlm;</span>\n";
		echo "<span id='span2'>$timestamp</span>\n";
		echo "<p>\n";
		echo "$org ($rhost) tried to logon as [$user] from $city in $country #alerted =$name=";
		echo "</p>\n";
		echo "</div>\n";
*/
		echo "	<div class='alert'>\n";
		echo "		<div class='icon'><img id='dog-image' src='skaddog_small.jpg'></img></div>\n";
		echo "		<div class='title'>$timestamp</div>\n";
		echo "		<div class='message'>$org ($rhost) tried to logon as [$user] from $city in $country #alerted =$name=</div>\n";
		echo "	</div>\n";
		echo "	<div class='solid-separator'></div>\n";
		echo "	<div class='clear-separator'></div>\n";
	}

	echo "</div>\n";
}	

?>
</html>
