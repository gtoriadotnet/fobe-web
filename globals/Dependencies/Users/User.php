<?php

namespace Alphaland\Users {

    use Alphaland\Moderation\UserModerationManager;
    use Alphaland\Web\WebContextManager;
    use PDO;

    class User
    {
        public int $ID = -1;
        public string $Name;
        public UserRank $Rank = UserRank::Visitor;
        public int $Currency = 0;
        public int $SessionCookieID = 0;
        public bool $IsLoggedIn = false;

        function __construct()
        {
            // TODO: Potential shared constant for the cookie's name?
            if (isset($_COOKIE['token']))
                $this->ValidateToken($_COOKIE['token']);
        }

        // RoleSet helpers
        public function IsOwner()
        {
            return $this->Rank === UserRank::Owner;
        }

        public function IsAdministrator()
        {
            return $this->Rank === UserRank::Administrator || $this->Rank === UserRank::Owner;
        }

        public function IsStaff()
        {
            return $this->Rank === UserRank::Administrator || $this->Rank === UserRank::Moderator || $this->Rank === UserRank::Owner;
        }

        public function UpdateLastSeen()
        {
            if (!UserModerationManager::IsBanned($this->ID)) {
                $query = $this->pdo->prepare("UPDATE `users` SET `lastseen` = UNIX_TIMESTAMP() WHERE `id` = :id");
                $query->bindParam(":id", $this->ID, PDO::PARAM_INT);
                $query->execute();
            }
        }

        public function UpdateDailyTime(int $dailyTime)
        {
            if (!UserModerationManager::IsBanned($this->ID)) {
                if (($dailyTime + User::SecondsInDays) < time() || $dailyTime == 0) {
                    // it has been a day or this is their first collection.
                    $query = $this->pdo->prepare("UPDATE `users` SET `dailytime` = UNIX_TIMESTAMP(), `currency` = (`currency` + 20) WHERE `id` = :id");
                    $query->bindParam(":id", $this->ID, PDO::PARAM_INT);
                    $query->execute();
                }
            }
        }

        public function UpdateIpAddress()
        {
            $ip = WebContextManager::GetCurrentIPAddress();
            $query = $this->pdo->prepare("UPDATE `users` SET `ip` = :ip WHERE `id` = :id");
            $query->bindParam(":ip", $ip, PDO::PARAM_STR);
            $query->bindParam(":id", $this->ID, PDO::PARAM_INT);
            $query->execute();
        }

        public function ValidateToken(string $token): bool
        {
            $query = $this->pdo->prepare("SELECT * FROM `users` WHERE `id` = :id");
            $query->bindParam(":tk", $token, PDO::PARAM_STR);
            $query->execute();

            if ($query->rowCount() > 0) {
                return $this->ValidateTokenInternal($query->fetch(PDO::FETCH_OBJ));
            }

            // No valid session found.
            setcookie("token", null, time(), "/");
            return false;
        }

        public function Logout()
        {
            if ($this->IsLoggedIn) {
                $query = $this->pdo->prepare("UPDATE `sessions` SET `valid` = 0 WHERE `id` = :id");
                $query->bindParam(":id", $this->SessionCookieID, PDO::PARAM_INT);
                $query->execute();
            }
        }

        private function ValidateTokenInternal($session): bool
        {
            $query = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
            $query->bindParam(":id", $session->uid, PDO::PARAM_INT);
            $query->execute();

            if ($query->rowCount() > 0) {
                $userInfo = $query->fetch(PDO::FETCH_OBJ);
                $this->ConstructSelf($session, $userInfo);
                $this->UpdateLastSeen();
                $this->UpdateIpAddress();
                $this->UpdateDailyTime($userInfo->dailytime);
                return true;
            }

            // No user info found.
            setcookie("token", null, time(), "/");
            return false;
        }


        private function ConstructSelf($session, $userInfo)
        {
            // Session
            $this->IsLoggedIn = true;
            $this->ID = $session->uid;
            $this->SessionCookieID = $session->id;

            // UserInfo
            $this->Name = $userInfo->username;
            $this->Rank = UserRank::FromInt($userInfo->rank);
            $this->Currency = $userInfo->currency;
        }


        private PDO $pdo = $GLOBALS['pdo'];
        private const $SecondsInDays = 86400;
    }
}
