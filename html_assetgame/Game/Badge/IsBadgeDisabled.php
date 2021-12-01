<?php

use Alphaland\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(400));
}

$badgeid = $_GET['BadgeID'];
$placeid = $_GET['PlaceID'];

