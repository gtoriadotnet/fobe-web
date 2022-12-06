<?php

/*
    Fobe 2021
*/

namespace Fobe\Users {

    use PDO;

    class Badge
    {
        public static function GiveOfficialBadge(int $badgeid, int $userid)
        {
            $gbadge = $GLOBALS['pdo']->prepare("INSERT INTO user_badges(uid,bid,isOfficial,whenEarned) VALUES(:n, :d, 1, UNIX_TIMESTAMP())");
            $gbadge->bindParam(":n", $userid, PDO::PARAM_INT);
            $gbadge->bindParam(":d", $badgeid, PDO::PARAM_INT);
            if ($gbadge->execute()) {
                return true;
            }
            return false;
        }

        public static function RemoveBadge(int $badgeid, int $userid)
        {
            $rbadge = $GLOBALS['pdo']->prepare("DELETE FROM user_badges WHERE uid = :u AND bid = :b");
            $rbadge->bindParam(":u", $userid, PDO::PARAM_INT);
            $rbadge->bindParam(":b", $badgeid, PDO::PARAM_INT);
            $rbadge->execute();
            if ($rbadge->rowCount() > 0) {
                return true;
            }
            return false;
        }

        public static function UserBadgeInfo(int $badgeid)
        {
            $check = $GLOBALS['pdo']->prepare("SELECT * FROM badges WHERE id = :i");
            $check->bindParam(":i", $badgeid, PDO::PARAM_INT);
            $check->execute();
            return $check->fetch(PDO::FETCH_OBJ);
        }
    }
}