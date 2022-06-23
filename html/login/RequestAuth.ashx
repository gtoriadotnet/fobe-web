<?php

//header("Cache-Control: no-cache, no-store");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");

echo "https://www.idk16.xyz/Game/Negotiate?suggest=" . genTicket();