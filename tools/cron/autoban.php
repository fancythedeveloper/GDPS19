<hr>
<?php
error_reporting(0);
include "../../incl/lib/connection.php";
echo "Initializing autoban";
ob_flush();
flush();
$query = $db->prepare("SELECT starStars, starDemon FROM levels");
$query->execute();
$levelstuff = $query->fetchAll();
$stars = 0;
$demons = 0;
foreach($levelstuff as $level){
	$stars = $stars + $level["starStars"];
	if($level["starDemon"] != 0){
		$demons++;
	}
}
$query = $db->prepare("SELECT stars FROM mappacks");
$query->execute();
$result = $query->fetchAll();
echo "<h3>Stars based bans</h3>";
ob_flush();
flush();
foreach($result as $pack){
	$stars += $pack["stars"];
}
$quarter = floor($stars / 4);
$stars = $stars + 200 + $quarter;
$query = $db->prepare("SELECT userID, userName FROM users WHERE stars > :stars");
$query->execute([':stars' => $stars]);
$result = $query->fetchAll();
foreach($result as $user){
	$query = $db->prepare("UPDATE users SET isBanned = '1' WHERE userID = :id");
	$query->execute([':id' => $user["userID"]]);
	echo "Banned ".htmlspecialchars($user["userName"],ENT_QUOTES)." - ".$user["userID"]."<br>";
}
echo "<h3>Demons based bans</h3>";
ob_flush();
flush();
$quarter = floor($demons / 16);
$demons = $demons + 3 + $quarter;
$query = $db->prepare("SELECT userID, userName FROM users WHERE demons > :demons");
$query->execute([':demons' => $demons]);
$result = $query->fetchAll();
foreach($result as $user){
	$query = $db->prepare("UPDATE users SET isBanned = '1' WHERE userID = :id");
	$query->execute([':id' => $user["userID"]]);
	echo "Banned ".htmlspecialchars($user["userName"],ENT_QUOTES)." - ".$user["userID"]."<br>";
}
$query = $db->prepare("SELECT IP FROM bannedips");
$query->execute();
$result = $query->fetchAll();
foreach($result as &$ip){
	$query = $db->prepare("UPDATE users SET isBanned = '1' WHERE IP LIKE CONCAT(:ip, '%')");
	$query->execute([':ip' => $ip["IP"]]);
}
echo "<hr>Autoban finished";
ob_flush();
flush();
?>
<hr>