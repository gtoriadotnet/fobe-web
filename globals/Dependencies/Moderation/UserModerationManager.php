<?php

namespace Alphaland\Moderation {

    use PDO;

    class UserModerationManager
    {
        public static function IsBanned(int $userId)
        {
            $query = $GLOBALS['pdo']->prepare("SELECT * FROM `user_bans` WHERE `uid` = :i AND `valid` = 1");
            $query->bindParam(":i", $userId, PDO::PARAM_INT);
            $query->execute();
            if ($query->rowCount() > 0) {
                return true;
            }
            return false;
        }
        
        public static function UnbanUser(int $uid)
        {
            if($GLOBALS['user']->isStaff()) {
                if (userExists($uid)) { 
                    $unban = $GLOBALS['pdo']->prepare("DELETE FROM user_bans WHERE uid = :u");
                    $unban->bindParam(":u", $uid, PDO::PARAM_INT);
                    $unban->execute();
                    if ($unban->rowCount() > 0) {
                        logStaffAction("Unbanned User ".$uid);
                        return true;
                    }
                }
            }
            return false;
        }

        // Nikita: TODO: Convert the bantype to a an enum
        public static function BanUser(int $uid, string $reason, int $banexpiration, int $bantype)
        {
            if($GLOBALS['user']->isStaff()) {
                if (userExists($uid)) {
                    $isstaffcheck = $GLOBALS['pdo']->prepare("SELECT * FROM `users` WHERE `id` = :i AND `rank` > 0");
                    $isstaffcheck->bindParam(":i", $uid, PDO::PARAM_INT);
                    $isstaffcheck->execute();
                    
                    if ($isstaffcheck->rowCount() == 0) {
                        if (!UserModerationManager::IsBanned($uid)) {
                            $ban = $GLOBALS['pdo']->prepare("INSERT INTO `user_bans`(`uid`, `banReason`, `whenBanned`, `banExpiration`, `banType`, `whoBanned`, `valid`) VALUES(:u, :br, UNIX_TIMESTAMP(), :be, :bt, :wb, 1)");
                            $ban->bindParam(":u", $uid, PDO::PARAM_INT);
                            $ban->bindParam(":br", $reason, PDO::PARAM_STR);
                            $ban->bindParam(":be", $banexpiration, PDO::PARAM_INT);
                            $ban->bindParam(":bt", $bantype, PDO::PARAM_INT);
                            $ban->bindParam(":wb", $GLOBALS['user']->id, PDO::PARAM_INT);
                            $ban->execute();
                            if ($ban->rowCount() > 0) {
                                kickUserIfInGame($uid, "You've been banned from Alphaland, '".$reason."'");
                                logStaffAction("Banned User ".$uid);
                                
                                //ban user from discord with bot
                                if($bantype == 2) { //perm ban
                                    $discordid = $GLOBALS['pdo']->prepare("SELECT * FROM `alphaland_verification` WHERE `uid` = :id AND isactivated = 1");
                                    $discordid->bindParam(":id", $uid, PDO::PARAM_INT);
                                    $discordid->execute();
                                    if ($discordid->rowCount() > 0) {
                                        $discordid = $discordid->fetch(PDO::FETCH_OBJ)->discordid;
                                        httpGetPing("http://localhost:4098/?type=ban&id=".$discordid."&reason=".urlencode($reason), 5000); 
                                    }
                                }
                                return true;
                            }
                        }
                    }
                }
            }
            return false;
        }

        public static function ReferralLimbBan(int $userid, string $reason)
        {
            $query = $GLOBALS['pdo']->prepare("SELECT * FROM `users_invited` WHERE `whoInvited` = :userid");
            $query->bindParam(":userid", $userid, PDO::PARAM_INT);
            $query->execute();
            if ($query->rowCount() > 0) {
                if (UserModerationManager::BanUser($userid, $reason, 0, 2)) {
                    foreach($query as $user) {
                        UserModerationManager::BanUser($user['invitedUser'], $reason, 0, 2); //perm ban
                    }
                }
                return true;
            }
            return false;
        }

        public static function PoisonBan(int $userid, string $reason)
        {
            $ip = userInfo($userid)->ip;
            if (UserModerationManager::BanUser($userid, $reason, 0, 2)) {
                $users = $GLOBALS['pdo']->prepare("SELECT * FROM `users` WHERE `ip` = :ip AND `rank` < 1");
                $users->bindParam(":ip", $ip, PDO::PARAM_STR);
                $users->execute();

                foreach ($users as $user) {
                   UserModerationManager::BanUser($user['id'], $reason, 0, 2); //perm ban
                }
                return true;
            }
            return false;
        }
    }
}
