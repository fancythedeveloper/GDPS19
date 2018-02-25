<?php
error_reporting(0);
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
$stars = $ep->remove($_POST["rating"]);
$levelID = $ep->remove($_POST["levelID"]);
$permState = $gs->checkPermission($accountID, "actionRateStars");
if($permState){
	$difficulty = $gs->getDiffFromStars($stars);
	$gs->rateLevel($levelID, 0, $difficulty["diff"], $difficulty["auto"], $difficulty["demon"]);
	echo 1;
}else{
	echo -1;
}