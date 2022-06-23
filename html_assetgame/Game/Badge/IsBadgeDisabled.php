<?php

use Finobe\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

$badgeid = $_GET['BadgeID'];
$placeid = $_GET['PlaceID'];

