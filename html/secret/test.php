<?php

if(!($user->isOwner())) {
	die();
}

/*
include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Users/Activation.php";

$test = new Alphaland\Users\Activation();


if ($test->isUserActivated(2))//
{
	echo "isactivated";
}
else
{
	echo "notactivated";
}


echo $test->generateActivationCode();
*/






//$render = json_decode($result->BatchJobExResult->LuaValue[0]->value); //returned by rcc]
//file_put_contents("astro.obj", base64_decode($render->files->{'scene.obj'}->content));

//Badge maker

//createGenericAsset($assetid, $assettypeid, $targetid, $producttype, $name, $description, $creatorid, $price, $onsale, $ispublicdomain, $isapproved, $hash)

/*
$filename = "test.png";
$width = 420;
$height = 420;

//upload parameters
$thumbnailuploadDirectory = $_SERVER['DOCUMENT_ROOT'] . "/../html_thumbs_cdn/"; //directory where the textures are stored
$thumbnailHash = genAssetHash(16);

$GLOBALS['pdo']->exec("LOCK TABLES assets WRITE"); //lock since this stuff is sensitive
				
$b = $GLOBALS['pdo']->prepare("SELECT * FROM assets");
$b->execute();
																	
//grab auto increment values
$autoincrement = $b->rowCount() + 1; //initial auto increment value

//add texture to assets
createGenericAsset($autoincrement, 1, $autoincrement, "", $assetname, "", $user->id, 0, 0, 1, 0, $thumbnailHash);

$GLOBALS['pdo']->exec("UNLOCK TABLES"); 

$name = "Test Badge";
$description = "";
$badgeawardingplaceid = 8;
$f = $GLOBALS['pdo']->prepare('INSERT INTO badges(Name, Description, BadgeImageAssetID, AwardingPlaceID, Created) VALUES (:name, :description, :badgeimageassetid, :awardingplaceid, UNIX_TIMESTAMP())');
$f->bindParam(":name", $name, PDO::PARAM_STR);
$f->bindParam(":description", $description, PDO::PARAM_STR);
$f->bindParam(":badgeimageassetid", $autoincrement, PDO::PARAM_INT);
$f->bindParam(":awardingplaceid", $badgeawardingplaceid, PDO::PARAM_INT);
$f->execute();

$ext = pathinfo($filename, PATHINFO_EXTENSION);

if ($ext=="jpg" || $ext=="jpeg") {
$image_s = imagecreatefromjpeg($filename);
} else if ($ext=="png") {
$image_s = imagecreatefrompng($filename);
}

$imgwidth = imagesx($image_s);
$imgheight = imagesy($image_s);
$image = imagecreatetruecolor($width, $height);
imagealphablending($image,true);
imagecopyresampled($image,$image_s,0,0,0,0,$width,$height,$imgwidth,$imgheight);
$mask = imagecreatetruecolor($imgwidth, $imgheight);
$mask = imagecreatetruecolor($width, $height);
$transparent = imagecolorallocate($mask, 255, 0, 0);
imagecolortransparent($mask, $transparent);
imagefilledellipse($mask, $width/2, $height/2, $width-10, $height-10, $transparent);
$red = imagecolorallocate($mask, 0, 0, 0);
imagecopymerge($image, $mask, 0, 0, 0, 0, $width, $height,100);
imagecolortransparent($image, $red);
imagefill($image,0,0, $red);
imagepng($image, $thumbnailuploadDirectory . $thumbnailHash);
*/