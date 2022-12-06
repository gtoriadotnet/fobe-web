<?php

use Fobe\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

$userid = $_GET['UserID'];
$badgeid = $_GET['BadgeID'];
$placeid = $_GET['PlaceID'];

if (rewardUserBadge($userid, $badgeid, $placeid))
{
    echo getUserBadgeInfo($badgeid)->Name;
}
else
{
    echo 0;
}