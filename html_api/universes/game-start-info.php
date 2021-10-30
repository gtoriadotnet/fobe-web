<?php
header('Content-Type: application/json');
echo json_encode(array(
    "r15Morphing" => true
), JSON_UNESCAPED_SLASHES);