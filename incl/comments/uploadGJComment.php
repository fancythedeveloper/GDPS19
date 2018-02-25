<?php
error_reporting(0);
chdir(dirname(__FILE__));
ini_set('display_errors', 0); 
include "../lib/connection.php";
require_once "../lib/mainLib.php";
$mainLib = new mainLib();
require_once "../lib/XORCipher.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../misc/commands.php";
$cmds = new Commands();
$userName = $ep->remove($_POST["userName"]);
$comment = $ep->remove($_POST["comment"]);
$gameversion = $_POST["gameVersion"];
$comment = base64_encode($comment);
$levelID = $ep->remove($_POST["levelID"]);
if(!empty($_POST["accountID"]) AND $_POST["accountID"]!="0"){
	$id = $ep->remove($_POST["accountID"]);
	$register = 1;
}else{
	$id = $ep->remove($_POST["udid"]);
	$register = 0;
	if(is_numeric($id)){
		exit("-1");
	}
}
$userID = $mainLib->getUserID($id, $userName);
$decodecomment = base64_decode($comment);
if($cmds->doCommands($id, $decodecomment, $levelID)){
	exit("-1");
}
if($id != "" AND $comment != ""){
	$query = $db->prepare("INSERT INTO comments (userName, comment, levelID, userID) VALUES (:userName, :comment, :levelID, :userID)");
	if($register == 1){
		$query->execute([':userName' => $userName, ':comment' => $comment, ':levelID' => $levelID, ':userID' => $userID]);
		echo 1;
	}else{
		$query->execute([':userName' => $userName, ':comment' => $comment, ':levelID' => $levelID, ':userID' => $userID]);
		echo 1;
	}
}else{
	echo -1;
}
?>
