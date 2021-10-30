<?php

$filename = $_GET['filename'];
$filetype = $_GET['filetype'];
$content = gzdecode(file_get_contents('php://input')); //content uploaded

//file_put_contents("uploads/" . $filename . "-" . date("m-d-Y", time()) . "-" . genHash(6) . "." . $filetype, $content);