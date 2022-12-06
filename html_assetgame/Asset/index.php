<?php

//we dont want duplicates of the asset fetching so we will just make this endpoint internally redirect

use Fobe\Web\WebContextManager;

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

WebContextManager::Redirect("https://www.idk16.xyz/asset/?id=" . $id . "&version=" . $assetversion); 