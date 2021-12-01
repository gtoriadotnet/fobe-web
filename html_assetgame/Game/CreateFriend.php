<?php

use Alphaland\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(400));
}

$firstuser = $_GET['firstUserId'];
$seconduser = $_GET['secondUserId'];

CreateFriend($firstuser, $seconduser);