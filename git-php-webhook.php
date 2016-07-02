<?php

//$debugfile = '/tmp/git-webhook.debug.txt';
if ($debugfile) file_put_contents($debugfile, ">>>>>\n", FILE_APPEND | LOCK_EX);

// Strip out the current git branch
$gitbranch = shell_exec("/usr/bin/git branch");
if ($debugfile) file_put_contents($debugfile, "gitbranches:\n$gitbranch\n", FILE_APPEND | LOCK_EX);
$index = strpos($gitbranch, "*");
$gitbranch = substr($gitbranch, $index + 2);
$index = strpos($gitbranch, "\n");
$gitbranch = substr($gitbranch, 0, $index - strlen($gitbranch));
if ($debugfile) file_put_contents($debugfile, "gitbranch = [$gitbranch]\n", FILE_APPEND | LOCK_EX);

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

$data = file_get_contents('php://input');
$jsondata = json_decode($data, true); 

if ($debugfile) file_put_contents($debugfile, "jsondata['ref'] = ".$jsondata['ref']."\n", FILE_APPEND | LOCK_EX);
$remotegitbranch = substr($jsondata['ref'], 11);
if ($debugfile) file_put_contents($debugfile, "remotegitbranch = [$remotegitbranch]\n", FILE_APPEND | LOCK_EX);

if ($gitbranch === $remotegitbranch) {

	if ($debugfile) file_put_contents($debugfile, "We have a match\n", FILE_APPEND | LOCK_EX);
	$output = shell_exec("cd /home/pi/SKAD && /usr/bin/sudo /usr/bin/git pull");
	if ($debugfile) file_put_contents($debugfile, "output = [$output]\n", FILE_APPEND | LOCK_EX);
}

//if ($debugfile) file_put_contents($debugfile, "data = ".$data."\n", FILE_APPEND | LOCK_EX);
//if ($debugfile) file_put_contents($debugfile, "jsondata['ref'] = ".$jsondata['ref']."\n", FILE_APPEND | LOCK_EX);

}

if ($debugfile) file_put_contents($debugfile, "<<<<<\n", FILE_APPEND | LOCK_EX);

?>
