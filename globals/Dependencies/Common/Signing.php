<?php

namespace Finobe\Common {
    
    class Signing
    {
        public static function SignData(string $data, bool $rbxsig=true)
        {
            $sig = "";
            $key = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap(file_get_contents($GLOBALS['privateKeyPath']), 64, "\n",true) . "\n-----END RSA PRIVATE KEY-----\n";
            openssl_sign($data, $sig, $key, OPENSSL_ALGO_SHA1);

            if ($rbxsig) {
                return "--rbxsig%" . base64_encode($sig) . "%" . $data;
            }
            return base64_encode($sig);
        }
    }
}
