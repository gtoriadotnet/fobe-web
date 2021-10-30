<?php

namespace Alphaland\Moderation {

    use PDO;

    class UserModerationManager
    {
        public static function IsBanned(int $userId): bool
        {
            $query = UserModerationManager::$pdo->prepare("SELECT COUNT(*) FROM `user_bans` WHERE `uid` = :i AND `valid` = 1");
            $query->bindParam(":i", $userId, PDO::PARAM_INT);
            $query->execute();
            if ($query->fetchColumn(0) > 0) return true;
            return false;
        }

        private static PDO $pdo = $GLOBALS['pdo'];
    }
}
