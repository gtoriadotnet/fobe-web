<?php

namespace Alphaland\Web {

    use Alphaland\Users\User;
    use PDO;

    class WebContextManager
    {
        public static function GetCurrentIPAddress(): string
        {
            return (isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER['REMOTE_ADDR']);
        }

        public static function IsUnderMaintenance(): bool
        {
            $query = $GLOBALS['pdo']->prepare("SELECT * FROM `websettings` WHERE `maintenance` = 1");
            $query->execute();

            if ($query->rowCount() > 0)
            {
                return true;
            }
            return false;
        }

        public static function CanBypassMaintenance()
        {
            // Wouldn't really be a bypass per say, but you know, reusing existing code is better than
            // copying already existing code.
            if (!WebContextManager::IsUnderMaintenance()) return true;

            if (
                !WebContextManager::$CurrentUser->IsAdministrator()
                && !WebContextManager::IsCurrentIpAddressWhitelisted()
            ) return false;

            return true;
        }

        public static function IsCurrentIpAddressWhitelisted()
        {
            $currentIp = WebContextManager::GetCurrentIPAddress();
            $ipWhitelist = []; // query from db

            return in_array($currentIp, $ipWhitelist);
        }

        public static $CurrentUser = new User();
    }
}
