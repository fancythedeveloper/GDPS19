<?php
error_reporting(0);
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
if(!empty($_POST["gameVersion"])){
	$gameVersion = $ep->remove($_POST["gameVersion"]);
}else{
	$gameVersion = 1;
}
if(!empty($_POST["coins"])){
	$coins = $ep->remove($_POST["coins"]);
}else{
	$coins = 0;
}
if(!isset($_POST["userName"]) OR !isset($_POST["secret"]) OR !isset($_POST["stars"]) OR !isset($_POST["demons"]) OR !isset($_POST["icon"]) OR !isset($_POST["color1"]) OR !isset($_POST["color2"])){
	exit("-1");
}
$userName = $ep->remove($_POST["userName"]);
$userName = preg_replace("/[^A-Za-z0-9 ]/", '', $userName);
$secret = $ep->remove($_POST["secret"]);
$stars = $ep->remove($_POST["stars"]);
$demons = $ep->remove($_POST["demons"]);
$icon = $ep->remove($_POST["icon"]);
$color1 = $ep->remove($_POST["color1"]);
$color2 = $ep->remove($_POST["color2"]);
if(!empty($_POST["iconType"])){
	$iconType = $ep->remove($_POST["iconType"]);
}else{
	$iconType = 0;
}
if(!empty($_POST["special"])){
	$special = $ep->remove($_POST["special"]);
}else{
	$special = 0;
}
$accountID = "";
if(empty($_POST["udid"]) AND empty($_POST["accountID"])){
	exit("-1");
}
if(!empty($_POST["udid"])){
	$id = $ep->remove($_POST["udid"]);
	if(is_numeric($id)){
		exit("-1");
	}
}
if(!empty($_POST["accountID"]) AND $_POST["accountID"]!="0"){
	$id = $ep->remove($_POST["accountID"]);
}else{
	$register = 0;
}
$userID = $gs->getUserID($id, $userName);
$uploadDate = time();
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$hostname = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$hostname = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$hostname = $_SERVER['REMOTE_ADDR'];
	}
$query = $db->prepare("SELECT stars,coins,demons FROM users WHERE userID=:userID LIMIT 1"); //getting differences
$query->execute([':userID' => $userID]);
$old = $query->fetch();
$starsdiff = $stars - $old["stars"];
$coindiff = $coins - $old["coins"];
$demondiff = $demons - $old["demons"];
$query2 = $db->prepare("INSERT INTO actions (type, value, timestamp, account, value2, value3) 
									 VALUES ('9',:stars,:timestamp,:account,:coinsd, :demon)"); //creating the action
$query = $db->prepare("UPDATE users SET gameVersion=:gameVersion, userName=:userName, coins=:coins,  secret=:secret, stars=:stars, demons=:demons, icon=:icon, color1=:color1, color2=:color2, iconType=:iconType, special=:special, IP=:hostname, lastPlayed=:uploadDate WHERE userID=:userID");
$query->execute([':gameVersion' => $gameVersion, ':userName' => $userName, ':coins' => $coins, ':secret' => $secret, ':stars' => $stars, ':demons' => $demons, ':icon' => $icon, ':color1' => $color1, ':color2' => $color2, ':iconType' => $iconType, ':special' => $special, ':hostname' => $hostname, ':uploadDate' => $uploadDate, ':userID' => $userID]);
$query2->execute([':timestamp' => time(), ':stars' => $starsdiff, ':account' => $userID, ':coinsd' => $coindiff, ':demon' => $demondiff]);
echo $userID;
?>