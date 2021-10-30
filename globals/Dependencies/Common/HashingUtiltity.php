<?php

namespace Alphaland\Common {
    class HashingUtiltity
    {
        public static function GenerateByteHash(int $length): string
        {
            return bin2hex(openssl_random_pseudo_bytes($length));
        }
    }
}
