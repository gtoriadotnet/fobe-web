<?php

/*
    Alphaland 2021
*/

namespace Alphaland\Games {

    use Alphaland\Assets\Asset;
    use Alphaland\Grid\RccServiceHelper;
    use Alphaland\Web\WebsiteSettings;
    use Exception;
    use PDO;

    class Game
    {
        public static function AllocatePort() //allocs a port between 50000 - 60000, verifies the port isn't in use by another game server
        {
            $port = 0;
            do {
                $port = rand(50000,60000); //port range forwarded on the server side (support up to 10000 jobs)
                $s = $GLOBALS['pdo']->prepare("SELECT * FROM `open_servers` WHERE `port` = :p AND `status` < 2");
                $s->bindParam(":p", $port, PDO::PARAM_STR);
                $s->execute();
            } while ($s->fetchColumn() != 0 || $port == 57236);
            return $port;
        }

        public static function GenerateJobId()
        {
            $jobid = "";
            do {
                $jobid = gen_uuid();
                $jobcheck = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM `open_servers` WHERE `jobid` = :u");
                $jobcheck->bindParam(":u", $jobid, PDO::PARAM_STR);
                $jobcheck->execute();
               
            } while ($jobcheck->fetchColumn() != 0);
            return $jobid;
        }

        public static function SetChatStyle(int $assetid, int $enum) 
        {
            if ($enum > -1 && $enum < 3) {
                $update = $GLOBALS['pdo']->prepare("UPDATE `assets` SET `ChatStyleEnum` = :enum WHERE `id` = :i");
                $update->bindParam(":enum", $enum, PDO::PARAM_INT);
                $update->bindParam(":i", $assetid, PDO::PARAM_INT);
                $update->execute();
            }
        }

        public static function GetChatStyle(int $assetid)
        {
            $enum = $GLOBALS['pdo']->prepare("SELECT `ChatStyleEnum` FROM `assets` WHERE `id` = :i");
			$enum->bindParam(":i", $assetid, PDO::PARAM_INT);
			$enum->execute();
            return $enum->fetch(PDO::FETCH_OBJ)->ChatStyleEnum;
        }

        public static function ConvertChatStyle(int $chatstyle)
        {
            switch ($chatstyle) {
                case 0:
                    return "Classic";
                case 1:
                    return "Bubble";
                case 2:
                    return "ClassicAndBubble";
                default:
                    return "ClassicAndBubble";
            }
        }

        public static function EnableWhitelist(int $placeid)
        {
            if (isOwner($placeid)) {
                $configgame = $GLOBALS['pdo']->prepare("UPDATE `assets` SET `isGameWhitelisted` = 1 WHERE `id` = :assetid");
                $configgame->bindParam(":assetid", $placeid, PDO::PARAM_INT);
                $configgame->execute();
                if ($configgame->rowCount() > 0) {
                    return true;
                }
                return false;
            }
        }

        public static function UserAccess(int $placeid, int $userid)
        {
            if (Asset::GetAssetInfo($placeid)->isGameWhitelisted) { //game whitelisted
                $whitelist = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM `game_access` WHERE `placeid` = :pid AND `userid` = :uid");
                $whitelist->bindParam(":pid", $placeid, PDO::PARAM_INT);
                $whitelist->bindParam(":uid", $userid, PDO::PARAM_INT);
                $whitelist->execute();
                if ($whitelist->fetchColumn() > 0 || $userid == Asset::GetAssetInfo($placeid)->CreatorId) {
                    return true;
                }
                return false;
            }
            return true;
        }
        
        public static function WhitelistAddUser(int $placeid, int $userid)
        {
            if (isOwner($placeid)) {
                if ($userid != Asset::GetAssetInfo($placeid)->CreatorId && !Game::UserAccess($placeid, $userid)) {
                    $whitelist = $GLOBALS['pdo']->prepare("INSERT INTO game_access(placeid, userid, whenWhitelisted) VALUES (:pid, :uid, UNIX_TIMESTAMP())");
                    $whitelist->bindParam(":pid", $placeid, PDO::PARAM_INT);
                    $whitelist->bindParam(":uid", $userid, PDO::PARAM_INT);
                    $whitelist->execute();
                    return true;
                }
                throw new Exception("Invalid user");
            }
            throw new Exception("Invalid permissions");
        }

