<?php

/*
    Fobe 2021
*/

namespace Fobe\Common {

    use PDO;

    class Email
    {
        public static function ObfuscateEmail(string $email)
        {
            $em = explode("@",$email);
            $name = implode('@', array_slice($em, 0, count($em)-1));
            $len = floor(strlen($name)/2);     
            return substr($name,0, $len) . str_repeat('.', $len) . "@" . end($em);   
        }
        
        public static function GenerateVerificationEmailHash(int $len)
        {
            $hash = "";
            do {
                $hash = HashingUtiltity::GenerateByteHash($len);    
                $tokencheck = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM verify_email_keys WHERE token = :t");
                $tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
                $tokencheck->execute();
            } while ($tokencheck->fetchColumn() != 0);
            return $hash;
        }

        public static function GenerateResetPasswordHash(int $len)
        {
            $hash = "";
            do {
                $hash = HashingUtiltity::GenerateByteHash($len); 
                $tokencheck = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM password_reset_keys WHERE token = :t");
                $tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
                $tokencheck->execute();
            } while ($tokencheck->fetchColumn() != 0);
            return $hash;
        }

        public static function IsEmailRegistered(string $email)
        {
            $check = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM users WHERE email = :e");
            $check->bindParam(":e", $email, PDO::PARAM_STR);
            $check->execute();
            if ($check->fetchColumn() > 0) {
                return true;
            }
            return false;
        }
    }
}