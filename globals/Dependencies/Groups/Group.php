<?php

/*
    Fobe 2021
    Astro: Jesus christ this is big, probably due to a lot of whitespace as well. Perhaps reduce the size?
    Nikita: (YOUR DUMBASS COMMENT HERE)
*/

namespace Fobe\Groups {

    use Fobe\Economy\EconomyHelper;
    use Fobe\UI\ImageHelper;
    use Fobe\Users\User;
    use Exception;
    use PDO;

    class Group
    {
        public static function Exists(int $groupid)
        {
            $group = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM groups WHERE id = :u");
            $group->bindParam(":u", $groupid, PDO::PARAM_INT);
            $group->execute();
            if ($group->fetchColumn() > 0) {
                return true;
            }
            return false;
        }

        public static function NameExists(string $name)
        {
            $checkname = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM groups WHERE name = :na");
            $checkname->bindParam(":na", $name, PDO::PARAM_STR);
            $checkname->execute();
            if ($checkname->fetchColumn() > 0) {
                return true;
            }
            return false;
        }

        public static function IsOwner(int $userid, int $groupid)
        {
            $owner = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM groups WHERE id = :gid AND creatorid = :cid");
            $owner->bindParam(":gid", $groupid, PDO::PARAM_INT);
            $owner->bindParam(":cid", $userid, PDO::PARAM_INT);
            $owner->execute();
            if ($owner->fetchColumn() > 0) {
                return true;
            }
            return false;
        }

        public static function IsMember(int $userid, int $groupid)
        {
            $member = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM group_members WHERE userid = :uid AND groupid = :gid");
            $member->bindParam(":uid", $userid, PDO::PARAM_INT);
            $member->bindParam(":gid", $groupid, PDO::PARAM_INT);
            $member->execute();
            
            if ($member->fetchColumn() > 0) {
                return true;
            }
            return false;
        }

        public static function IsPendingRequest(int $userid, int $groupid)
        {
            $pending = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM group_join_requests WHERE groupid = :gid AND userid = :uid");
            $pending->bindParam(":gid", $groupid, PDO::PARAM_INT);
            $pending->bindParam(":uid", $userid, PDO::PARAM_INT);
            $pending->execute();
            if ($pending->fetchColumn() > 0) {
                return true;
            }
            return false;
        }

        public static function IsManualApproval(int $groupid)
        {
            $manual = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM groups WHERE id = :gid AND manualapproval = 1");
            $manual->bindParam(":gid", $groupid, PDO::PARAM_INT);
            $manual->execute();
            if ($manual->fetchColumn() > 0) {
                return true;
            }
            return false;
        }

        public static function RankExists(int $groupid, int $rank)
        {
            $role = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM group_roles WHERE groupid = :groupid AND rank = :rank");
            $role->bindParam(":groupid", $groupid, PDO::PARAM_INT);
            $role->bindParam(":rank", $rank, PDO::PARAM_INT);
            $role->execute();
            if ($role->fetchColumn() > 0) {
                return true;
            }
            return false;
        }

        public static function MemberInfo(int $groupid, int $userid)
        {
            $member = $GLOBALS['pdo']->prepare("SELECT * FROM group_members WHERE userid = :uid AND groupid = :gid");
            $member->bindParam(":uid", $userid, PDO::PARAM_INT);
            $member->bindParam(":gid", $groupid, PDO::PARAM_INT);
            $member->execute();
            return $member->fetch(PDO::FETCH_OBJ);
        }

        public static function RoleInfo(int $groupid, int $rank)
        {
            $role = $GLOBALS['pdo']->prepare("SELECT * FROM group_roles WHERE groupid = :gid AND rank = :r");
            $role->bindParam(":gid", $groupid, PDO::PARAM_INT);
            $role->bindParam(":r", $rank, PDO::PARAM_INT);
            $role->execute();
            return $role->fetch(PDO::FETCH_OBJ);
        }

        public static function GetName(int $groupid)
        {
            $name = $GLOBALS['pdo']->prepare("SELECT * FROM groups WHERE id = :u");
            $name->bindParam(":u", $groupid, PDO::PARAM_INT);
            $name->execute();
            return $name->fetch(PDO::FETCH_OBJ)->name;
        }

