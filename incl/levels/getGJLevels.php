<?php
error_reporting(0);
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/exploitPatch.php";
$ep = new exploitPatch();
require_once "../lib/mainLib.php";
$gs = new mainLib();
$lvlstring = "";
$userstring = "";
$songsstring  = "";
$lvlsmultistring = "";
$orderenabled = true;
$params = array();
if(!empty($_POST["gameVersion"])){
	$gameVersion = $ep->remove($_POST["gameVersion"]);
}else{
	$gameVersion = 0;
}
if(!is_numeric($gameVersion)){
	exit("-1");
}
if(!empty($_POST["type"])){
	$type = $ep->remove($_POST["type"]);
}else{
	$type = 0;
}
$query = "";
if(!empty($_POST["len"])){
	$len = $ep->remove($_POST["len"]);
}else{
	$len = "-";
}
if(!empty($_POST["diff"])){
	$diff = $ep->remove($_POST["diff"]);
}else{
	$diff = "-";
}
if($gameVersion==0){
	$params[] = "gameVersion <= 18";
}else{
	$params[] = " gameVersion <= '$gameVersion'";
}
if(!empty($_POST["featured"]) AND $_POST["featured"]==1){
	$params[] = "starFeatured = 1";
}
if(!empty($_POST["original"]) AND $_POST["original"]==1){
	$params[] = "original = 0";
}
if(!empty($_POST["uncompleted"]) AND $_POST["uncompleted"]==1){
	$completedLevels = $ep->remove($_POST["completedLevels"]);
	$completedLevels = explode("(",$completedLevels)[1];
	$completedLevels = explode(")",$completedLevels)[0];
	$completedLevels = $db->quote($completedLevels);
	$completedLevels = str_replace("'","", $completedLevels);
	$params[] = "NOT levelID IN ($completedLevels)";
}
if(!empty($_POST["song"])){
	if(empty($_POST["customSong"])){
		$song = $ep->remove($_POST["song"]);
		$song = str_replace("'", "", $db->quote($song));
		$song = $song -1;
		$params[] = "audioTrack = '$song' AND songID = 0";
	}else{
		$song = $ep->remove($_POST["song"]);
		$params[] = "songID = '$song'";
	}
}
if(!empty($_POST["twoPlayer"]) AND $_POST["twoPlayer"]==1){
	$params[] = "twoPlayer = 1";
}
if(!empty($_POST["star"])){
	$params[] = "NOT starStars = 0";
}
if(!empty($_POST["noStar"])){
	$params[] = "starStars = 0";
}
$diff = $db->quote($diff);
$diff = str_replace("'","", $diff);
$diff = explode(")",$diff)[0];
switch($diff){
	case -1:
		$params[] = "starDifficulty = '0'";
		break;
	case -3:
		$params[] = "starAuto = '1'";
		break;
	case -2:
		$params[] = "starDemon = 1";
		break;
	case "-";
		break;
	default:
		$diff = str_replace(",", "0,", $diff) . "0";
		$params[] = "starDifficulty IN ($diff) AND starAuto = '0' AND starDemon = '0'";
		break;
}
$len = $db->quote($len);
$len = str_replace("'","", $len);
if($len != "-"){
	$params[] = "levelLength IN ($len)";
}
if(!empty($_POST["str"])){
	$str = $ep->remove($_POST["str"]);
	$str = $db->quote($str);
	$str = str_replace("'","", $str);
}else{
	$str = "";
}
if(isset($_POST["page"]) AND is_numeric($_POST["page"])){
	$page = $ep->remove($_POST["page"]);
}else{
	$page = 0;
}
$lvlpagea = $page*10;
if($type==0){
	$order = "likes";
	if($str!=""){
		if(is_numeric($str)){
			$params = array("levelID = '$str'");
		}else{
			$params[] = "levelName LIKE '%$str%'";
		}
	}
}
if($type==1){
	$order = "downloads";
}
if($type==2){
	$order = "likes";
}
if($type==3){
	$uploadDate = time() - (7 * 24 * 60 * 60);
	$params[] = "uploadDate > $uploadDate ";
	$order = "likes";
}
if($type==5){
	$params[] = "userID = '$str'";
}
if($type==6 OR $type==17){
	$params[] = "starFeatured = 1";
	$order = "rateDate DESC,uploadDate";
}
if($type==7){
	$params[] = "objects > 9999";
}
if($type==10){
	$order = false;
	$params[] = "levelID IN ($str)";
}
if(empty($order)){
	$order = "uploadDate";
}
$querybase = "FROM levels";
if(!empty($params)){
	$querybase .= " WHERE (" . implode(" ) AND ( ", $params) . ")";
}
$query = "(SELECT * $querybase ) ";
if($order){
	$query .= "ORDER BY $order DESC";
}
$query .= " LIMIT 10 OFFSET $lvlpagea";
$countquery = "SELECT count(*) $querybase";
$query = $db->prepare($query);
$query->execute();
$countquery = $db->prepare($countquery);
$countquery->execute();
$totallvlcount = $countquery->fetchColumn();
$result = $query->fetchAll();
$levelcount = $query->rowCount();
foreach($result as &$level1) {
	if($level1["levelID"]!=""){
		$lvlsmultistring .= $level1["levelID"].",";
		$lvlstring .= "1:".$level1["levelID"].":2:".$level1["levelName"].":5:".$level1["levelVersion"].":6:".$level1["userID"].":8:10:9:".$level1["starDifficulty"].":10:".$level1["downloads"].":12:".$level1["audioTrack"].":13:".$level1["gameVersion"].":14:".$level1["likes"].":17:".$level1["starDemon"].":25:".$level1["starAuto"].":18:".$level1["starStars"].":19:".$level1["starFeatured"].":45:".$level1["objects"].":3:".$level1["levelDesc"].":15:".$level1["levelLength"].":30:".$level1["original"].":35:".$level1["songID"]."|";
		if($level1["songID"]!=0){
			$song = $gs->getSongString($level1["songID"]);
			if($song){
				$songsstring .= $gs->getSongString($level1["songID"]) . "~:~";
			}
		}
		$userstring .= $gs->getUserString($level1["userID"])."|";
	}
}
$lvlstring = substr($lvlstring, 0, -1);
$lvlsmultistring = substr($lvlsmultistring, 0, -1);
$userstring = substr($userstring, 0, -1);
$songsstring = substr($songsstring, 0, -3);
echo $lvlstring."#".$userstring;
echo "#".$songsstring;
echo "#".$totallvlcount.":".$lvlpagea.":10";
echo "#";
require "../lib/generateHash.php";
$hash = new generateHash();
echo $hash->genMulti($lvlsmultistring);
?>