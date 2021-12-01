<?php

namespace Alphaland\Common {
    class System
    {
        public static function IsCommandLine()
        {
            return php_sapi_name() === 'cli';
        }
    }
}