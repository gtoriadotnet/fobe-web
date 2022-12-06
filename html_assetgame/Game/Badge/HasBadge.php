<?php

use Fobe\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

$userid = $_GET['UserID'];
$badgeid = $_GET['BadgeID'];

if (hasUserBadge($userid, $badgeid))
{
    echo "Success";
}