        public static function WhitelistRemoveUser(int $placeid, int $userid)
        {
            if (isOwner($placeid)) {
                if ($userid != Asset::GetAssetInfo($placeid)->CreatorId) {
                    $whitelistremove = $GLOBALS['pdo']->prepare("DELETE FROM game_access WHERE placeid = :pid AND userid = :uid");
                    $whitelistremove->bindParam(":pid", $placeid, PDO::PARAM_INT);
                    $whitelistremove->bindParam(":uid", $userid, PDO::PARAM_INT);
                    $whitelistremove->execute();
                    if ($whitelistremove->rowCount() > 0) {
                        return true;
                    }
                    throw new Exception("Failed to remove user");
                }
                throw new Exception("Invalid user");
            }
            throw new Exception("Invalid permissions");
        }

        public static function ClearWhitelist(int $placeid)
        {
            if (isOwner($placeid)) {
                $whitelistclear = $GLOBALS['pdo']->prepare("DELETE FROM game_access WHERE placeid = :pid");
                $whitelistclear->bindParam(":pid", $placeid, PDO::PARAM_INT);
                $whitelistclear->execute();
                if ($whitelistclear->rowCount() > 0)
                {
                    return true;
                }
            }
            return false;
        }

        public static function CloseDeadJobs(int $placeid)
        {
            $jobinfo = $GLOBALS['pdo']->prepare("UPDATE `open_servers` SET `status` = 2, `killedby` = 0, `whenDied` = UNIX_TIMESTAMP() WHERE `gameID` = :g AND (`lastPing` + 95) < UNIX_TIMESTAMP() AND (`status` = 0 OR `status` = 1)"); 
            $jobinfo->bindParam(":g", $placeid, PDO::PARAM_INT);
            $jobinfo->execute();
            if ($jobinfo->rowCount() > 0) {
                return true;
            }
            return false;
        }

        public static function JobClosed(string $jobid)
        {
            $job = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM `open_servers` WHERE `jobid` = :j AND `status` = 2");
            $job->bindParam(":j", $jobid, PDO::PARAM_STR);
            $job->execute();
            
            if ($job->fetchColumn() > 0) {
                return true;
            }
            return false;
        }

        public static function TotalPlayerCount(int $placeid)
        {
            $job = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM `game_presence` WHERE `placeid` = :placeid AND (`lastPing` + 50) > UNIX_TIMESTAMP()");
            $job->bindParam(":placeid", $placeid, PDO::PARAM_INT);
            $job->execute(); 
            return $job->fetchColumn();
        }

        public static function JobPlayerCount(int $placeid, string $jobid)
        {
            $p = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM `game_presence` WHERE `placeid` = :p AND `jobid` = :j AND (`lastPing` + 50) > UNIX_TIMESTAMP()");
            $p->bindParam(":p", $placeid, PDO::PARAM_INT);
            $p->bindParam(":j", $jobid, PDO::PARAM_STR);
            $p->execute();
            return $p->fetchColumn();
        }

        public static function CloseAllJobs(int $placeid)
        {
            $servers = $GLOBALS['pdo']->prepare("SELECT * FROM `open_servers` WHERE `gameID` = :gid AND `status` < 2");
            $servers->bindParam(":gid", $placeid, PDO::PARAM_INT);
            $servers->execute();
            if ($servers->rowCount() > 0) {
                $CloseJob = new RccServiceHelper($GLOBALS['gamesArbiter']);
                foreach ($servers as $server) {
                    $CloseJob->CloseJob($server['jobid']);
                }
                return true;
            }
            return false;
        }

        public static function SetToPersonalBuildPlace(int $placeid)
        {
            $set = $GLOBALS['pdo']->prepare("UPDATE `assets` SET `isPersonalServer` = 1 WHERE `id` = :i");
            $set->bindParam(":i", $placeid, PDO::PARAM_INT);
            $set->execute();
            if ($set->rowCount() > 0) {
                return true;
            }
            return false;
        }

        public static function SetToPlace(int $placeid)
        {
            $set = $GLOBALS['pdo']->prepare("UPDATE assets SET `isPersonalServer` = 0 WHERE `id` = :i");
            $set->bindParam(":i", $placeid, PDO::PARAM_INT);
            $set->execute();
            if ($set->rowCount() > 0) {
                return true;
            }
            return false;
        }

        public static function GetAllGames()
        {
            $games = $GLOBALS['pdo']->query("SELECT * FROM assets WHERE AssetTypeId = 9 ORDER BY Visited DESC");
            return $games;
        }

        public static function ArbiterOnline() //the main portion of this check is now a background script
        {
            return WebsiteSettings::GetSetting("isGameServerAlive");
        }

