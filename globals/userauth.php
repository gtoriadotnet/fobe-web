<?php

/*
	Alphaland 2021
	User class
*/

use Alphaland\Moderation\UserModerationManager;
use Alphaland\Users\Activation;

class user {
	public $id = -1;
	public $name = "";
	public $rank = -1; // -1 = visitor, 0 = member, 1 = mod, 2 = admin, 3 = owner
	public $currency = -1;
	public $sessionCookieID = 0;
	public $logged_in = false;
	public $twoFactorUnlocked = false;
	
	function __construct() {
		if(isset($_COOKIE['token'])) { $this->checkIfTokenValid($_COOKIE['token']); }
	}

	function isOwner() {
		if ($this->rank == 3) {
			return true;
		}
		return false;
	}
	
	function isAdmin() {
		if($this->rank == 2 || $this->rank == 3) {
			return true;
		}
		return false;
	}

	function isStaff() {
		if($this->rank == 1 || $this->rank == 2 || $this->rank == 3) {
			return true;
		}
		return false;
	}
	
	function checkIfTokenValid($token) {
		$check = $GLOBALS['pdo']->prepare("SELECT * FROM sessions WHERE token = :tk AND valid = 1");
		$check->bindParam(":tk", $token, PDO::PARAM_STR);
		$check->execute();
		if($check->rowCount() > 0) {
		    $info = $check->fetch(PDO::FETCH_OBJ);
			$userIP = getIP();
    		//if(($info->whenCreated + (86400 * 30)) > time()) { //Tokens should only last 30 days
				$userInfo = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :id");
				$userInfo->bindParam(":id", $info->uid, PDO::PARAM_INT);
				$userInfo->execute();
				if($userInfo->rowCount() > 0) {
					$userInfo = $userInfo->fetch(PDO::FETCH_OBJ);
							
					//session info
					$this->logged_in = true;
					$this->id = $info->uid;
					$this->sessionCookieID = $info->id;
					$this->twoFactorUnlocked = $info->twoFactorUnlocked;
					// ...
							
					//user info
					$this->name = $userInfo->username;
					$this->rank = $userInfo->rank;
					$this->currency = $userInfo->currency;
					// ..

					//activation stuff
					$activated = Activation::IsUserActivated($this->id);

					//banned
					$banned = UserModerationManager::IsBanned($this->id);

					if (!$banned)
					{
						//update token interval
						$updateLastSeen = $GLOBALS['pdo']->prepare("UPDATE users SET lastseen = UNIX_TIMESTAMP() WHERE id = :id");
						$updateLastSeen->bindParam(":id", $this->id, PDO::PARAM_INT);
						$updateLastSeen->execute();
					}
							
					//update user's ip
					$updateip = $GLOBALS['pdo']->prepare("UPDATE users SET ip = :ip WHERE id = :id");
					$updateip->bindParam(":ip", $userIP, PDO::PARAM_STR);
					$updateip->bindParam(":id", $info->uid, PDO::PARAM_INT);
					$updateip->execute();
							
					if ($activated && !$banned)
					{
						//reward currency daily
						if (($userInfo->dailytime + (86400 * 1)) < time() || $userInfo->dailytime == 0) //its been a day or first time
						{
							$updateDaily = $GLOBALS['pdo']->prepare("UPDATE users SET dailytime = UNIX_TIMESTAMP(), currency = (currency + 20) WHERE id = :id");
							$updateDaily->bindParam(":id", $this->id, PDO::PARAM_INT);
							$updateDaily->execute();
						}
					}
					return true;
				}
    	    //}
		}
		//invalid token, set the token to null
		setcookie("token", null, time(), "/");
		return false;
	}
	function logout() {
		if($this->logged_in) {
			$logout = $GLOBALS['pdo']->prepare("DELETE FROM sessions WHERE id = :id");
			$logout->bindParam(":id", $this->sessionCookieID, PDO::PARAM_INT);
			$logout->execute();
		}
	}

	
}

$user = new user();