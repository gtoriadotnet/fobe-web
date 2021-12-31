<?php

// WARNING: This is deprecated over html_clientsettings/v1/GetSetting, please implement this new route into any clients
// as this endpoint will be removed in the future.

header('Content-Type: application/json');
header("Cache-Control: no-cache, no-store");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");

$ws = $pdo->query("SELECT * FROM websettings WHERE id = 1")->fetch(PDO::FETCH_OBJ);

echo $ws->ClientAppSettings;