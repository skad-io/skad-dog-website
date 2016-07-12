<html>

<head>
  <title>My Dog</title>
  <style>

  body {
  font-family: 'Lucida Grande', 'Helvetica Neue', Helvetica, Arial, sans-serif;
  padding: 50px;
  font-size: 13px;
  background: white;
}


#span2 {
  direction: ltr;
  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
  font-size: 13px;
  height: auto;
  line-height: 17.875px;
  text-align: left;
  unicode-bidi: embed;
  width: auto;
}

p {
  -webkit-locale: "en";
  color: rgb(41, 47, 51);
  cursor: pointer;
  display: block;
  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
  font-size: 26px;
  font-weight: 300;
  height: 136px;
  letter-spacing: 0.259999990463257px;
  line-height: 32px;
  margin-bottom: 0px;
  margin-left: 0px;
  margin-right: 0px;
  margin-top: 0px;
  text-align: left;
  white-space: pre-wrap;
  width: 505.984375px;
  word-wrap: break-word;
}

#div1 {
  display: inline-block;
  padding: 16px;
  margin: 10px 0;
  max-width: 506px;
  border: #ddd 1px solid;
  border-top-color: #eee;
  border-bottom-color: #bbb;
  background:#e1f5ff;
  border-radius: 5px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.15);
  font: bold 14px/18px Helvetica, Arial, sans-serif; */
  color: #000;
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

        echo "<H1>The attempted attacks on $name, as they happen</H1>\n";
	$query = array("key" => "$key");
	
	if (empty($name)) {
		$results = $attempts->find($query)->sort(array('timestamp'=>-1));
	}
	else {
		$results = $attempts->find($query)->sort(array('timestamp'=>-1))->limit((int)$limit);
	}

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
		$timestamp = date_create_from_format("Ymd-His", $result["timestamp"]);
		$org = $source["org"];
		$city = $source["city"];
		$country = $source["country"];
		$rhost = $result["rhost"];
		$user = $result["user"];

		echo "<div id='div1'>\n";
		echo "<img id='img1' src='skaddog_small.jpg'></img>\n";
		echo "<span id='span1'>&rlm;</span>\n";
		echo "<span id='span2'>$timestamp</span>\n";
		echo "<p>\n";
		echo "$org ($rhost) tried to logon as [$user] from $city in $country #alerted =$name=";
		echo "</p>\n";
		echo "</div>\n";
	}
}	

?>
</html>
