<?php

RCCHeaderEnvironment();

$firstuser = $_GET['firstUserId'];
$seconduser = $_GET['secondUserId'];

CreateFriend($firstuser, $seconduser);