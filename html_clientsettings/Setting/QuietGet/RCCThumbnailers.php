<?php

use Fobe\Web\WebContextManager;

header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");

if(!WebContextManager::VerifyAccessKeyHeader()) {
	http_response_code(404);
	exit;
}
?>
{"DFIntContentProviderThreadPoolSize": "512", "DFFlagContentProviderHttpCaching": "True", "DFFlagImageFailedToLoadContext": "False"}