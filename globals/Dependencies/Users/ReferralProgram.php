<?php

/*
    Alphaland 2021
*/

namespace Alphaland\Users {

    use Alphaland\Moderation\UserModerationManager;
    use Alphaland\Common\HashingUtiltity;
    use PDO;

    class ReferralProgram
    {
        public static function IsMember(int $userid)
        {
            /*
            if (isInGroup($userid, 22)) //id 22 is the official referral program group
            {
                return true;
            }
            return false;
            */
            return true;
        }

        public static function IsUserGeneratedKey(string $key)
        {
            $check = $GLOBALS['pdo']->prepare("SELECT * FROM user_signup_keys WHERE signupkey = :ke");
            $check->bindParam(":ke", $key, PDO::PARAM_STR);
            $check->execute();
            if ($check->rowCount() > 0) {
                if (!UserModerationManager::IsBanned($check->fetch(PDO::FETCH_OBJ)->userGen)) {
                    return true;
                }
            }
            return false;
        }

        private static function GenerateKey($len)
        {
            $hash = "";
            do {
                $hash = HashingUtiltity::GenerateByteHash($len);
            } while (ReferralProgram::IsUserGeneratedKey($hash));
            return $hash;
        }

        public static function DeleteUserKey(string $key)
        {
            $userkey = $GLOBALS['pdo']->prepare("DELETE FROM user_signup_keys WHERE signupkey = :ke");
            $userkey->bindParam(":ke", $key, PDO::PARAM_STR);
            $userkey->execute();
            return $userkey->rowCount() > 0;
        }

        public static function DeleteAllKeys(int $user)
        {
            $keys = $GLOBALS['pdo']->prepare("DELETE FROM user_signup_keys WHERE userGen = :u");
            $keys->bindParam(":u", $user, PDO::PARAM_INT);
            $keys->execute();
        }

        public static function UserKeysCount(int $user)
        {
            $keys = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM user_signup_keys WHERE userGen = :u");
            $keys->bindParam(":u", $user, PDO::PARAM_INT);
            $keys->execute();
            return $keys->fetchColumn();
        }

        public static function NextRenewal(int $user)
        {
            return userInfo($user)->referralNextRenewal;
        }

        public static function IsRenewable(int $user)
        {
            return time() >= ReferralProgram::NextRenewal($user); //returns true if the current timestamp is greater or equal than the scheduled renewal
        }

        public static function UpdateNextRenewal(int $user)
        {
            $updateuser = $GLOBALS['pdo']->prepare('UPDATE users SET referralNextRenewal = (UNIX_TIMESTAMP() + 604800) WHERE id = :userid');
            $updateuser->bindParam(":userid", $user, PDO::PARAM_INT);
            $updateuser->execute();
        }

        public static function CreateKey(int $user)
        {
            $newkey = ReferralProgram::GenerateKey(32);
            $n = $GLOBALS['pdo']->prepare("INSERT INTO user_signup_keys(userGen,signupkey,whenGenerated) VALUES(:user,:key,UNIX_TIMESTAMP())");
            $n->bindParam(":user", $user, PDO::PARAM_INT);
            $n->bindParam(":key", $newkey, PDO::PARAM_STR);
            $n->execute();
            return $newkey;
        }

        public static function CheckUserKeys(int $user)
        {
            if (ReferralProgram::IsMember($user)) {
                if (ReferralProgram::IsRenewable($user))
                {
                    //step 1, update the next renewal time
                    ReferralProgram::UpdateNextRenewal($user);

                    //step 2, delete all the current keys
                    ReferralProgram::DeleteAllKeys($user);

                    //step 3, generate two keys
                    ReferralProgram::CreateKey($user);
                    ReferralProgram::CreateKey($user);

                    return true;
                }
            }
            return false;
        }

        public static function ConfirmSignup(int $newuser, string $key)
        {
            $userkey = $GLOBALS['pdo']->prepare("SELECT * FROM user_signup_keys WHERE signupkey = :ke");
            $userkey->bindParam(":ke", $key, PDO::PARAM_STR);
            $userkey->execute();
            if ($userkey->rowCount() > 0) {
                $whoinvited = $userkey->fetch(PDO::FETCH_OBJ)->userGen;
                $n = $GLOBALS['pdo']->prepare("INSERT INTO users_invited(invitedUser,whoInvited,whenAccepted) VALUES(:inviteduser,:whoinvited,UNIX_TIMESTAMP())");
                $n->bindParam(":inviteduser", $newuser, PDO::PARAM_INT);
                $n->bindParam(":whoinvited", $whoinvited, PDO::PARAM_INT);
                $n->execute();

                if (ReferralProgram::DeleteUserKey($key)){
                    return true;
                }
            }
            return false;
        }
    }
}