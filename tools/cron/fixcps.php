<?php
error_reporting(0);
chdir(dirname(__FILE__));
echo "Please wait...<br>";
ob_flush();
flush();
set_time_limit(0);
$cplog = "";
$people = array();
$nocpppl = "";
include "../../incl/lib/connection.php";
$query = $db->prepare("SELECT userID, userName FROM users");
$query->execute();
$result = $query->fetchAll();
foreach($result as $user){
	$userID = $user["userID"];
	$query2 = $db->prepare("SELECT count(*) FROM levels WHERE userID = :userID AND starStars != 0 AND isCPShared = 0");
	$query2->execute([':userID' => $userID]);
	$creatorpoints = $query2->fetchColumn();
	$cplog .= $user["userName"] . " - " . $creatorpoints . "\r\n";
	$query3 = $db->prepare("SELECT count(*) FROM levels WHERE userID = :userID AND starFeatured != 0 AND isCPShared = 0");
	$query3->execute([':userID' => $userID]);
	$cpgain = $query3->fetchColumn();
	$creatorpoints = $creatorpoints + $cpgain;
	$cplog .= $user["userName"] . " - " . $creatorpoints . "\r\n";
	if($creatorpoints != 0){
		$people[$userID] = $creatorpoints;
	}else{
		$nocpppl .= $userID.",";
	}
}
$query = $db->prepare("SELECT levelID, userID, starStars, starFeatured FROM levels WHERE isCPShared = 1");
$query->execute();
$result = $query->fetchAll();
foreach($result as $level){
	$deservedcp = 0;
	if($level["starStars"] != 0){
		$deservedcp++;
	}
	if($level["starFeatured"] != 0){
		$deservedcp++;
	}
	$query = $db->prepare("SELECT userID FROM cpshares WHERE levelID = :levelID");
	$query->execute([':levelID' => $level["levelID"]]);
	$sharecount = $query->rowCount() + 1;
	$addcp = $deservedcp / $sharecount;
	$shares = $query->fetchAll();
	foreach($shares as &$share){
		$people[$share["userID"]] += $addcp;
	}
	$people[$level["userID"]] += $addcp;
}
$nocpppl = substr($nocpppl, 0, -1);
$query4 = $db->prepare("UPDATE users SET creatorPoints = 0 WHERE userID IN ($nocpppl)");
$query4->execute();
echo "Reset CP of $nocpppl <br>";
foreach($people as $user => $cp){
	echo "$user now has $cp creator points... <br>";
	ob_flush();
	flush();
	$query4 = $db->prepare("UPDATE users SET creatorPoints = :creatorpoints WHERE userID=:userID");
	$query4->execute([':userID' => $user, ':creatorpoints' => $cp]);
}
echo "<hr>done";
?>
