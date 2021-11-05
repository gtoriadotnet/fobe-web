<?php

if(!($user->isOwner())) {
	die();
}

$authenticator = new PHPGangsta_GoogleAuthenticator();

function safeGenerate2FASecret($username)
{
	$secret = "";
	while (true) {
		$secret = $GLOBALS['authenticator']->createSecret();
		
		$keycheck = $GLOBALS['pdo']->prepare("SELECT * FROM `google_2fa` WHERE `secret` = :ac");
		$keycheck->bindParam(":ac", $secret, PDO::PARAM_STR);
		$keycheck->execute();
		if ($keycheck->rowCount() == 0) {
			break;
		}
	}
	return $secret;
}

function deleteUser2FA($userid)
{
	$del = $GLOBALS['pdo']->prepare("DELETE FROM `google_2fa` WHERE `userid` = :uid");
	$del->bindParam(":uid", $userid, PDO::PARAM_INT);
	$del->execute();
}

function getUser2FASecret($userid)
{
	$code = $GLOBALS['pdo']->prepare("SELECT * FROM `google_2fa` WHERE `userid` = :uid");
	$code->bindParam(":uid", $userid, PDO::PARAM_INT);
	$code->execute();
	if ($code->rowCount() > 0) {
		return $code->fetch(PDO::FETCH_OBJ)->secret;
	}
}

function verify2FACode($userid, $code)
{
	$secret = getUser2FASecret($userid);
	if ($secret) {
		if ($GLOBALS['authenticator']->verifyCode($secret, $code, 0)) {
			return true;
		}
	}
	return false;
}

function activateUser2FA($userid, $code)
{
	if(verify2FACode($userid, $code)) {
		$check = $GLOBALS['pdo']->prepare("UPDATE `google_2fa` SET `validated` = 1 WHERE `userid` = :uid");
		$check->bindParam(":uid", $userid, PDO::PARAM_INT);
		if ($check->execute()) {
			return true;
		}
	}
	return false;
}

function getUser2FAQR($userid)
{
	$qrcode = $GLOBALS['pdo']->prepare("SELECT * FROM `google_2fa` WHERE `userid` = :uid");
	$qrcode->bindParam(":uid", $userid, PDO::PARAM_INT);
	$qrcode->execute();
	if ($qrcode->rowCount() > 0) {
		return $qrcode->fetch(PDO::FETCH_OBJ)->qr;
	}
}

function initialize2FA($userid)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM `google_2fa` WHERE `userid` = :uid");
	$check->bindParam(":uid", $userid, PDO::PARAM_INT);
	$check->execute();
	if ($check->rowCount() > 0) {
		deleteUser2FA($userid);
	}

	$username = getUsername($userid);
	if ($username) {
		$secret = safeGenerate2FASecret($username);
		$qrcode = $GLOBALS['authenticator']->getQRCodeGoogleUrl($username, $secret, "alphaland.cc");
		$new2fa = $GLOBALS['pdo']->prepare("INSERT INTO `google_2fa`(`userid`, `secret`, `qr`, `whenGenerated`) VALUES (:uid, :secret, :qr, UNIX_TIMESTAMP())");
		$new2fa->bindParam(":uid", $userid, PDO::PARAM_INT);
		$new2fa->bindParam(":secret", $secret, PDO::PARAM_STR);
		$new2fa->bindParam(":qr", $qrcode, PDO::PARAM_STR);
		$new2fa->execute();
	}
}

/*
$username = "Astrologies";

$g = new \Google\Authenticator\GoogleAuthenticator();
$salt = '8c9e27216a6ca82002eeb21db39b8656f3e2daa1dc7719b';
$secret = $username.$salt;
echo '<img src="'.$g->getURL($username, 'example.com', $secret).'" />';
*/


/*
$validXML = true;
	try {
		$ParsedXML = new SimpleXMLElement($xml);
	} catch (Exception $e) {
		$validXML = false;
	}
						
	if ($validXML) {
		//find mesh urls
		$meshes = $ParsedXML->xpath('//Content[@name="MeshId"]/url');

		$meshuploadsuccess = true;
		foreach ($meshes as $mesh) {
			$assetid = convertAssetUrlToId($mesh);
			if (!$assetid) {
				return "Unsupported Asset";
			}
			$assetid = uploadRobloxMesh($assetname, $assetid, 1);
			if ($assetid !== FALSE) {
				$xml=str_replace($mesh, $GLOBALS['url'] . "/asset/?id=" . $assetid, $xml);
				RenderMesh($assetid);
			} else {
				$meshuploadsuccess = false;
				break;
			}
		}

	}
*/

/*
$html = file_get_contents("shit.txt");
$needle = "http://www.roblox.com/asset/?id=";
$lastPos = 0;

while (($lastPos = strpos($html, $needle, $lastPos))!== false) {
	$asseturl = substr($html, $lastPos, strlen($needle)+9);
    $lastPos = $lastPos + strlen($needle);

	$robloxasset = convertAssetUrlToId($asseturl);
	$newasset = uploadRobloxAnimation(getRobloxProductInfo($robloxasset)->Name, $robloxasset, 1);

	$html=str_replace($asseturl, $GLOBALS['url'] . "/asset/?id=" . $newasset, $html);
}

file_put_contents("finished.txt", $html);
*/

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