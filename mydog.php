
<?php
// For debug purposes - rm before checkin KS
error_reporting(0);
?>

<?php

// We need at least one ofthese for the page to work.
$name = $_GET["name"];
$key = $_GET["key"];
$limit = $_GET["limit"];

//Let's just get a reasonable number of barks as default
if ($limit == ""){
	$limit = 25;
}

// No dog name or key? Go to the index page where it is requested.
if ($name == "" && $key == ""){
	header('Location: index.php');
}

?>



<!doctype html>
<html lang="en">
<?php include 'head.html'; ?> 
<body class="dashboard">
<?php include 'nav.html'; ?> 

       
<?php

//Need to be sure that the name is capitalised same as in DB
if ($name !== ""){
    $name = ucfirst( strtolower($name) );
}

// Connect to MongoDB and get the basics.
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

?>
<!-- Dog heading bar -->
<section class="page-title page-title-3 bg-secondary">
    <div class="container">
        <div class="row">
            <div class="col-sm-12 text-center">
                <h3 class="uppercase mb0">Attacks on <?php echo $name; ?></h3>
            </div>
         </div>
    </div>
</section>

<!-- Map section -->
<!--section>
    <div class="container">
        <div class="row">
            <div class="col-sm-12 text-center">
                <h2 class="uppercase mb0">D3 Map</h2>
            </div>
         </div>
    </div>
</section-->

<!-- The notifications section -->
<section >
    <div class="container">
        <div class="row">
        	<div class="col-sm-8">
 <?php    
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
	//	$timestamp = date_create_from_format("Ymd-His", $result["timestamp"]);
	    $timestamp = DateTime::createFromFormat('Ymd - His', $result["timestamp"])->format('d M Y  h:i:s');
	    $timestamp_unix = DateTime::createFromFormat('Ymd - His', $result["timestamp"])->format('U');
	    $ago = time() - $timestamp_unix;
	    $plural = "";
        $fresh = "";
	    
	    // If the bark is pretty fresh, add the 'fresh' class and styling to the bark.
	    if ($ago < 1800){
           $fresh = "fresh";
	    }


	    // Turn into most sensible unit
        $ago = floor($ago / 60);
        $unit = "minute";
        if ($ago > 60){
        	 $ago = floor($ago / 60);
             $unit = "hour";
        }
        if ($ago > 24){
        	 $ago = floor($ago / 24);
             $unit = "day";
        }
        if ($ago > 7){
        	 $ago = floor($ago / 7);
             $unit = "week";
        }
        // And add the 's' if it's not just 1
        if ($ago !== 1){
        	$plural = "s";
        }

	    //$timestamp = $result["timestamp"];
		$org = $source["org"];
		$city = $source["city"];
		$country = $source["country"];
		$rhost = $result["rhost"];
		$user = $result["user"];

		if ($org == "") {
			$org = "Unknown attacker";
		}

		echo "<div id='div1' class='feature mt16 col-sm-12 bg-secondary bark $fresh'>\n";
		echo "<span id='span2' class='time'>$ago $unit$plural ago</span>\n";
		echo "<p class='mt8'>\n";
		echo "$org ($rhost) tried to logon to $name from $city in $country as [$user]";
		echo "</p>\n";
		echo "</div>\n";
	}
?>
            </div>


<!-- The graphs section -->

        	<div class="col-sm-4">

<div class='feature mt16 col-sm-12 bg-secondary graph'>
<p>Graph</p>
</div>

<div class='feature mt16 col-sm-12 bg-secondary graph'>
<p>Graph</p>
</div>

<div  class='feature mt16 col-sm-12 bg-secondary graph'>
<p>Graph</p>
</div>

            </div>
        </div>
    </div>
</section>


    <?php include 'footer.html'; ?> 
    </body>
</html>
