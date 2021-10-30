<?php

RCCHeaderEnvironment();

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