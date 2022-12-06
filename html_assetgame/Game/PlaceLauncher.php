<?php

use Fobe\Web\WebContextManager;

$requesttype = $_GET['request'];
$placeid = $_GET['placeId'];
$userid = $_GET['userid'];
$isTeleport = $_GET['isTeleport'];

WebContextManager::Redirect("https://www.idk16.xyz/Game/PlaceLauncher?request=" . $requesttype . "&placeId=" . $placeid . "&isTeleport=" . $isTeleport);