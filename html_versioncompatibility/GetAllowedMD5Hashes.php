<?php

use Alphaland\Web\WebContextManager;
use Alphaland\Web\WebsiteSettings;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

header('Content-Type: application/json');

echo json_encode(array(
    "data" => array(
        WebsiteSettings::GetSetting("md5_hash"),
    )
), JSON_UNESCAPED_SLASHES);
