<?php

namespace Fobe\Common {
    class System
    {
        public static function IsCommandLine()
        {
            return php_sapi_name() === 'cli';
        }
    }
}