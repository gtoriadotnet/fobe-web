<?php

use Alphaland\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

$firstuser = $_GET['firstUserId'];
$seconduser = $_GET['secondUserId'];

BreakFriend($firstuser, $seconduser);