        public static function GetDescription(int $groupid)
        {
            $name = $GLOBALS['pdo']->prepare("SELECT * FROM groups WHERE id = :u");
            $name->bindParam(":u", $groupid, PDO::PARAM_INT);
            $name->execute();
            return cleanOutput($name->fetch(PDO::FETCH_OBJ));
        }

        public static function GetRankName(int $rank, int $groupid)
        {
            $name = $GLOBALS['pdo']->prepare("SELECT * FROM group_roles WHERE groupid = :gid AND rank = :rank");
            $name->bindParam(":gid", $groupid, PDO::PARAM_INT);
            $name->bindParam(":rank", $rank, PDO::PARAM_INT);
            $name->execute();
            return $name->fetch(PDO::FETCH_OBJ)->rolename;
        }

        public static function GetUserRankName(int $userid, int $groupid)
        {
            return Group::GetRankName(Group::MemberInfo($groupid, $userid)->rank, $groupid);
        }

        public static function GetRank($userid, $groupid)
        {
            if (Group::IsMember($userid, $groupid)) {
                return Group::MemberInfo($groupid, $userid)->rank;
            }
        }

        public static function GetLowestRank(int $groupid)
        {
            $getrole = $GLOBALS['pdo']->prepare("SELECT rank FROM `group_roles` WHERE groupid = :groupid ORDER BY rank ASC LIMIT 1"); //lowest rank available
            $getrole->bindParam(":groupid", $groupid, PDO::PARAM_INT);
            $getrole->execute();
            return $getrole->fetchColumn();
        }

        public static function MemberCount(int $groupid)
        {
            $count = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM group_members WHERE groupid = :gid");
            $count->bindParam(":gid", $groupid, PDO::PARAM_INT);
            $count->execute();
            return $count->fetchColumn();
        }

        public static function RankMemberCount(int $groupid, int $rank)
        {
            $count = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM group_members WHERE groupid = :gid AND rank = :r");
            $count->bindParam(":gid", $groupid, PDO::PARAM_INT);
            $count->bindParam(":r", $rank, PDO::PARAM_INT);
            $count->execute();
            return $count->fetchColumn();
        }

        public static function MemberRoleInfo(int $userid, int $groupid)
        {
            if (Group::IsMember($userid, $groupid)) { 
                return Group::RoleInfo($groupid, Group::MemberInfo($groupid, $userid)->rank);
            }
        }

        public static function RankingCooldown(int $groupid)
        {
            $whenCreated = $GLOBALS['pdo']->prepare("SELECT * FROM group_roles WHERE groupid = :groupid ORDER BY whenCreated DESC LIMIT 1");
            $whenCreated->bindParam(":groupid", $groupid, PDO::PARAM_INT);
            $whenCreated->execute();

            if ($whenCreated->rowCount() > 0) {
                if($whenCreated->fetch(PDO::FETCH_OBJ)->whenCreated + 60 > time()) {
                    return true;
                }
            }
            return false;
        }

        public static function PostingCooldown(int $userid)
        {
            $whenCreated = $GLOBALS['pdo']->prepare("SELECT * FROM group_posts WHERE userid = :userid ORDER BY postdate DESC LIMIT 1");
            $whenCreated->bindParam(":userid", $userid, PDO::PARAM_INT);
            $whenCreated->execute();

            if ($whenCreated->rowCount() > 0) {
                if($whenCreated->fetch(PDO::FETCH_OBJ)->postdate + 60 > time()) {
                    return true;
                }
            }
            return false;
        }

        public static function IsInGroup(int $userid, int $groupid)
        {
            $member = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM `group_members` WHERE `userid` = :uid AND `groupid` = :gid");
            $member->bindParam(":uid", $userid, PDO::PARAM_INT);
            $member->bindParam(":gid", $groupid, PDO::PARAM_INT);
            $member->execute();
            if ($member->fetchColumn() > 0) {
                return true;
            }
            return false;
        }

