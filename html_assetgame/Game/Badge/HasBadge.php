<?php

RCCHeaderEnvironment();

$userid = $_GET['UserID'];
$badgeid = $_GET['BadgeID'];

if (hasUserBadge($userid, $badgeid))
{
    echo "Success";
}

