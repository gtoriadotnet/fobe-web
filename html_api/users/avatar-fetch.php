<?php

header('Content-Type: application/json');
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");

$id = (int)$_GET['userId'];

$accessories = array();

$resolvedavatar = "R6";

$get = $pdo->prepare("SELECT * FROM wearing_items WHERE uid = :u ORDER BY `id` DESC");
$get->bindParam(":u", $id, PDO::PARAM_INT);
$get->execute();
if($get->rowCount() > 0) {
    $items = $get->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($items as $item) {
        array_push($accessories, $item['aid']);
    }
}

$joinparams = json_encode(array(
    "resolvedAvatarType" => $resolvedavatar,
    "accessoryIds" => $accessories,
    "equippedGearIds" => [],
    "backpackGearIds" => [],
    "animations" => [],
    "scales" => array(
        "Width" => 5,
        "Height" => 5,
        "Head" => 5,
        "Depth" => 5,
        "Proportion" => 0,
        "BodyType" => 0
    ),
    "bodyColorsUrl" => "https://api.idk16.xyz/users/bodycolors?userId=".$id."&tick=" . time()
), JSON_UNESCAPED_SLASHES);

echo $joinparams;