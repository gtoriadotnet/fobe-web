<?php

namespace Finobe\Common {
    use PDO;
    class HashingUtiltity
    {
        public static function GenerateByteHash(int $length): string
        {
            return bin2hex(openssl_random_pseudo_bytes($length));
        }

        public static function GenRandomAssetHash(int $len): string
        {
            $hash = "";
            do {
                $hash = HashingUtiltity::GenerateByteHash($len);   
                $tokencheck = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM assets WHERE Hash = :t");
                $tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
                $tokencheck->execute();
                } while ($tokencheck->fetchColumn() != 0);
            return $hash;
        }

        public static function VerifyMD5(string $md5)
        {
            $hashcheck = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM assets WHERE Hash = :t");
            $hashcheck->bindParam(":t", $md5, PDO::PARAM_STR);
            $hashcheck->execute();
            if ($hashcheck->fetchColumn() != 0) {
                $md5 = HashingUtiltity::GenRandomAssetHash(16); //fallback to random gen hash (this sshouldnt happen often)
            }
            return $md5;
        }
    }
}
