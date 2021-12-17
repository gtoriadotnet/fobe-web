<?php

use Alphaland\Web\WebContextManager;

$requesttype = $_GET['request'];
$placeid = $_GET['placeId'];
$userid = $_GET['userid'];
$isTeleport = $_GET['isTeleport'];

WebContextManager::Redirect("https://www.alphaland.cc/Game/PlaceLauncher?request=" . $requesttype . "&placeId=" . $placeid . "&isTeleport=" . $isTeleport);