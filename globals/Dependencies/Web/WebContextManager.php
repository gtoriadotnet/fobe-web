<?php

namespace Alphaland\Web {
    
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

        public static function IsCurrentIpAddressWhitelisted()
        {
            $currentIp = WebContextManager::GetCurrentIPAddress();
            $ipWhitelist = explode(";", $GLOBALS['ws']->webservice_whitelist);

            return in_array($currentIp, $ipWhitelist);
        }

        public static function CanBypassMaintenance()
        {
            // Wouldn't really be a bypass per say, but you know, reusing existing code is better than
            // copying already existing code.
            if (!WebContextManager::IsUnderMaintenance()) return true;

            if (!$GLOBALS['user']->isAdmin()
                && !WebContextManager::IsCurrentIpAddressWhitelisted()
            ) return false;

            return true;
        }

        public static function GetRequestHeaders() 
        {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            return $headers;
        }

        public static function VerifyAccessKeyHeader()
        {   
            $headers = WebContextManager::GetRequestHeaders();
            $accesskey = $headers['Accesskey'];
        
            if (!empty($accesskey))
            {
                if(WebContextManager::IsCurrentIpAddressWhitelisted())
                {
                    if($accesskey == $GLOBALS['ws']->webservice_key)
                    {
                        return true;
                    }
                }
            }
            return false;
        }

        public static function IsCloudflareHttps() 
        {
            return isset($_SERVER['HTTPS']) ||
                ($visitor = json_decode($_SERVER['HTTP_CF_VISITOR'])) &&
                    $visitor->scheme == 'https';
        }
        
        public static function ForceHttpsCloudflare() 
        {
            if(!is_https_cloudflare()) {
                header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
                exit();
            }
        }

        public static function HttpGetPing($url, $timeout) //to see if a URL times out
        {
            $curl_do = curl_init(); 
            curl_setopt($curl_do, CURLOPT_URL,          	      $url);   
            curl_setopt($curl_do, CURLOPT_RETURNTRANSFER,         true);
            curl_setopt($curl_do, CURLOPT_CONNECTTIMEOUT_MS,$timeout); 
            curl_setopt($curl_do, CURLOPT_TIMEOUT_MS,       $timeout); 
            curl_setopt($curl_do, CURLOPT_SSL_VERIFYPEER,        false);  
            curl_setopt($curl_do, CURLOPT_SSL_VERIFYHOST,        false); 
            curl_setopt($curl_do, CURLOPT_POST,                  false ); 
            curl_setopt($curl_do, CURLOPT_HEADER, 1);
            
            $result = curl_exec($curl_do);
            
            curl_close($curl_do);
            
            if ($result) {
                return true;
            }
            return false;
        }
    }
}