        public static function RemovePersonalBuildServerRank(int $placeid, int $userid)
        {
            $remove = $GLOBALS['pdo']->prepare("DELETE FROM personal_build_ranks WHERE placeid = :pid AND userid = :uid");
            $remove->bindParam(":pid", $placeid, PDO::PARAM_INT);
            $remove->bindParam(":uid", $userid, PDO::PARAM_INT);
            $remove->execute();
            if ($remove->rowCount() > 0) {
                return true;
            }
            return false;
        }

        public static function GetPersonalBuildServerRank(int $placeid, int $userid)
        {
            if ($userid == Asset::GetAssetInfo($placeid)->CreatorId) {
                return 255;
            } else {
                $rank = $GLOBALS['pdo']->prepare("SELECT * FROM personal_build_ranks WHERE placeid = :pid AND userid = :uid");
                $rank->bindParam(":pid", $placeid, PDO::PARAM_INT);
                $rank->bindParam(":uid", $userid, PDO::PARAM_INT);
                $rank->execute();
                if ($rank->rowCount() > 0) {
                    return $rank->fetch(PDO::FETCH_OBJ)->rank;
                }
            }
            return 10; //no rank. consider them Visitor rank
        }

        public static function PersonalBuildRankToName($rank) 
        {
            switch ($rank)
            {
                case 255:
                    return "Owner";
                case 240:
                    return "Admin";
                case 128:
                    return "Member";
                case 10:
                    return "Visitor";
                case 0:
                    return "Banned";
            }
        }

        public static function RemovePlayerFromQueue(int $userid)
        {
            $removeQueue = $GLOBALS['pdo']->prepare("DELETE FROM game_launch_queue WHERE userid = :uid");
            $removeQueue->bindParam(":uid", $userid, PDO::PARAM_INT);
            $removeQueue->execute();
            if ($removeQueue->rowCount() > 0) {
                return true;
            }
            return false;
        }

        public static function IsPlayerInQueue(int $placeid, string $jobid, int $userid)
        {
            $playerinqueue = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM game_launch_queue WHERE placeid = :pid AND jobid = :jid AND userid = :uid");
            $playerinqueue->bindParam(":pid", $placeid, PDO::PARAM_INT);
            $playerinqueue->bindParam(":jid", $jobid, PDO::PARAM_STR);
            $playerinqueue->bindParam(":uid", $userid, PDO::PARAM_INT);
            $playerinqueue->execute();
            if ($playerinqueue->fetchColumn() > 0) {
                return true;
            }
            return false;
        }

        public static function AddPlayerToQueue(int $placeid, string $jobid, int $userid)
        {
            if (!Game::IsPlayerInQueue($placeid, $jobid, $userid)) {
                Game::RemovePlayerFromQueue($userid); //if any queue leftover
                $newQueue = $GLOBALS['pdo']->prepare("INSERT INTO game_launch_queue(placeid, jobid, userid, queuePing, whenQueued) VALUES (:pid, :jid, :uid, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
                $newQueue->bindParam(":pid", $placeid, PDO::PARAM_INT);
                $newQueue->bindParam(":jid", $jobid, PDO::PARAM_STR);
                $newQueue->bindParam(":uid", $userid, PDO::PARAM_INT);
                $newQueue->execute();
            } else { //ping
                $updateQueue = $GLOBALS['pdo']->prepare("UPDATE game_launch_queue SET queuePing = UNIX_TIMESTAMP() WHERE placeid = :pid AND jobid = :jid AND userid = :uid");
                $updateQueue->bindParam(":pid", $placeid, PDO::PARAM_INT);
                $updateQueue->bindParam(":jid", $jobid, PDO::PARAM_STR);
                $updateQueue->bindParam(":uid", $userid, PDO::PARAM_INT);
                $updateQueue->execute();
            }
        }

        public static function IsNextInQueue($placeid, $jobid, $userid)
        {
            $queue = $GLOBALS['pdo']->prepare("SELECT * FROM game_launch_queue WHERE placeid = :pid AND jobid = :jid ORDER BY whenQueued DESC LIMIT 1");
            $queue->bindParam(":pid", $placeid, PDO::PARAM_INT);
            $queue->bindParam(":jid", $jobid, PDO::PARAM_STR);
            $queue->execute();
            $queue = $queue->fetch(PDO::FETCH_OBJ);
            if ((int)$queue->queuePing + 10 < time()) { //hasnt pinged in 10 seconds, assume they left queue
                Game::RemovePlayerFromQueue($queue->userid);
            } else if ($queue->userid == $userid) {
                return true;
            }
            return false;
        }
    }
}
