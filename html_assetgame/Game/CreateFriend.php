<?php

use Fobe\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

$firstuser = $_GET['firstUserId'];
$seconduser = $_GET['secondUserId'];

CreateFriend($firstuser, $seconduser);