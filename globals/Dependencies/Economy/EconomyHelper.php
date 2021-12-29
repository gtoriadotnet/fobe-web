<?php

/*
    Alphaland 2021
*/

namespace Alphaland\Economy {

    use PDO;
    use Alphaland\Users\User;

    class EconomyHelper
    {
        public static function LogTransaction(int $amount, int $userid, string $description)
        {
            $log = $GLOBALS['pdo']->prepare("INSERT INTO transaction_logs (info, amount, userid, whenTransaction) VALUES (:info, :amount, :userid, UNIX_TIMESTAMP())");
            $log->bindParam(":info", $description, PDO::PARAM_STR);
            $log->bindParam(":amount", $amount, PDO::PARAM_INT);
            $log->bindParam(":userid", $userid, PDO::PARAM_INT);
            if ($log->execute()) {
                return true;
            }
            return false;
        }

        public static function HasEnoughAlphabux(int $amount, int $userid)
        {
            if (User::GetUserInfo($userid)->currency >= $amount) {
                return true;
            }
            return false;
        }

        public static function GiveAlphabux(int $amount, int $userid, $description="")
        {
            if (EconomyHelper::LogTransaction($amount, $userid, $description)) {
                $check = $GLOBALS['pdo']->prepare("UPDATE users SET currency = (currency + :u) WHERE id = :i");
                $check->bindParam(":i", $userid, PDO::PARAM_INT);
                $check->bindParam(":u", $amount, PDO::PARAM_INT);
                $check->execute();
                if ($check->rowCount() > 0) {
                    return true;
                }
            }
            return false;
        }

        public static function RemoveAlphabux(int $amount, int $userid, string $description="")
        {
            if (EconomyHelper::HasEnoughAlphabux($amount, $userid)) {
                if (EconomyHelper::LogTransaction($amount, $userid, $description)) {
                    $check = $GLOBALS['pdo']->prepare("UPDATE users SET currency = (currency - :u) WHERE id = :i");
                    $check->bindParam(":i", $userid, PDO::PARAM_INT);
                    $check->bindParam(":u", $amount, PDO::PARAM_INT);
                    $check->execute();
                    if ($check->rowCount() > 0) {
                        return true;
                    }
                }
            }
            return false;
        }
    }
}
