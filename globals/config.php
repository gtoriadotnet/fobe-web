<?php

/*
	Alphaland 2021 site configuration
	This is extremely sensitive.

	Fuck u nsg
	Fuck you too Austin :)
	my balls yo jaws
*/

try 
{
	//php config
	ini_set("display_errors", "Off");
	ignore_user_abort(true);
	
	//PDO
	$pdoOptions = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_PERSISTENT => true
	);
								//host				//db name					//db user						  //db password              //options
	$pdo = new PDO("mysql:host=localhost;dbname=alphalanddatabase", "aa9205c5b776b2368833bec1e8b34e1c", "68adae776e087fb1b34baf439710cf94", $pdoOptions);

	//general vars
	$jsversion = "3.07"; //update this after updating JS, this will re-cache the latest js for users
	$cssversion = "3.02"; //update this after updating CSS, this will re-cache the latest css for users

	$siteName = "Alphaland"; //site name
	$domain = "alphaland.cc";
	$url = "https://www.".$domain; //site URL
	$ws = $pdo->query("SELECT * FROM websettings WHERE id = 1")->fetch(PDO::FETCH_OBJ); //websettings
	$clientUserAgent = "Roblox/WinInet";
	$ROBLOXAssetAPI = "https://assetdelivery.roblox.com/v1/asset/?id=";
	$ROBLOXProductInfoAPI = "https://api.roblox.com/marketplace/productinfo?assetId=";
	$ROBLOXAssetThumbnailAPI = "https://thumbnails.roblox.com/v1/assets?assetIds=";

	//default character hashes
	$defaultOutfitHash = "e335382cb0ef996df9053df58adcbe95"; //default render hash for characters
	$defaultHeadshotHash = "fb5d52c08aa538483647373c5a20fd73"; //default headshot render for characters

	//cdn urls
	$renderCDN = "https://trcdn.alphaland.cc"; //endpoint for renders
	$assetCDN = "https://acdn.alphaland.cc"; //endpoint for assets
	$thumbnailCDN = "https://tcdn.alphaland.cc"; //endpoint for thumbnails
	
	//cdn paths
	$renderCDNPath = "C:/Webserver/nginx/Alphaland/html_renders_cdn/"; //path to where renders are stored
	$thumbnailCDNPath = "C:/Webserver/nginx/Alphaland/html_thumbs_cdn/"; //path to where thumbnails are stored
	$assetCDNPath = "C:/Webserver/nginx/Alphaland/html_assets_cdn/"; //path to where assets are stored
	
	//lua script paths
	$avatarthumbnailscript = "C:/Webserver/nginx/Alphaland/luascripts/thumbnails/AvatarScript.lua";
	$facethumbnailscript = "C:/Webserver/nginx/Alphaland/luascripts/thumbnails/FaceScript.lua";
	$hatthumbnailscript = "C:/Webserver/nginx/Alphaland/luascripts/thumbnails/HatScript.lua";
	$tshirtthumbnailscript = "C:/Webserver/nginx/Alphaland/luascripts/thumbnails/TShirtScript.lua";
	$shirtthumbnailscript = "C:/Webserver/nginx/Alphaland/luascripts/thumbnails/ShirtScript.lua";
	$pantsthumbnailscript = "C:/Webserver/nginx/Alphaland/luascripts/thumbnails/PantsScript.lua";
	$headthumbnailscript = "C:/Webserver/nginx/Alphaland/luascripts/thumbnails/HeadScript.lua";
	$placethumbnailscript = "C:/Webserver/nginx/Alphaland/luascripts/thumbnails/PlaceScript.lua";
	$modelthumbnailscript = "C:/Webserver/nginx/Alphaland/luascripts/thumbnails/ModelScript.lua";
	$gearthumbnailscript = "C:/Webserver/nginx/Alphaland/luascripts/thumbnails/GearScript.lua";
	$avatarcloseupthumbnailscript = "C:/Webserver/nginx/Alphaland/luascripts/thumbnails/AvatarCloseupScript.lua";
	$meshthumbnailscript = "C:/Webserver/nginx/Alphaland/luascripts/thumbnails/MeshScript.lua";
	$packagescript = "C:/Webserver/nginx/Alphaland/luascripts/thumbnails/PackageScript.lua"; 
	$gameserverscript = "C:/Webserver/nginx/Alphaland/luascripts/game/gameserver.lua"; 

	//soap paths
	$RCCwsdl = "C:/Webserver/nginx/Alphaland/RCCService.wsdl"; //wsdl path for SOAP

	//misc paths
	$setupHtmlPath = "C:/Webserver/nginx/Alphaland/html_setup/";
	$defaultPlacesPath = "C:/Webserver/nginx/Alphaland/default_places/"; //path to where the default places are stored
	$defaultPbsPlacesPath = "C:/Webserver/nginx/Alphaland/default_pbs_places/"; //path to where the default pbs places are stored
	$defaultXmlsPath = "C:/Webserver/nginx/Alphaland/default_xmls/"; //path to where the default xmls stored
	$privateKeyPath = "C:/Webserver/nginx/Alphaland/AlphalandRawKey.txt"; //path to where the private key is stored

	//machine ip's
	$gameMachine = "167.114.96.92"; //IP address of the machine that runs gameservers
	$renderMachine = "192.168.1.234"; //IP address of the machine that renders thumbnails

	//arbiter ip's
	$gamesArbiter = "192.168.1.169:64989";	//IP address/port of the Arbiter running on the gameserver machine
	$thumbnailArbiter = $renderMachine.":64989"; //IP address/port of the Arbiter running on the render machine
	
	//autoloader include
	require 'C:\Users\Administrator\vendor\autoload.php';
	
	//alphaland specfic dependencies
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Users/Activation.php";

	//authenticator 
	$authenticator = new PHPGangsta_GoogleAuthenticator();

	//mailer
	$mail = new PHPMailer\PHPMailer\PHPMailer(true);
	$mail->IsSMTP();
	$mail->SMTPAuth   = TRUE;
	$mail->SMTPSecure = "tls";
	$mail->Port       = 587;
	$mail->Host       = "smtp.gmail.com";
	$mail->Username   = "alphalandtemp@gmail.com"; //google for now (easy and free)
	$mail->Password   = "117A7AE7CE40674453E00492CB699F54";
	
	//cloudflare
	$cloudflareheader = array(
		"Content-Type: application/json",
		"X-Auth-Email: superativeroblox@gmail.com",
		"X-Auth-Key: 06ea819593bb6d038c8d49808c0f0f200124b"
	);
	
	//more includes
	require_once 'functions.php';
	require_once 'userauth.php';
	
	//redirects
	if (!commandLine() && //is not executed from cmd line
	!RCCHeaderEnvironment(true)) //is not an authenticated rcc
	{
		$accesseddomain = $_SERVER['SERVER_NAME'];
		$accesseddirectory = $_SERVER['PHP_SELF'];

		if ($accesseddomain == "www.".$domain && //if the domain the user is visiting www
		$_SERVER['HTTP_USER_AGENT'] != $clientUserAgent) { //is not client user agent
			forceHttpsCloudflare();
		}

		$activated = new Alphaland\Users\Activation();
		$activated = $activated::isUserActivated($GLOBALS['user']->id);
		$maintenance = checkIfUnderMaintenance();
		$banned = checkIfBanned($GLOBALS['user']->id);
		$twofactor = isSession2FAUnlocked();

		//step 1, check if under maintenance
		if ($maintenance) { //maintenance redirect
			if ($accesseddirectory != "/maintenance.php") {
				redirect($url . "/maintenance");
			}
		}

		//step 2, check if user is banned
		if ($GLOBALS['user']->logged_in && $banned) { //ban redirect
			if ($accesseddirectory != "/ban.php" &&
			$accesseddirectory != "/logout.php") {
				redirect($url . "/ban");
			}
		}
	
		//step 3, check if user is activated
		if ($GLOBALS['user']->logged_in && !$activated) { //activation redirect
			if ($accesseddirectory != "/activate.php" && 
			$accesseddirectory != "/logout.php") {
				redirect($url . "/activate");
			}
		}

		//step 4, check if 2fa is authenticated
		if ($GLOBALS['user']->logged_in && !$twofactor) { //2fa redirect
			if ($accesseddirectory != "/2fa.php") {
				redirect($url . "/2fa");
			}
		}

		//pages accessible to users who aren't logged in
		if (!$GLOBALS['user']->logged_in) { //not logged in
			if ($accesseddomain == "www.".$domain) { //www
				if ($accesseddirectory != "/index.php" &&
				$accesseddirectory != "/login/index.php" &&
				$accesseddirectory != "/login/forgotpassword.php" &&
				$accesseddirectory != "/register.php" &&
				$accesseddirectory != "/verifyemail.php" &&
				$accesseddirectory != "/maintenance.php" &&
				$accesseddirectory != "/noJS.php" &&
				$accesseddirectory != "/ban.php" &&
				$accesseddirectory != "/404.php" &&
				$accesseddirectory != "/Game/Negotiate.ashx" &&
				$accesseddirectory != "/asset/index.php" &&
				$accesseddirectory != "/settings/resetpassword.php" &&
				$accesseddirectory != "/secret/localtesting.php") { //for local client testing, doesn't contain anything sensitive
					redirect($url);
				}
			}
			else if ($accesseddomain == "api.".$domain) { //api
				if ($accesseddirectory != "/logo.php") {
					redirect($url);
				}
			}
			else if ($accesseddomain == "data.".$domain) { //data
				if ($accesseddirectory != "/Error/Dmp.ashx") {
					redirect($url);
				}
			}
			else if ($accesseddomain == "setup.".$domain) { //setup
				//do nothing (we arent restricting on this subdomain)
			}
			else if ($accesseddomain == "clientsettings.api.".$domain) { //clientsettings
				//do nothing (we arent restricting on this subdomain)
			} else {
				redirect($url);
			}
		}
	}
} 
catch (Exception $e)
{
	die("Alphaland is currently unavailable.");
}