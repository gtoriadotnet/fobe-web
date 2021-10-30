<?php

header('Content-Type: application/json');

$userid = $_GET['userId'];
$assetId = $_GET['assetId'];

if (playerOwnsAsset($assetId, $userid) || isOwner($assetId, $userid))
{
    echo(json_encode(true));
}
else
{
    echo(json_encode(false));
}