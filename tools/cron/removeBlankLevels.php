<?php
error_reporting(0);
include "../../incl/lib/connection.php";
$query = $db->prepare("DELETE FROM users WHERE extID = ''");
$query->execute();
$query = $db->prepare("DELETE FROM songs WHERE download = ''");
$query->execute();
$query = $db->prepare("DELETE FROM levels WHERE objects = '' OR objects = ''");
$query->execute();
echo "Deleted invalid users and songs.<br>";
ob_flush();
flush();
$query = $db->prepare("SELECT accountID, userName, registerDate FROM accounts");
$query->execute();
$result = $query->fetchAll();
echo "Deleting unused accounts<br>";
ob_flush();
flush();
foreach($result as &$account){
	$query = $db->prepare("SELECT count(*) FROM users WHERE extID = :accountID");
	$query->execute([':accountID' => $account["accountID"]]);
	if($query->fetchColumn() == 0){
		$time = time() - 2592000;
		if($account["registerDate"] < $time){
			echo "Deleted " . htmlspecialchars($account["userName"],ENT_QUOTES) . "<br>";
			$query = $db->prepare("DELETE FROM accounts WHERE accountID = :accountID");
			$query->execute([':accountID' => $account["accountID"]]);
			ob_flush();
			flush();
		}
	}
}
echo "<hr>Success probably";
ob_flush();
flush();
?>