<?php

/*
    Alphaland 2021
*/

// Astro, please make public members start with capital letters
// Also where you aren't actually fetching data, please make it do a COUNT(*)

namespace Alphaland\Users {

    use PDO;

    class TwoFactor
    {
        private static function SafeGenerate2FASecret()
        {
            $secret = "";
            do {
                $secret = $GLOBALS['authenticator']->createSecret();
                $keycheck = $GLOBALS['pdo']->prepare("SELECT * FROM `google_2fa` WHERE `secret` = :ac");
                $keycheck->bindParam(":ac", $secret, PDO::PARAM_STR);
                $keycheck->execute();
            } while ($keycheck->rowCount() != 0);
            return $secret;
        }

        public static function Deauth2FAUserSession()
        {
            $session = $GLOBALS['user']->sessionCookieID;
            $check = $GLOBALS['pdo']->prepare("UPDATE `sessions` SET `twoFactorUnlocked` = 0 WHERE `id` = :session");
            $check->bindParam(":session", $session, PDO::PARAM_INT);
            if ($check->execute()) {
                return true;
            }
            return false;
        }

        public static function DeleteUser2FA(int $userid)
        {
            $del = $GLOBALS['pdo']->prepare("DELETE FROM `google_2fa` WHERE `userid` = :uid");
            $del->bindParam(":uid", $userid, PDO::PARAM_INT);
            $del->execute();
            if ($del->rowCount() > 0) {
                TwoFactor::Deauth2FAUserSession();
                return true;
            }
            return false;
        }

        public static function GetUser2FASecret(int $userid)
        {
            $code = $GLOBALS['pdo']->prepare("SELECT * FROM `google_2fa` WHERE `userid` = :uid");
            $code->bindParam(":uid", $userid, PDO::PARAM_INT);
            $code->execute();
            if ($code->rowCount() > 0) {
                return $code->fetch(PDO::FETCH_OBJ)->secret;
            }
        }

        public static function Verify2FACode(int $userid, string $code)
        {
            $secret = TwoFactor::GetUser2FASecret($userid);
            if ($secret) {
                if ($GLOBALS['authenticator']->verifyCode($secret, $code, 0)) {
                    return true;
                }
            }
            return false;
        }

        public static function Is2FAInitialized(int $userid)
        {
            $isinit = $GLOBALS['pdo']->prepare("SELECT * FROM `google_2fa` WHERE `validated` = 1 AND `userid` = :uid");
            $isinit->bindParam(":uid", $userid, PDO::PARAM_INT);
            $isinit->execute();
            if ($isinit->rowCount() > 0) {
                return true;
            }
            return false;
        }

        public static function Auth2FAUserSession()
        {
            $session = $GLOBALS['user']->sessionCookieID;
            $check = $GLOBALS['pdo']->prepare("UPDATE `sessions` SET `twoFactorUnlocked` = 1 WHERE `id` = :session");
            $check->bindParam(":session", $session, PDO::PARAM_INT);
            if ($check->execute()) {
                return true;
            }
            return false;
        }

        public static function ActivateUser2FA(int $userid, string $code) //after initializing we make sure it works with a first time activation code
        {
            if(!TwoFactor::Is2FAInitialized($userid) && TwoFactor::Verify2FACode($userid, $code)) {
                $check = $GLOBALS['pdo']->prepare("UPDATE `google_2fa` SET `validated` = 1 WHERE `userid` = :uid");
                $check->bindParam(":uid", $userid, PDO::PARAM_INT);
                if ($check->execute()) {
                    TwoFactor::Auth2FAUserSession();
                    return true;
                }
            }
            return false;
        }
            
        public static function Initialize2FA(int $userid)
        {
            $check = $GLOBALS['pdo']->prepare("SELECT * FROM `google_2fa` WHERE `userid` = :uid");
            $check->bindParam(":uid", $userid, PDO::PARAM_INT);
            $check->execute();
            if ($check->rowCount() == 0) {
                $username = getUsername($userid);
                if ($username) {
                    $secret = TwoFactor::SafeGenerate2FASecret();
                    $qrcode = $GLOBALS['authenticator']->getQRCodeGoogleUrl($username, $secret, "Alphaland");
                    $new2fa = $GLOBALS['pdo']->prepare("INSERT INTO `google_2fa`(`userid`, `secret`, `qr`, `whenGenerated`) VALUES (:uid, :secret, :qr, UNIX_TIMESTAMP())");
                    $new2fa->bindParam(":uid", $userid, PDO::PARAM_INT);
                    $new2fa->bindParam(":secret", $secret, PDO::PARAM_STR);
                    $new2fa->bindParam(":qr", $qrcode, PDO::PARAM_STR);
                    $new2fa->execute();
                }
            }
        }

        public static function GetUser2FAQR(int $userid)
        {
            $qrcode = $GLOBALS['pdo']->prepare("SELECT * FROM `google_2fa` WHERE `userid` = :uid");
            $qrcode->bindParam(":uid", $userid, PDO::PARAM_INT);
            $qrcode->execute();
            if ($qrcode->rowCount() > 0) {
                return $qrcode->fetch(PDO::FETCH_OBJ)->qr;
            }
        }

        public static function IsSession2FAUnlocked()
        {
            $localuser = $GLOBALS['user']->id;
            $session = $GLOBALS['user']->sessionCookieID;
            $check = $GLOBALS['pdo']->prepare("SELECT * FROM `sessions` WHERE `twoFactorUnlocked` = 1 AND `id` = :session");
            $check->bindParam(":session", $session, PDO::PARAM_INT);
            $check->execute();
            if ($check->rowCount() > 0 || !TwoFactor::Is2FAInitialized($localuser)) {
                return true;
            }
            return false;
        }

        public static function AttemptSession2FAUnlock(string $code)
        {
            $localuser = $GLOBALS['user']->id;
            if (!TwoFactor::IsSession2FAUnlocked()) {
                if (TwoFactor::Verify2FACode($localuser, $code)) {
                    if (TwoFactor::Auth2FAUserSession()) {
                        return true;
                    }
                }
            }
            return false;
        }
    }
}