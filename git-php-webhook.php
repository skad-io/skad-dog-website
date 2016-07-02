<?php

//$debugfile = '/tmp/git-webhook.debug.txt';
if ($debugfile) file_put_contents($debugfile, ">>>>>\n", FILE_APPEND | LOCK_EX);

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

file_put_contents($debugfile, "jsondata['ref'] = ".$jsondata['ref']."\n", FILE_APPEND | LOCK_EX);

$data = file_get_contents('php://input');
$jsondata = json_decode($data, true); 
if ($debugfile) file_put_contents($debugfile, "data = ".$data."\n", FILE_APPEND | LOCK_EX);
if ($debugfile) file_put_contents($debugfile, "jsondata['ref'] = ".$jsondata['ref']."\n", FILE_APPEND | LOCK_EX);

//shell_exec("cd /home/pi/SKAD && git pull");

}

if ($debugfile) file_put_contents($debugfile, "<<<<<\n", FILE_APPEND | LOCK_EX);

?>
