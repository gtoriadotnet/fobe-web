<?php

/*
    Alphaland 2021
*/

// Astro, please make public members start with capital letters

namespace Alphaland\Users {

    use PDO;

    class TwoFactor
    {
        public static function safeGenerate2FASecret()
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

        public static function deauth2FAUserSession()
        {
            $session = $GLOBALS['user']->sessionCookieID;
            $check = $GLOBALS['pdo']->prepare("UPDATE `sessions` SET `twoFactorUnlocked` = 0 WHERE `id` = :session");
            $check->bindParam(":session", $session, PDO::PARAM_INT);
            if ($check->execute()) {
                return true;
            }
            return false;
        }

        public static function deleteUser2FA($userid)
        {
            $del = $GLOBALS['pdo']->prepare("DELETE FROM `google_2fa` WHERE `userid` = :uid");
            $del->bindParam(":uid", $userid, PDO::PARAM_INT);
            $del->execute();
            if ($del->rowCount() > 0) {
                TwoFactor::deauth2FAUserSession();
                return true;
            }
            return false;
        }

        public static function getUser2FASecret($userid)
        {
            $code = $GLOBALS['pdo']->prepare("SELECT * FROM `google_2fa` WHERE `userid` = :uid");
            $code->bindParam(":uid", $userid, PDO::PARAM_INT);
            $code->execute();
            if ($code->rowCount() > 0) {
                return $code->fetch(PDO::FETCH_OBJ)->secret;
            }
        }

        public static function verify2FACode($userid, $code)
        {
            $secret = TwoFactor::getUser2FASecret($userid);
            if ($secret) {
                if ($GLOBALS['authenticator']->verifyCode($secret, $code, 0)) {
                    return true;
                }
            }
            return false;
        }

        public static function is2FAInitialized($userid)
        {
            $isinit = $GLOBALS['pdo']->prepare("SELECT * FROM `google_2fa` WHERE `validated` = 1 AND `userid` = :uid");
            $isinit->bindParam(":uid", $userid, PDO::PARAM_INT);
            $isinit->execute();
            if ($isinit->rowCount() > 0) {
                return true;
            }
            return false;
        }

        public static function auth2FAUserSession()
        {
            $session = $GLOBALS['user']->sessionCookieID;
            $check = $GLOBALS['pdo']->prepare("UPDATE `sessions` SET `twoFactorUnlocked` = 1 WHERE `id` = :session");
            $check->bindParam(":session", $session, PDO::PARAM_INT);
            if ($check->execute()) {
                return true;
            }
            return false;
        }

        public static function activateUser2FA($userid, $code) //after initializing we make sure it works with a first time activation code
        {
            if(!TwoFactor::is2FAInitialized($userid) && 
            TwoFactor::verify2FACode($userid, $code)) {
                $check = $GLOBALS['pdo']->prepare("UPDATE `google_2fa` SET `validated` = 1 WHERE `userid` = :uid");
                $check->bindParam(":uid", $userid, PDO::PARAM_INT);
                if ($check->execute()) {
                    TwoFactor::auth2FAUserSession();
                    return true;
                }
            }
            return false;
        }
            
        public static function initialize2FA($userid)
        {
            $check = $GLOBALS['pdo']->prepare("SELECT * FROM `google_2fa` WHERE `userid` = :uid");
            $check->bindParam(":uid", $userid, PDO::PARAM_INT);
            $check->execute();
            if ($check->rowCount() == 0) {
                $username = getUsername($userid);
                if ($username) {
                    $secret = TwoFactor::safeGenerate2FASecret();
                    $qrcode = $GLOBALS['authenticator']->getQRCodeGoogleUrl($username, $secret, "Alphaland");
                    $new2fa = $GLOBALS['pdo']->prepare("INSERT INTO `google_2fa`(`userid`, `secret`, `qr`, `whenGenerated`) VALUES (:uid, :secret, :qr, UNIX_TIMESTAMP())");
                    $new2fa->bindParam(":uid", $userid, PDO::PARAM_INT);
                    $new2fa->bindParam(":secret", $secret, PDO::PARAM_STR);
                    $new2fa->bindParam(":qr", $qrcode, PDO::PARAM_STR);
                    $new2fa->execute();
                }
            }
        }

        public static function getUser2FAQR($userid)
        {
            $qrcode = $GLOBALS['pdo']->prepare("SELECT * FROM `google_2fa` WHERE `userid` = :uid");
            $qrcode->bindParam(":uid", $userid, PDO::PARAM_INT);
            $qrcode->execute();
            if ($qrcode->rowCount() > 0) {
                return $qrcode->fetch(PDO::FETCH_OBJ)->qr;
            }
        }

        public static function isSession2FAUnlocked()
        {
            $localuser = $GLOBALS['user']->id;
            $session = $GLOBALS['user']->sessionCookieID;
            $check = $GLOBALS['pdo']->prepare("SELECT * FROM `sessions` WHERE `twoFactorUnlocked` = 1 AND `id` = :session");
            $check->bindParam(":session", $session, PDO::PARAM_INT);
            $check->execute();
            if ($check->rowCount() > 0 || !TwoFactor::is2FAInitialized($localuser)) {
                return true;
            }
            return false;
        }

        public static function attemptSession2FAUnlock($code)
        {
            $localuser = $GLOBALS['user']->id;
            if (!TwoFactor::isSession2FAUnlocked()) {
                if (TwoFactor::verify2FACode($localuser, $code)) {
                    TwoFactor::auth2FAUserSession();
                    return true;
                }
            }
            return false;
        }
    }
}