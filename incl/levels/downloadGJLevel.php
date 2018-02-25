<?php
error_reporting(0);
chdir(dirname(__FILE__));
include "../lib/connection.php";
require "../lib/XORCipher.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
require "../lib/generateHash.php";
$hash = new generateHash();
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
	$ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
	$ip = $_SERVER['REMOTE_ADDR'];
}
if(empty($_POST["levelID"])){
	exit("-1");
}
$levelID = $ep->remove($_POST["levelID"]);
$feaID = 0;
if(!is_numeric($levelID)){
	echo -1;
}else{
	$query=$db->prepare("SELECT * FROM levels WHERE levelID = :levelID");
	$query->execute([':levelID' => $levelID]);
	$lvls = $query->rowCount();
	if($lvls!=0){
		$result = $query->fetch();
		$query6 = $db->prepare("SELECT count(*) FROM actions WHERE type=:type AND value=:itemID AND value2=:ip");
		$query6->execute([':type' => 7, ':itemID' => $levelID, ':ip' => $ip]);
		if($query6->fetchColumn() < 2){
			$query2=$db->prepare("UPDATE levels SET downloads = downloads + 1 WHERE levelID = :levelID");
			$query2->execute([':levelID' => $levelID]);
			$query6 = $db->prepare("INSERT INTO actions (type, value, timestamp, value2) VALUES 
														(:type,:itemID, :time, :ip)");
			$query6->execute([':type' => 7, ':itemID' => $levelID, ':time' => time(), ':ip' => $ip]);
		}
		$uploadDate = date("d-m-Y G-i", $result["uploadDate"]);
		$updateDate = date("d-m-Y G-i", $result["updateDate"]);
		$pass = $result["password"];
		$desc = $result["levelDesc"];
		$query=$db->prepare("SELECT count(*) FROM modips WHERE IP = :ip");
		$query->execute([":ip" => $ip]);
		$ips = $query->fetchColumn();
		if($ips > 0){
			$pass = "1";
		}
		if($gameVersion > 19){
			$xor = new XORCipher();
			$xorPass = base64_encode($xor->cipher($pass,26364));
		}else{
			$xorPass = $pass;
			$desc = $ep->remove(base64_decode($desc));
		}
		if(file_exists("../../data/levels/$levelID")){
			$levelstring = file_get_contents("../../data/levels/$levelID");
		}else{
			$levelstring = $result["levelString"];
		}
		if(substr($levelstring,0,3) == 'kS1'){
			$levelstring = base64_encode(gzcompress($levelstring));
			$levelstring = str_replace("/","_",$levelstring);
			$levelstring = str_replace("+","-",$levelstring);
		}
		$response = "1:".$result["levelID"].":2:".$result["levelName"].":3:".$desc.":4:".$levelstring.":5:".$result["levelVersion"].":6:".$result["userID"].":8:10:9:".$result["starDifficulty"].":10:".$result["downloads"].":11:1:12:".$result["audioTrack"].":13:".$result["gameVersion"].":14:".$result["likes"].":17:".$result["starDemon"].":25:".$result["starAuto"].":18:".$result["starStars"].":19:".$result["starFeatured"].":45:".$result["objects"].":15:".$result["levelLength"].":30:".$result["original"].":31:1:28:".$uploadDate. ":29:".$updateDate. ":35:".$result["songID"].":36:".$result["extraString"].":27:$xorPass";
		echo $response;
	}else{
		echo -1;
	}
}
?>