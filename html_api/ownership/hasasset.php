<?php

use Finobe\Users\User;

header('Content-Type: application/json');

$userid = $_GET['userId'];
$assetId = $_GET['assetId'];

if (User::OwnsAsset($userid, $assetId) || isOwner($assetId, $userid))
{
    echo(json_encode(true));
}
else
{
    echo(json_encode(false));
}