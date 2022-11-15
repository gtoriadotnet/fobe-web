<?php

header('Content-Type: application/json');
header("Cache-Control: no-cache, no-store");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");

echo json_encode(array(
    "ThreadPoolConfig" => "Threads16"
), JSON_UNESCAPED_SLASHES);