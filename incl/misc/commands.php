<?php
error_reporting(0);
class Commands {
	public function ownCommand($comment, $command, $accountID, $targetExtID){
		require_once "../lib/mainLib.php";
		$gs = new mainLib();
		$commandInComment = strtolower("!".$command);
		$commandInPerms = ucfirst(strtolower($command));
		$commandlength = strlen($commandInComment);
		if(substr($comment,0,$commandlength) == $commandInComment AND (($gs->checkPermission($accountID, "command".$commandInPerms."All") OR ($targetExtID == $accountID AND $gs->checkPermission($accountID, "command".$commandInPerms."Own"))))){
			return true;
		}
		return false;
	}
	public function doCommands($accountID, $comment, $levelID) {
		include dirname(__FILE__)."/../lib/connection.php";
		require_once "../lib/exploitPatch.php";
		require_once "../lib/mainLib.php";
		$ep = new exploitPatch();
		$gs = new mainLib();
		$commentarray = explode(' ', $comment);
		$uploadDate = time();
		$query2 = $db->prepare("SELECT extID FROM levels WHERE levelID = :id");
		$query2->execute([':id' => $levelID]);
		$targetExtID = $query2->fetchColumn();
		if(substr($comment,0,5) == '!rate' AND $gs->checkPermission($accountID, "commandRate")){
			$starStars = $commentarray[2];
			if($starStars == ""){
				$starStars = 0;
			}
			$starFeatured = $commentarray[4];
			$diffArray = $gs->getDiffFromName($commentarray[1]);
			$starDemon = $diffArray[1];
			$starAuto = $diffArray[2];
			$starDifficulty = $diffArray[0];
			$query = $db->prepare("UPDATE levels SET starStars=:starStars, starDifficulty=:starDifficulty, starDemon=:starDemon, starAuto=:starAuto WHERE levelID=:levelID");
			$query->execute([':starStars' => $starStars, ':starDifficulty' => $starDifficulty, ':starDemon' => $starDemon, ':starAuto' => $starAuto, ':levelID' => $levelID]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value2, value3, timestamp, account) VALUES ('1', :value, :value2, :levelID, :timestamp, :id)");
			$query->execute([':value' => $commentarray[1], ':timestamp' => $uploadDate, ':id' => $accountID, ':value2' => $starStars, ':levelID' => $levelID]);
			if($starFeatured != ""){
				$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES ('2', :value, :levelID, :timestamp, :id)");
				$query->execute([':value' => $starFeatured, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);	
				$query = $db->prepare("UPDATE levels SET starFeatured=:starFeatured WHERE levelID=:levelID");
				$query->execute([':starFeatured' => $starFeatured, ':levelID' => $levelID]);
			}
			return true;
		}
		if(substr($comment,0,7) == '!unrate' AND $gs->checkPermission($accountID, "commandFeature")){
			$query = $db->prepare("UPDATE levels SET starStars='0', starDifficulty='0', starDemon='0', starAuto='0', starFeatured='0' WHERE levelID=:levelID");
			$query->execute([':levelID' => $levelID]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES ('2', :value, :levelID, :timestamp, :id)");
			$query->execute([':value' => "1", ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
			return true;
		}
		if(substr($comment,0,8) == '!feature' AND $gs->checkPermission($accountID, "commandFeature")){
			$query = $db->prepare("UPDATE levels SET starFeatured='1' WHERE levelID=:levelID");
			$query->execute([':levelID' => $levelID]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES ('2', :value, :levelID, :timestamp, :id)");
			$query->execute([':value' => "1", ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
			return true;
		}
		if(substr($comment,0,10) == '!unfeature' AND $gs->checkPermission($accountID, "commandFeature")){
			$query = $db->prepare("UPDATE levels SET starFeatured='0' WHERE levelID=:levelID");
			$query->execute([':levelID' => $levelID]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES ('2', :value, :levelID, :timestamp, :id)");
			$query->execute([':value' => "1", ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
			return true;
		}
		if(substr($comment,0,7) == '!delete' AND $gs->checkPermission($accountID, "commandDelete")){
			if(!is_numeric($levelID)){
				return false;
			}
			$query = $db->prepare("DELETE from levels WHERE levelID=:levelID LIMIT 1");
			$query->execute([':levelID' => $levelID]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES ('6', :value, :levelID, :timestamp, :id)");
			$query->execute([':value' => "1", ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
			if(file_exists(dirname(__FILE__)."../../data/levels/$levelID")){
				rename(dirname(__FILE__)."../../data/levels/$levelID",dirname(__FILE__)."../../data/levels/deleted/$levelID");
			}
			return true;
		}
		if(substr($comment,0,7) == '!setacc' AND $gs->checkPermission($accountID, "commandSetacc")){
			$query = $db->prepare("SELECT accountID FROM accounts WHERE userName = :userName OR accountID = :userName LIMIT 1");
			$query->execute([':userName' => $commentarray[1]]);
			if($query->rowCount() == 0){
				return false;
			}
			$targetAcc = $query->fetchColumn();
			$query = $db->prepare("SELECT userID FROM users WHERE extID = :extID LIMIT 1");
			$query->execute([':extID' => $targetAcc]);
			$userID = $query->fetchColumn();
			$query = $db->prepare("UPDATE levels SET extID=:extID, userID=:userID, userName=:userName WHERE levelID=:levelID");
			$query->execute([':extID' => $targetAcc["accountID"], ':userID' => $userID, ':userName' => $commentarray[1], ':levelID' => $levelID]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES ('7', :value, :levelID, :timestamp, :id)");
			$query->execute([':value' => $commentarray[1], ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
			return true;
		}

		
		if($this->ownCommand($comment, "rename", $accountID, $targetExtID)){
			$name = $ep->remove(str_replace("!rename ", "", $comment));
			$query = $db->prepare("UPDATE levels SET levelName=:levelName WHERE levelID=:levelID");
			$query->execute([':levelID' => $levelID, ':levelName' => $name]);
			$query = $db->prepare("INSERT INTO modactions (type, value, timestamp, account, value3) VALUES ('8', :value, :timestamp, :id, :levelID)");
			$query->execute([':value' => $name, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
			return true;
		}
		if($this->ownCommand($comment, "pass", $accountID, $targetExtID)){
			$pass = $ep->remove(str_replace("!pass ", "", $comment));
			if(is_numeric($pass)){
				$pass = sprintf("%06d", $pass);
				if($pass == "000000"){
					$pass = "";
				}
				$pass = "1".$pass;
				$query = $db->prepare("UPDATE levels SET password=:password WHERE levelID=:levelID");
				$query->execute([':levelID' => $levelID, ':password' => $pass]);
				$query = $db->prepare("INSERT INTO modactions (type, value, timestamp, account, value3) VALUES ('9', :value, :timestamp, :id, :levelID)");
				$query->execute([':value' => $pass, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
				return true;
			}
		}
		if($this->ownCommand($comment, "description", $accountID, $targetExtID)){
			$desc = base64_encode($ep->remove(str_replace("!description ", "", $comment)));
			$query = $db->prepare("UPDATE levels SET levelDesc=:desc WHERE levelID=:levelID");
			$query->execute([':levelID' => $levelID, ':desc' => $desc]);
			$query = $db->prepare("INSERT INTO modactions (type, value, timestamp, account, value3) VALUES ('13', :value, :timestamp, :id, :levelID)");
			$query->execute([':value' => $desc, ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
			return true;
		}
		if($this->ownCommand($comment, "sharecp", $accountID, $targetExtID)){
			$query = $db->prepare("SELECT userID FROM users WHERE userName = :userName ORDER BY isRegistered DESC LIMIT 1");
			$query->execute([':userName' => $commentarray[1]]);
			$targetAcc = $query->fetchColumn();
			$query = $db->prepare("INSERT INTO cpshares (levelID, userID) VALUES (:levelID, :userID)");
			$query->execute([':userID' => $targetAcc, ':levelID' => $levelID]);
			$query = $db->prepare("UPDATE levels SET isCPShared='1' WHERE levelID=:levelID");
			$query->execute([':levelID' => $levelID]);
			$query = $db->prepare("INSERT INTO modactions (type, value, value3, timestamp, account) VALUES ('11', :value, :levelID, :timestamp, :id)");
			$query->execute([':value' => $commentarray[1], ':timestamp' => $uploadDate, ':id' => $accountID, ':levelID' => $levelID]);
			return true;
		}
		return false;
	}
}
?>