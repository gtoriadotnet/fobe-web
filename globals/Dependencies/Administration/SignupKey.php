<?php

namespace Alphaland\Administration {

    use Alphaland\Common\HashingUtiltity;
    use PDO;

    class SignupKey
    {
        public static function GenerateSignupKey()
        {
            $hash = "";
            do {
                $hash = HashingUtiltity::GenerateByteHash(16);
                $tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM signup_keys WHERE signupkey = :t");
                $tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
                $tokencheck->execute();
            } while ($tokencheck->fetchColumn() != 0);

            $n = $GLOBALS['pdo']->prepare("INSERT INTO signup_keys(signupkey, whenGenerated) VALUES(:t, UNIX_TIMESTAMP())");
            $n->bindParam(":t", $hash, PDO::PARAM_STR);
            $n->execute();
            return $hash;
        }

        public static function ValidateSignupKey(string $key) 
        {
            $n = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM signup_keys WHERE signupkey = :t");
            $n->bindParam(":t", $key, PDO::PARAM_STR);
            $n->execute();
            
            if ($n->fetchColumn() > 0) {
                $invalidate = $GLOBALS['pdo']->prepare("DELETE FROM signup_keys WHERE signupkey = :t");
                $invalidate->bindParam(":t", $key, PDO::PARAM_STR);
                $invalidate->execute();
                if ($invalidate->rowCount() > 0) {
                    return true;
                }     
            }
            return false;
        }
    }
}
