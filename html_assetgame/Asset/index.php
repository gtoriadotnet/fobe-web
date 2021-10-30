<?php

//we dont want duplicates of the asset fetching so we will just make this endpoint internally redirect

$id = (int)$_GET["id"];
$assetversionid = (int)$_GET["assetversionid"];
$version = (int)$_GET["version"];

$assetversion = 0;
if ($assetversionid)
{
    $assetversion = $assetversionid;
}
else if ($version)
{
    $assetversion = $version;
}

redirect("https://www.alphaland.cc/asset/?id=" . $id . "&version=" . $assetversion); 