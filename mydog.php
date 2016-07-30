
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

// Set the page to refresh once every 5 minutes.
header("Refresh: 300");
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
$dmzstatus = $dbname->dmzstatus;

if (empty($name)) {
	$names = iterator_to_array($dogs->find(array("key" => "$key")));
	$name = array_values($names)[0]["name"];
}

else if (empty($key)) {
	$names = iterator_to_array($dogs->find(array("key" => "$key")));
	$keys = iterator_to_array($dogs->find(array("name" => "$name")));
	$key = array_values($keys)[0]["key"];
}

// Find the latest heartbeat for this guy.
$dmzresults = $dmzstatus->find(array("key" => "$key"));
$dmztimestamp = 0;
$dmzstatus = "";

foreach ($dmzresults as $dmzresult) {
	$timestamp = DateTime::createFromFormat('Ymd - His', $dmzresult["timestamp"])->format('U');
	$dmz = $dmzresult['DMZ'];
    if ($timestamp > $dmztimestamp){
    	$dmztimestamp = $timestamp;
    	$dmzstatus = $dmz;
    }
}	

$lastmidnight = 1469800000;
$date = date('Y-m-d');
// Actually one minute to midnight
$lastmidnight = strtotime($date) - 60;

?>
<!-- Dog heading bar -->
<section class="page-title page-banner-small bg-secondary">
	<div class="title-icon background-image-holder">
        <img alt="image" class="float-left small" src="img/watch-dog-icon.png">
    </div>
    <div class="container">
        <div class="row">
            <div class="col-sm-12 text-center">
                <h3 class="uppercase mb0">Attacks on <?php echo $name; ?></h3>
            </div>
         </div>
    </div>
</section>

<!-- Map section -->
<section class="map-section z-depth-1 no-pad">
	<div class="container">
        <div class="row  no-pad text-center">

        <div class="col-sm-12">
            <div class="map  no-pad">

            </div>
         </div>
    </div>
    </div>
    </div>
</section>

<!-- The notifications and graphs section -->
<section  class="no-pad mt32">
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
	$places = "[";
	$latestbark = 0;

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
        $text = "ago";
	    
	    // If the bark is pretty fresh, add the 'fresh' class and styling to the bark.
	    if ($ago < 1800){
           $fresh = "fresh";
	    }


	    // Turn into most sensible unit
        $ago = floor($ago / 60);
        $unit = "minute";
        if ($ago == 0){
        	// Special case less than a minute
        	$ago = "Just now";
        	$text = "";
        	$plural = "";
        	$unit = "";

        }
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
        if ($ago > 1 ){
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
         
        $location = "";
		if ($city == "") {
			if ($country == "") {
				$location = "a mysterious location";
			}
			else {
                $location = $country;
		    }
		} else {
			if ($country == "") {
				$location = $city;
			}
			else {
                $location = $city . " in " . $country;
		    }
		}

		echo "<div id='div1' class='feature col-sm-12 bg-secondary bark $fresh'>\n";
		echo "<span id='span2' class='time'>$ago $unit$plural $text</span>\n";
		echo "<p class='mt8'>\n";
		echo "$org ($rhost) tried to logon from $location as [$user]";
		echo "</p>\n";
		echo "</div>\n";

		if ($timestamp_unix > $latestbark){
    	    $latestbark = $timestamp_unix;
        }

		$latitude = $source["lat"];
		$longitude = $source["lon"];
        if ($latitude != "" && $longitude !="") {
			$places = $places . "{ location: { latitude: " . $latitude . ", longitude: " . $longitude . " } },";
        }
	}
	$places = $places . "]";

    // default = alert
    $dogstatus = "alert";
    $dmzdescription = "$name is in the DMZ and ready to bark.";

    // if it says it's active in the DMZ, get its most recent action
	if ($dmzstatus == "Y" ){
        if ($latestbark > $dmztimestamp){
        	$dmztimestamp = $latestbark;
        }
	} 
    // if it doesn't say it's in the DMZ and hasn't barked since then, it's asleep
	else {
	    if ($latestbark < $dmztimestamp) {
            $dogstatus = "asleep";
            $dmzdescription = "$name is not in the DMZ.";
        } else {
        	// This section puts a correct date in for dogs without the DMZ stuff, but who have barked.
        	$dmztimestamp = $latestbark;
        }
	}
	// if it hasn't pinged since before midnight last night, it's probably not plugged in -> away
    if ($dmztimestamp < $lastmidnight){
        $dogstatus = "away";
        $dmzdescription = "We haven't heard from $name in a while.";
    }

    $dmztime = DateTime::createFromFormat('U', $dmztimestamp)->format(' H:i') . ' on ' . DateTime::createFromFormat('U', $dmztimestamp)->format('d M Y');
	
?>
            </div>


<!-- The sidebar -->

        	<div class="col-sm-4">
        		

        		<!-- Limit links and refresh button -->

  
        	<div class="col-sm-12 feature boxed text-center status bg-secondary">
                <h4><?php echo "$name is $dogstatus"; ?></h4>
                <p><?php echo "$dmzdescription"; ?></p>
                <p><?php echo "Last seen at $dmztime"; ?></p>
        	</div>
        	<div class="col-sm-12 feature boxed text-center graph bg-secondary">
                <h4>Number of results</h4>
                <span class="col-sm-12 no-pad"><a class="btn text-center <?php if ($limit == 25){echo "btn-filled";} ?>" href="mydog.php?limit=25&name=<?php echo "$name"; ?>">25</a></span>
                <span class="col-sm-12 no-pad"><a class="btn text-center <?php if ($limit == 50){echo "btn-filled";} ?>" href="mydog.php?limit=50&name=<?php echo "$name"; ?>">50</a></span>
                <span class="col-sm-12 no-pad"><a class="btn text-center <?php if ($limit == 100){echo "btn-filled";} ?>" href="mydog.php?limit=100&name=<?php echo "$name"; ?>">100</a></span>
        	</div>

    
			<!-- The graphs section 

			<div class='feature mt16 col-sm-12 bg-secondary graph'>
			<p>Graph</p>
			</div>

			<div class='feature mt16 col-sm-12 bg-secondary graph'>
			<p>Graph</p>
			</div>

			<div  class='feature mt16 col-sm-12 bg-secondary graph'>
			<p>Graph</p>
			</div>
			-->

            </div>
        </div>
    </div>
    </section>
	<script>
	var places = <?php echo "$places"; ?>;
	</script>
    <?php include 'map.html'; ?>               
    <?php include 'footer.html'; ?> 
    </body>
</html>
