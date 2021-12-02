<?php

use Alphaland\Web\WebContextManager;

$url = $_SERVER['REQUEST_URI'];

if (strpos($url, '/ratelimit') !== false || strpos($url, '/ratelimit.php') !== false) {
    WebContextManager::Redirect("/404"); //why not
}

echo "Rate limit exceeded, slow down!";