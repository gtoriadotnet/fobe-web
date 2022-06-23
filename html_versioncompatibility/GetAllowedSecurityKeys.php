<?php

use Finobe\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

header('Content-Type: application/json');

echo json_encode(array(
    "data" => array(
        $ws->security_version
    )
), JSON_UNESCAPED_SLASHES);