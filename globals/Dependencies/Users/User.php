<?php

/*
	Alphaland 2021
	User class
*/

namespace Alphaland\Users
{
    use Alphaland\Moderation\UserModerationManager;
    use Alphaland\Users\Activation;
    use Alphaland\Web\WebContextManager;
    use PDO;

    class user 
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

        public function UpdateLastSeen()
        {
            if (!UserModerationManager::IsBanned($this->id)) {
                $updateLastSeen = $GLOBALS['pdo']->prepare("UPDATE users SET lastseen = UNIX_TIMESTAMP() WHERE id = :id");
                $updateLastSeen->bindParam(":id", $this->id, PDO::PARAM_INT);
                $updateLastSeen->execute();
            }
        }

        public function UpdateDailyTime(int $dailyTime)
        {
            if (Activation::IsUserActivated($this->id) && !UserModerationManager::IsBanned($this->id)) {
                if (($dailyTime + User::SecondsInDays) < time() || $dailyTime == 0) {
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
            setcookie("token", null, time(), "/", ".alphaland.cc"); //delete (all token?) cookies
            return false;
        }

        function logout() 
        {
            if($this->logged_in) {
                $logout = $GLOBALS['pdo']->prepare("DELETE FROM sessions WHERE id = :id");
                $logout->bindParam(":id", $this->sessionCookieID, PDO::PARAM_INT);
                $logout->execute();
            }
        }
    }	
}