        public static function NewJoinRequest(int $groupid, int $userid)
        {
            $newrequest = $GLOBALS['pdo']->prepare("INSERT INTO `group_join_requests`(`groupid`, `userid`, `whenRequested`) VALUES (:groupid, :userid, UNIX_TIMESTAMP())");
            $newrequest->bindParam(":groupid", $groupid, PDO::PARAM_INT);
            $newrequest->bindParam(":userid", $userid, PDO::PARAM_INT);
            $newrequest->execute();
            if ($newrequest->rowCount() > 0) {
                return true;
            }
            return false;
        }

        public static function WallViewPermission(int $userid, int $groupid)
        {
            if (Group::MemberRoleInfo($userid, $groupid)->AccessGroupWall) {
                return true;
            }
            return false;
        }

        public static function WallPostPermission(int $userid, int $groupid)
        { 
            if (Group::MemberRoleInfo($userid, $groupid)->PostGroupWall) {
                return true;
            }
            return false;
        }

        public static function WallDeletePermission(int $userid, int $groupid)
        {
            if (Group::MemberRoleInfo($userid, $groupid)->DeleteGroupWallPosts) {
                return true;
            }
            return false;
        }

        public static function PostShoutPermission(int $userid, int $groupid)
        {
            if (Group::MemberRoleInfo($userid, $groupid)->PostGroupShout) {
                return true;
            }
            return false;
        }

        public static function ManageLowerRankPermission(int $userid, int $groupid)
        {
            if (Group::MemberRoleInfo($userid, $groupid)->ManageLowerRanks) {
                return true;
            }
            return false;
        }

        public static function KickLowerRankPermission(int $userid, int $groupid)
        {
            if (Group::MemberRoleInfo($userid, $groupid)->KickLowerRanks) {
                return true;
            }
            return false;
        }

        public static function AcceptJoinRequestPermission(int $userid, int $groupid)
        {
            if (Group::MemberRoleInfo($userid, $groupid)->AcceptJoinRequests) {
                return true;
            }
            return false;
        }

        public static function ViewAuditLogPermission(int $userid, int $groupid)
        {
            if (Group::MemberRoleInfo($userid, $groupid)->ViewAuditLog) {
                return true;
            }
            return false;
        }

        public static function ConfigPermission(int $userid, int $groupid)
        {
            if (Group::KickLowerRankPermission($userid, $groupid) || Group::KickLowerRankPermission($userid, $groupid) || Group::AcceptJoinRequestPermission($userid, $groupid) || Group::ViewAuditLogPermission($userid, $groupid)) {
                return true;
            }
            return false;
        }

        public static function CreateRole(int $groupid, string $name, int $rank)
        {
            $name = cleanInput($name);
            $localplayer = $GLOBALS['user']->id;

            if (!$groupid || !$rank) {
                throw new Exception('Missing parameters');
            } else if (!Group::IsOwner($localplayer, $groupid)) {
                throw new Exception('You do not have permission to perform this action');
            } else if ($rank < 0 || $rank > 254) {
                throw new Exception('Rank must be above 0 and below 255');
            } else if (Group::RankExists($groupid, $rank)) {
                throw new Exception('Rank '.$rank.' already exists');
            } else if (strlen($name) > 30) {
                throw new Exception('Role name is too long');
            } else if (strlen($name) < 3) {
                throw new Exception('Role name is too short');
            } else if (Group::RankingCooldown($groupid)) {
                throw new Exception('Please wait before creating another role');
            } else {
                $newrole = $GLOBALS['pdo']->prepare("INSERT INTO `group_roles` (`groupid`, `rolename`, `rank`, `AccessGroupWall`, `PostGroupWall`, `DeleteGroupWallPosts`, `PostGroupShout`, `ManageLowerRanks`, `KickLowerRanks`, `AcceptJoinRequests`, `ViewAuditLog`, `whenCreated`) VALUES (:groupid, :rolename, :rank, '1', '1', '0', '0', '0', '0', '0', '0', UNIX_TIMESTAMP())");
                $newrole->bindParam(":groupid", $groupid, PDO::PARAM_INT);
                $newrole->bindParam(":rolename", $name, PDO::PARAM_STR);
                $newrole->bindParam(":rank", $rank, PDO::PARAM_INT);
                $newrole->execute();
                if ($newrole->rowCount() > 0) {
                    EconomyHelper::RemoveAlphabux(15, $localplayer, 'Purchase of role named '.$name.', groupid '.$groupid);
                    return true;
                }
                return false;
            }
        }

