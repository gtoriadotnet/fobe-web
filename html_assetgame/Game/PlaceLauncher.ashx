<?php

$requesttype = $_GET['request'];
$placeid = $_GET['placeId'];
$userid = $_GET['userid'];
$isTeleport = $_GET['isTeleport'];

redirect("https://www.alphaland.cc/Game/PlaceLauncher.ashx?request=" . $requesttype . "&placeId=" . $placeid . "&isTeleport=" . $isTeleport);