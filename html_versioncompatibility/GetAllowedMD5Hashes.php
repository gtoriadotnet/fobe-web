<?php

RCCHeaderEnvironment();

header('Content-Type: application/json');

echo json_encode(array(
    "data" => array(
        $ws->md5_hash
    )
), JSON_UNESCAPED_SLASHES);