        public static function CreatePost(int $groupid, int $userid, string $post)
        {
            $post = cleanInput($post);

            if (!$groupid || !$userid) {
                throw new Exception('Error occurred');
            } else if (!$post) {
                throw new Exception('Post cannot be blank');
            } else if (!Group::IsMember($userid, $groupid) || !Group::WallPostPermission($userid, $groupid)) {
                throw new Exception('You do not have permission to perform this action');
            } else if (strlen($post) > 256) {
                throw new Exception('Post is too long');
            } else if (strlen($post) < 3) {
                throw new Exception('Post is too short');
            } else if (Group::PostingCooldown($userid)) {
                throw new Exception('Please wait before posting again');
            } else {
                $newpost = $GLOBALS['pdo']->prepare("INSERT INTO group_posts(userid, groupid, post, postdate) VALUES(:u, :gid, :p, UNIX_TIMESTAMP())");
				$newpost->bindParam(":u", $userid, PDO::PARAM_INT);
				$newpost->bindParam(":gid", $groupid, PDO::PARAM_INT);
				$newpost->bindParam(":p", $post, PDO::PARAM_STR);
				$newpost->execute();
                if ($newpost->rowCount() > 0){
					return true;
				}
                return false;
            }
        }

        public static function DeletePost(int $postid, int $groupid)
        {
            $localplayer = $GLOBALS['user']->id;

            if (!Group::WallDeletePermission($localplayer, $groupid)) {
                throw new Exception('You do not have permission to perform this action');
            } else {
                $deletepost = $GLOBALS['pdo']->prepare("DELETE FROM group_posts WHERE id = :id AND groupid = :groupid"); //lowest rank available
                $deletepost->bindParam(":id", $postid, PDO::PARAM_INT);
                $deletepost->bindParam(":groupid", $groupid, PDO::PARAM_INT);
                $deletepost->execute();
                if ($deletepost->rowCount() > 0) {
                    return true;
                }
                return false;
            }
        }

        public static function Leave(int $userid, int $groupid)
        {
            if (Group::IsOwner($userid, $groupid) || !Group::IsMember($userid, $groupid) || Group::IsPendingRequest($userid, $groupid)) {
                throw new Exception('You do not have permission to perform this action');
            } else {
                $deletegroupuser = $GLOBALS['pdo']->prepare("DELETE FROM group_members WHERE userid = :userid AND groupid = :groupid");
                $deletegroupuser->bindParam(":userid", $userid, PDO::PARAM_INT);
                $deletegroupuser->bindParam(":groupid", $groupid, PDO::PARAM_INT);
                $deletegroupuser->execute();
                if ($deletegroupuser->rowCount() > 0) {
                    return true;
                }
                return false;
            }
        }

        public static function ExileUser(int $groupid, int $userid)
        {
            $localplayer = $GLOBALS['user']->id;

            if (!Group::IsOwner($localplayer, $groupid) || !Group::IsMember($userid, $groupid) || Group::GetRank($userid, $groupid) == 255) {
                throw new Exception('You do not have permission to perform this action');
            } else {
                $deleteuser = $GLOBALS['pdo']->prepare("DELETE FROM group_members WHERE userid = :userid AND groupid = :groupid");
                $deleteuser->bindParam(":userid", $userid, PDO::PARAM_INT);
                $deleteuser->bindParam(":groupid", $groupid, PDO::PARAM_INT);
                $deleteuser->execute();
                if ($deleteuser->rowCount() > 0) {
                    return true;
                }
                return false;
            }
        }

