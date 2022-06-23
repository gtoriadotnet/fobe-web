<?php

/*
	Finobe 2021
	User session class
*/

namespace Finobe\Users
{

    use Finobe\Common\HashingUtiltity;
    use Finobe\Moderation\UserModerationManager;
    use Finobe\Users\Activation;
    use Finobe\Web\WebContextManager;
    use PDO;

    class Session 
    {
        public $id = -1;
        public $name = "";
        public $rank = -1; // -1 = visitor, 0 = member, 1 = mod, 2 = admin, 3 = owner
        public $currency = -1;
        public $sessionCookieID = 0;
        public $logged_in = false;
        public $twoFactorUnlocked = false;

        private const SecondsInDays = 86400;
        
        function __construct() {
            if(isset($_COOKIE['token'])) { 
                $this->ValidateSession($_COOKIE['token']); 
            }
        }

        public function GenerateSessionToken(int $len)
        {
            $hash = "";
            do {
                $hash = HashingUtiltity::GenerateByteHash($len);
                $tokencheck = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM sessions WHERE token = :t");
                $tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
                $tokencheck->execute();
            } while ($tokencheck->fetchColumn() != 0);
            return $hash;
        }

        public function IsOwner() {
            if ($this->rank == 3) {
                return true;
            }
            return false;
        }
        
        public function IsAdmin() {
            if($this->rank == 2 || $this->rank == 3) {
                return true;
            }
            return false;
        }

        public function IsStaff() {
            if($this->rank == 1 || $this->rank == 2 || $this->rank == 3) {
                return true;
            }
            return false;
        }

        public function CreateSession(int $userid) 
        {
            $token = $this->GenerateSessionToken(128); //generate the auth token
            $ip = WebContextManager::GetCurrentIPAddress();
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
               
            $session = $GLOBALS['pdo']->prepare("INSERT INTO sessions(token, uid, ip, whenCreated, user_agent) VALUES(:t,:u,:i,UNIX_TIMESTAMP(),:ua)");
            $session->bindParam(":t", $token, PDO::PARAM_STR);
            $session->bindParam(":u", $userid, PDO::PARAM_INT);
            $session->bindParam(":i", $ip, PDO::PARAM_STR);
            $session->bindParam(":ua", $user_agent, PDO::PARAM_STR);
            if($session->execute()) {
                setcookie("token", $token, time() + (86400 * 30), "/", ".idk16.xyz"); //30 day expiration on token for (hopefully) all finobe paths 
                $this->ValidateSession($token);
                return true;
            } else {
                return false;
            }
        }

        public function UpdateLastSeen()
        {
            if (!UserModerationManager::IsBanned($this->id)) {
                $updateLastSeen = $GLOBALS['pdo']->prepare("UPDATE users SET lastseen = UNIX_TIMESTAMP() WHERE id = :id");
                $updateLastSeen->bindParam(":id", $this->id, PDO::PARAM_INT);
                $updateLastSeen->execute();
                return true;
            }
            return false;
        }

        public function UpdateDailyTime(int $dailyTime)
        {
            if (Activation::IsUserActivated($this->id) && !UserModerationManager::IsBanned($this->id)) {
                if (($dailyTime + Session::SecondsInDays) < time() || $dailyTime == 0) {
                    // it has been a day or this is their first collection.
                    $query = $GLOBALS['pdo']->prepare("UPDATE `users` SET `dailytime` = UNIX_TIMESTAMP(), `currency` = (`currency` + 20) WHERE `id` = :id");
                    $query->bindParam(":id", $this->id, PDO::PARAM_INT);
                    $query->execute();
                }
            }
        }

        public function UpdateIpAddress()
        {
            $ip = WebContextManager::GetCurrentIPAddress();
            $updateip = $GLOBALS['pdo']->prepare("UPDATE users SET ip = :ip WHERE id = :id");
            $updateip->bindParam(":ip", $ip, PDO::PARAM_STR);
            $updateip->bindParam(":id", $this->id, PDO::PARAM_INT);
            $updateip->execute();
        }

        public function ValidateSession(string $token)
        {
            $session = $GLOBALS['pdo']->prepare("SELECT * FROM sessions WHERE token = :tk AND valid = 1");
            $session->bindParam(":tk", $token, PDO::PARAM_STR);
            $session->execute();
            if($session->rowCount() > 0) 
            {
                $session = $session->fetch(PDO::FETCH_OBJ);
                $userinfo = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :id");
                $userinfo->bindParam(":id", $session->uid, PDO::PARAM_INT);
                $userinfo->execute();

                if ($userinfo->rowCount() > 0) 
                {
                    $userinfo = $userinfo->fetch(PDO::FETCH_OBJ);
                    
                    //session dependent info
                    $this->logged_in = true;
                    $this->sessionCookieID = $session->id;
                    $this->twoFactorUnlocked = $session->twoFactorUnlocked;

                    //user dependent info
                    $this->id = $userinfo->id;
                    $this->name = $userinfo->username;
                    $this->rank = $userinfo->rank;
                    $this->currency = $userinfo->currency;
                    $this->UpdateLastSeen();
                    $this->UpdateIpAddress();          
                    $this->UpdateDailyTime($userinfo->dailytime);

                    return true;
                }
            }
            //No valid session
            setcookie("token", null, time(), "/", ".idk16.xyz"); //delete (all token?) cookies
            return false;
        }

        public function LogoutAllSessions(int $userid) 
        {
            $sessions = $GLOBALS['pdo']->prepare("DELETE FROM sessions WHERE uid = :uid");
            $sessions->bindParam(":uid", $userid, PDO::PARAM_INT);
            $sessions->execute();
            if ($sessions->rowCount() > 0) {
                return true;
            }
            return false;
        }

        public function Logout() 
        {
            if($this->logged_in) {
                $logout = $GLOBALS['pdo']->prepare("DELETE FROM sessions WHERE id = :id");
                $logout->bindParam(":id", $this->sessionCookieID, PDO::PARAM_INT);
                $logout->execute();
            }
        }
    }	
}