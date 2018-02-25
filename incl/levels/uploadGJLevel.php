<?php
error_reporting(0);
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/exploitPatch.php";
require_once "../lib/mainLib.php";
$mainLib = new mainLib();
$ep = new exploitPatch();
$gameVersion = $ep->remove($_POST["gameVersion"]);
$userName = $ep->remove($_POST["userName"]);
$userName = preg_replace("/[^A-Za-z0-9 ]/", '', $userName);
$levelID = $ep->remove($_POST["levelID"]);
$levelName = $ep->remove($_POST["levelName"]);
$levelName = preg_replace("/[^A-Za-z0-9 ]/", '', $levelName);
$levelDesc = $ep->remove($_POST["levelDesc"]);
$levelDesc = base64_encode($levelDesc);
$levelVersion = $ep->remove($_POST["levelVersion"]);
$levelLength = $ep->remove($_POST["levelLength"]);
$audioTrack = $ep->remove($_POST["audioTrack"]);
if(!empty($_POST["auto"])){
	$auto = $ep->remove($_POST["auto"]);
}else{
	$auto = 0;
}
if(isset($_POST["password"])){
	$password = $ep->remove($_POST["password"]);
}else{
	$password = 1;
	if($gameVersion > 17){
		$password = 0;
	}
}
if(!empty($_POST["original"])){
	$original = $ep->remove($_POST["original"]);
}else{
	$original = 0;
}
if(!empty($_POST["twoPlayer"])){
	$twoPlayer = $ep->remove($_POST["twoPlayer"]);
}else{
	$twoPlayer = 0;
}
if(!empty($_POST["songID"])){
	$songID = $ep->remove($_POST["songID"]);
}else{
	$songID = 0;
}
if(!empty($_POST["objects"])){
	$objects = $ep->remove($_POST["objects"]);
}else{
	$objects = 0;
}
if(!empty($_POST["extraString"])){
	$extraString = $ep->remove($_POST["extraString"]);
}else{
	$extraString = "29_29_29_40_29_29_29_29_29_29_29_29_29_29_29_29";
}
$levelString = $ep->remove($_POST["levelString"]);
$secret = $ep->remove($_POST["secret"]);
$accountID = "";
if(!empty($_POST["udid"])){
	$id = $ep->remove($_POST["udid"]);
	if(is_numeric($id)){
		exit("-1");
	}
}
if(!empty($_POST["accountID"]) AND $_POST["accountID"]!="0"){
	$id = $ep->remove($_POST["accountID"]);
}
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
	$hostname = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$hostname = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
	$hostname = $_SERVER['REMOTE_ADDR'];
}
$userID = $mainLib->getUserID($id, $userName);
$uploadDate = time();
$query = $db->prepare("SELECT count(*) FROM levels WHERE uploadDate > :time AND (userID = :userID OR hostname = :ip)");
$query->execute([':time' => $uploadDate - 60, ':userID' => $userID, ':ip' => $hostname]);
if($query->fetchColumn() > 0){
	exit("-1");
}
$query = $db->prepare("INSERT INTO levels (levelName, gameVersion, userName, levelDesc, levelVersion, levelLength, audioTrack, auto, password, original, twoPlayer, songID, objects, extraString, levelString, secret, uploadDate, userID, extID, updateDate, hostname)
VALUES (:levelName, :gameVersion, :userName, :levelDesc, :levelVersion, :levelLength, :audioTrack, :auto, :password, :original, :twoPlayer, :songID, :objects, :extraString, :levelString, :secret, :uploadDate, :userID, :id, :uploadDate, :hostname)");
if($levelString != "" AND $levelName != ""){
	$querye=$db->prepare("SELECT levelID FROM levels WHERE levelName = :levelName AND userID = :userID");
	$querye->execute([':levelName' => $levelName, ':userID' => $userID]);
	$levelID = $querye->fetchColumn();
	$lvls = $querye->rowCount();
	if($lvls==1){
		$query = $db->prepare("UPDATE levels SET levelName=:levelName, gameVersion=:gameVersion, userName=:userName, levelDesc=:levelDesc, levelVersion=:levelVersion, levelLength=:levelLength, audioTrack=:audioTrack, auto=:auto, password=:password, original=:original, twoPlayer=:twoPlayer, songID=:songID, objects=:objects, extraString=:extraString, levelString=:levelString, secret=:secret, updateDate=:uploadDate, hostname=:hostname WHERE levelName=:levelName AND extID=:id");	
		$query->execute([':levelName' => $levelName, ':gameVersion' => $gameVersion, ':userName' => $userName, ':levelDesc' => $levelDesc, ':levelVersion' => $levelVersion, ':levelLength' => $levelLength, ':audioTrack' => $audioTrack, ':auto' => $auto, ':password' => $password, ':original' => $original, ':twoPlayer' => $twoPlayer, ':songID' => $songID, ':objects' => $objects, ':extraString' => $extraString, ':levelString' => "", ':secret' => $secret, ':levelName' => $levelName, ':id' => $id, ':uploadDate' => $uploadDate, ':hostname' => $hostname]);
		file_put_contents("../../data/levels/$levelID",$levelString);
		echo $levelID;
	}else{
		$query->execute([':levelName' => $levelName, ':gameVersion' => $gameVersion, ':userName' => $userName, ':levelDesc' => $levelDesc, ':levelVersion' => $levelVersion, ':levelLength' => $levelLength, ':audioTrack' => $audioTrack, ':auto' => $auto, ':password' => $password, ':original' => $original, ':twoPlayer' => $twoPlayer, ':songID' => $songID, ':objects' => $objects, ':extraString' => $extraString, ':levelString' => "", ':secret' => $secret, ':uploadDate' => $uploadDate, ':userID' => $userID, ':id' => $id, ':hostname' => $hostname]);
		$levelID = $db->lastInsertId();
		file_put_contents("../../data/levels/$levelID",$levelString);
		echo $levelID;
	}
}else{
	echo -1;
}
?>