        public static function UpdateUserRank(int $groupid, int $userid, int $rank)
        {
            $localplayer = $GLOBALS['user']->id;
            
            if (!$groupid || !$userid || !$rank) {
                throw new Exception('Error occurred');
            } else if (!Group::ManageLowerRankPermission($localplayer, $groupid) || 
                    Group::GetRank($userid, $groupid) >= Group::GetRank($localplayer, $groupid) || 
                    Group::GetRank($userid, $groupid) == 255 || 
                    $rank == 255 || 
                    !Group::RankExists($groupid, $rank)) {
                throw new Exception('You do not have permission to perform this action');
            } else {
                $updateuser = $GLOBALS['pdo']->prepare("UPDATE `group_members` SET rank = :rank WHERE userid = :userid AND groupid = :groupid");
                $updateuser->bindParam(":rank", $rank, PDO::PARAM_INT);
                $updateuser->bindParam(":userid", $userid, PDO::PARAM_INT);
                $updateuser->bindParam(":groupid", $groupid, PDO::PARAM_INT);
                $updateuser->execute();
                if ($updateuser->rowCount() > 0) {
                    return true;
                }
                return false;
            }
        }

        public static function Create(string $name, string $description, bool $approval, int $creatorid, string $base64emblem)
        {            
            $name = cleanInput($name);
            $description = cleanInput($description);
            $approval = boolval($approval);
            $base64emblem = file_get_contents($base64emblem); //this removes the header from js post and base64 decodes it, very convenient
            $mimetype = finfo_buffer(finfo_open(), $base64emblem, FILEINFO_MIME_TYPE); //file type

            if (!$name || !$description || !$creatorid || !$base64emblem) {
                throw new Exception('Missing required fields');
            } else if (Group::NameExists($name)) {
                throw new Exception('Group name already taken');
            } else if (strlen($name) > 50) {
                throw new Exception('Group name too long');
            } else if (strlen($name) < 3) {
                throw new Exception('Group name too short');
            } else if (strlen($description) > 1024) {
                throw new Exception('Group description too long');
            } else if (strlen($description) < 3) {
                throw new Exception('Group description too short');
            } else if (!in_array($mimetype, array('image/png','image/jpeg'))) {
                throw new Exception('Invalid image provided');
            } else if (!User::UserExists($creatorid)) {
                throw new Exception('Error Occurred');
            } else if (!EconomyHelper::RemoveAlphabux(20, $creatorid, "Purchase of group named ".$name)) {
                throw new Exception('Not enough Alphabux to purchase a group');
            } else {
                //new hash
                $emblemhash = genAssetHash(16);
                    
                if (!ImageHelper::ResizeImageFromString(150, 150, $GLOBALS['thumbnailCDNPath'] . $emblemhash, $base64emblem)) { //resize to 150x150
                    throw new Exception('Error occurred');
                }


                //TODO: clean up a bit vvvv


                try //wrap this in a try-catch block, if anything happens we immediately unlock the db
                {
                    $GLOBALS['pdo']->exec("LOCK TABLES assets WRITE"); //lock since this stuff is sensitive
                        
                    $b = $GLOBALS['pdo']->prepare("SELECT * FROM assets");
                    $b->execute();
                                                            
                    //grab auto increment values
                    $autoincrement = $b->rowCount() + 1; //initial auto increment value
                        
                    //add texture to assets
                    $assetname = $name . " Emblem";
                    $x = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`, `Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,22,:aname,'Group Emblem',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),:oid,:aid2,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,:hash)");
                    $x->bindParam(":aid", $autoincrement, PDO::PARAM_INT);
                    $x->bindParam(":aname", $assetname, PDO::PARAM_STR);
                    $x->bindParam(":oid", $creatorid, PDO::PARAM_INT);
                    $x->bindParam(":aid2", $autoincrement, PDO::PARAM_INT);
                    $x->bindParam(":hash", $emblemhash, PDO::PARAM_STR);
                    $x->execute();
                        
                    $GLOBALS['pdo']->exec("UNLOCK TABLES"); 

        
                    $GLOBALS['pdo']->exec("LOCK TABLES groups WRITE"); //lock since this stuff is sensitive
                    
                    $g = $GLOBALS['pdo']->prepare("SELECT * FROM groups");
                    $g->execute();
                                                            
                    //grab auto increment values
                    $nextgroup = $g->rowCount() + 1; //initial auto increment value
                        
                    $group = $GLOBALS['pdo']->prepare("INSERT INTO `groups` (`id`, `name`, `description`, `manualapproval`, `creatorid`, `emblem`, `moderated`) VALUES (:id, :name, :description, :approvals, :creatorid, :emblem, 0)");
                    $group->bindParam(":id", $nextgroup, PDO::PARAM_INT);
                    $group->bindParam(":name", $name, PDO::PARAM_STR);
                    $group->bindParam(":description", $description, PDO::PARAM_STR);
                    $group->bindParam(":approvals", $approval, PDO::PARAM_INT);
                    $group->bindParam(":creatorid", $creatorid, PDO::PARAM_INT);
                    $group->bindParam(":emblem", $autoincrement, PDO::PARAM_INT);
                    $group->execute();
                        
                    $GLOBALS['pdo']->exec("UNLOCK TABLES"); 
                        
        
                    $groupjoin = $GLOBALS['pdo']->prepare("INSERT INTO `group_members` (`userid`, `groupid`, `rank`, `whenJoined`) VALUES (:userid, :groupid, '255', UNIX_TIMESTAMP())");
                    $groupjoin->bindParam(":userid", $creatorid, PDO::PARAM_INT);
                    $groupjoin->bindParam(":groupid", $nextgroup, PDO::PARAM_INT);
                    $groupjoin->execute();
                        
                    $ownerrole = $GLOBALS['pdo']->prepare("INSERT INTO `group_roles` (`groupid`, `rolename`, `rank`, `AccessGroupWall`, `PostGroupWall`, `DeleteGroupWallPosts`, `PostGroupShout`, `ManageLowerRanks`, `KickLowerRanks`, `AcceptJoinRequests`, `ViewAuditLog`) VALUES (:groupid, 'Owner', '255', '1', '1', '1', '1', '1', '1', '1', '1')");
                    $ownerrole->bindParam(":groupid", $nextgroup, PDO::PARAM_INT);
                    $ownerrole->execute();
                        
                    $adminrole = $GLOBALS['pdo']->prepare("INSERT INTO `group_roles` (`groupid`, `rolename`, `rank`, `AccessGroupWall`, `PostGroupWall`, `DeleteGroupWallPosts`, `PostGroupShout`, `ManageLowerRanks`, `KickLowerRanks`, `AcceptJoinRequests`, `ViewAuditLog`) VALUES (:groupid, 'Admin', '254', '1', '1', '1', '1', '0', '0', '0', '0')");
                    $adminrole->bindParam(":groupid", $nextgroup, PDO::PARAM_INT);
                    $adminrole->execute();
                        
                    $memberrole = $GLOBALS['pdo']->prepare("INSERT INTO `group_roles` (`groupid`, `rolename`, `rank`, `AccessGroupWall`, `PostGroupWall`, `DeleteGroupWallPosts`, `PostGroupShout`, `ManageLowerRanks`, `KickLowerRanks`, `AcceptJoinRequests`, `ViewAuditLog`) VALUES (:groupid, 'Member', '253', '1', '1', '0', '0', '0', '0', '0', '0')");
                    $memberrole->bindParam(":groupid", $nextgroup, PDO::PARAM_INT);
                    $memberrole->execute();

                } catch (Exception $e) {
                    $GLOBALS['pdo']->exec("UNLOCK TABLES"); //precaution
                    throw new Exception('Critical error occurred, please report this under #bugs');
                }
                return true;
            }
        }
        
        public static function Join(int $groupid, int $userid, bool $force=false)
        {
            if (!Group::Exists($groupid) || Group::IsMember($userid, $groupid) && !$force) {
                throw new Exception('Error occurred');
            } else {
                if (Group::IsManualApproval($groupid) && !$force) {
                    if (Group::NewJoinRequest($groupid, $userid)) {
                        return true;
                    }
                } else {
                    $lowestrank = Group::GetLowestRank($groupid);
                    $join = $GLOBALS['pdo']->prepare("INSERT INTO group_members(userid, groupid, rank, whenJoined) VALUES(:userid, :groupid, :rank, UNIX_TIMESTAMP())");
                    $join->bindParam(":userid", $userid, PDO::PARAM_INT);
                    $join->bindParam(":groupid", $groupid, PDO::PARAM_INT);
                    $join->bindParam(":rank", $lowestrank, PDO::PARAM_INT);
                    $join->execute();
                    if ($join->rowCount() > 0) {
                        return true;
                    }
                }
                return false;
            }
        }        
        
        public static function DeleteJoinRequest(int $groupid, int $userid)
        {
            $localplayer = $GLOBALS['user']->id;

            if (!Group::IsOwner($localplayer, $groupid) || !Group::IsPendingRequest($userid, $groupid)) {
                throw new Exception('You do not have permission to perform this action');
            } else {
                $deleterequest = $GLOBALS['pdo']->prepare("DELETE FROM group_join_requests WHERE groupid = :groupid AND userid = :userid");
                $deleterequest->bindParam(":groupid", $groupid, PDO::PARAM_INT);
                $deleterequest->bindParam(":userid", $userid, PDO::PARAM_INT);
                $deleterequest->execute();
                if ($deleterequest->rowCount() > 0) {
                    return true;
                }
                return false;
            }
        }

        public static function ApproveJoinRequest(int $groupid, int $userid)
        {
            $localplayer = $GLOBALS['user']->id;

            if (!Group::IsOwner($localplayer, $groupid) || !Group::IsPendingRequest($userid, $groupid)) {
                throw new Exception('You do not have permission to perform this action');
            } else {
                if (Group::DeleteJoinRequest($groupid, $userid) && Group::Join($groupid, $userid, true)) {
                    return true;
                }
                return false;
            }
        }

        public static function UpdateGeneralConfig(int $groupid, string $description, bool $approval, string $base64emblem)
        {
            $localplayer = $GLOBALS['user']->id;
            $description = cleanInput($description);
            $approval = boolval($approval);
            $newtextureid = 0;
            
            if (!$groupid) {
                throw new Exception('Error occurred');
            } else if (!Group::IsOwner($localplayer, $groupid)) {
                throw new Exception('You do not have permission to perform this action');
            } else if (!$description) {
                throw new Exception('A description must be provided');
            } else if (strlen($description) < 3) {
                throw new Exception('Group description is too short');
            } else if (strlen($description) > 1024) {
                throw new Exception('Group description is too long');
            } else {
                if ($base64emblem) {
                    $emblemname = Group::GetName($groupid) . " Emblem";
                    $emblemhash = genAssetHash(16);
                    $base64emblem = file_get_contents($base64emblem); //this removes the header from js post and base64 decodes it, very convenient
                    $mimetype = finfo_buffer(finfo_open(), $base64emblem, FILEINFO_MIME_TYPE); //file type

                    if (!in_array($mimetype, array('image/png','image/jpeg'))) {
                        throw new Exception('Invalid image provided');
                    }

                    if (!ImageHelper::ResizeImageFromString(150, 150, $GLOBALS['thumbnailCDNPath'] . $emblemhash, $base64emblem)) { //resize to 150x150
                        throw new Exception('Error occurred');
                    }
                }

                try {
                    $GLOBALS['pdo']->exec("LOCK TABLES assets WRITE"); //lock since this stuff is sensitive
                            
                    $newtextureid = $GLOBALS['pdo']->prepare("SELECT * FROM assets");
                    $newtextureid->execute();                                             
                    $newtextureid = $newtextureid->rowCount() + 1;
                    
                    if ($base64emblem && $newtextureid > 0) {
                        $x = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`, `Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,22,:aname,'Group Emblem',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),:oid,:aid2,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,:hash)");
                        $x->bindParam(":aid", $newtextureid, PDO::PARAM_INT);
                        $x->bindParam(":aname", $emblemname, PDO::PARAM_STR);
                        $x->bindParam(":oid", $localplayer, PDO::PARAM_INT);
                        $x->bindParam(":aid2", $newtextureid, PDO::PARAM_INT);
                        $x->bindParam(":hash", $emblemhash, PDO::PARAM_STR);
                        $x->execute();
                    }
                    
                    $GLOBALS['pdo']->exec("UNLOCK TABLES"); 
                } catch (Exception $e) {
                    $GLOBALS['pdo']->exec("UNLOCK TABLES"); //precaution
                    throw new Exception('Critical error occurred, please report this under #bugs');
                }
                
                $config = $GLOBALS['pdo']->prepare("UPDATE groups SET description = :description, manualapproval = :approval" . (!empty($base64emblem) && $newtextureid > 0 ? " ,emblem = ".$newtextureid."":"") . " WHERE id = :gid");
                $config->bindParam(":gid", $groupid, PDO::PARAM_INT);
                $config->bindParam(":description", $description, PDO::PARAM_STR);
                $config->bindParam(":approval", $approval, PDO::PARAM_INT);
                if ($config->execute()) {
                    return true;
                }
                return false;
            }
        }

        public static function UpdateRole(int $groupid, int $rank, int $newrank, string $name, bool $accessgroupwall, bool $postgroupwall, bool $deletegroupwallposts, bool $postgroupshout, bool $managelowerranks, bool $kicklowerranks, bool $acceptjoinrequests, bool $auditaccess)
        {
            $localplayer = $GLOBALS['user']->id;

            if (!$groupid || !$rank || !$newrank) {
                throw new Exception('Missing parameters');
            } else if (!Group::IsOwner($localplayer, $groupid)) {
                throw new Exception('You do not have permission to perform this action');
            } else if ($newrank < 0 || $newrank > 254) {
                throw new Exception('Rank must be above 0 and below 255');
            } else if (Group::RankExists($groupid, $newrank) && $rank != $newrank) {
                throw new Exception('Rank '.$newrank.' already exists');
            } else if (strlen($name) > 30) {
                throw new Exception('Role name is too long');
            } else if (strlen($name) < 3) {
                throw new Exception('Role name is too short');
            } else {   
                $updaterole = $GLOBALS['pdo']->prepare("UPDATE group_roles SET rolename = :rolename, rank = :newrank, AccessGroupWall = :groupwallaccess, PostGroupWall = :postgroupwall, DeleteGroupWallPosts = :deletegroupwallposts, PostGroupShout = :postgroupshout, ManageLowerRanks = :managelowerranks, KickLowerRanks = :kicklowerranks, AcceptJoinRequests = :acceptjoinrequest, ViewAuditLog = :viewauditlog WHERE groupid = :gid AND rank = :rank");
                $updaterole->bindParam(":rolename", $name, PDO::PARAM_STR);
                $updaterole->bindParam(":newrank", $newrank, PDO::PARAM_INT);
                $updaterole->bindParam(":groupwallaccess", $accessgroupwall, PDO::PARAM_INT);
                $updaterole->bindParam(":postgroupwall", $postgroupwall, PDO::PARAM_INT);
                $updaterole->bindParam(":deletegroupwallposts", $deletegroupwallposts, PDO::PARAM_INT);
                $updaterole->bindParam(":postgroupshout", $postgroupshout, PDO::PARAM_INT);
                $updaterole->bindParam(":managelowerranks", $managelowerranks, PDO::PARAM_INT);
                $updaterole->bindParam(":kicklowerranks", $kicklowerranks, PDO::PARAM_INT);
                $updaterole->bindParam(":acceptjoinrequest", $acceptjoinrequests, PDO::PARAM_INT);
                $updaterole->bindParam(":viewauditlog", $auditaccess, PDO::PARAM_INT);
                $updaterole->bindParam(":gid", $groupid, PDO::PARAM_INT);
                $updaterole->bindParam(":rank", $rank, PDO::PARAM_INT);
                if ($updaterole->execute()) {
                    return true;
                }
                return false;
            }
        }
    }
}