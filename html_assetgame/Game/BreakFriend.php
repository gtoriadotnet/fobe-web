<?php

RCCHeaderEnvironment();

$firstuser = $_GET['firstUserId'];
$seconduser = $_GET['secondUserId'];

BreakFriend($firstuser, $seconduser);