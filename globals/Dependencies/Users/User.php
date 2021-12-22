<?php

namespace Alphaland\Users {

    use PDO;

    class User
    {
        public static function ValidatePassword(int $userid, string $password) 
        {
            $userpassword = $GLOBALS['pdo']->prepare("SELECT pwd FROM users WHERE id = :i");
            $userpassword->bindParam(":i", $userid, PDO::PARAM_INT);
            $userpassword->execute();
            if($userpassword->rowCount() > 0) {
                if(password_verify($password, $userpassword->fetch(PDO::FETCH_OBJ)->pwd)) {
                    return true; //correct
                }
            }
            return false;
        }
    }
}