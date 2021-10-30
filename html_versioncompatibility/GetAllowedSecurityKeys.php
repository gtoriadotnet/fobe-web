<?php

RCCHeaderEnvironment();

header('Content-Type: application/json');

echo json_encode(array(
    "data" => array(
        $ws->security_version
    )
), JSON_UNESCAPED_SLASHES);