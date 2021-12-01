<?php

use Alphaland\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(400));
}

header('Content-Type: application/json');

echo json_encode(array(
    "data" => array(
        $ws->security_version
    )
), JSON_UNESCAPED_SLASHES);