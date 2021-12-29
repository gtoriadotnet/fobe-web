<?php

/*
	Alphaland 2021 site configuration
	This is extremely sensitive.
	TODO: not ideal to hardcode paths like this, clean up

	Fuck u nsg
	Fuck you too Austin :)
	my balls yo jaws
	from m.2 ssd
	TODO: kill nsg
*/

use Alphaland\Users\Activation;
use Alphaland\Users\TwoFactor;
use Alphaland\Moderation\UserModerationManager;
use Alphaland\Web\WebContextManager;
use Alphaland\Common\System;
use Alphaland\Users\Session;

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
	$jsversion = "12.00"; //update this after updating JS, this will re-cache the latest js for users
	$cssversion = "12.00"; //update this after updating CSS, this will re-cache the latest css for users

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
	$pbsOverlayPath = "C:/Webserver/nginx/Alphaland/PersonalServerOverlay.png";
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
	
	//alphaland specfic dependencies (listing manually for now due to active rewrite of stuff)
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Users/Activation.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Users/TwoFactor.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Users/ReferralProgram.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Moderation/UserModerationManager.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Common/HashingUtiltity.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Web/IpRange.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Web/WebContextManager.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Common/System.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Games/Game.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Grid/RccServiceHelper.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Assets/Render.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/UI/ImageHelper.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Users/Render.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Common/Signing.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Common/Email.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Games/Ticket.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Users/User.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Users/Session.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Users/Outfit.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Moderation/Filter.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Users/Badge.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Administration/SignupKey.php";
	include "C:/Webserver/nginx/Alphaland/globals/Dependencies/Groups/Group.php";

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

	//user
	$user = new Session();
	
	//redirects
	if (!System::IsCommandLine() && //is not executed from cmd line
	!WebContextManager::VerifyAccessKeyHeader()) //is not an authenticated rcc
	{
		$accesseddomain = $_SERVER['SERVER_NAME'];
		$accesseddirectory = $_SERVER['PHP_SELF'];

		if ($accesseddomain == "www.".$domain && //if the domain the user is visiting www
		$_SERVER['HTTP_USER_AGENT'] != $clientUserAgent) { //is not client user agent
			WebContextManager::ForceHttpsCloudflare();
		}

		//account status checks
		$activated = Activation::IsUserActivated($GLOBALS['user']->id);
		$twofactor = TwoFactor::IsSession2FAUnlocked();
		$banned = UserModerationManager::IsBanned($GLOBALS['user']->id);
		$maintenance = WebContextManager::IsUnderMaintenance();

		if ($maintenance) { //check if under maintenance
			if ($accesseddirectory != "/maintenance.php") {
				WebContextManager::Redirect($url . "/maintenance");
			}
		} else if ($GLOBALS['user']->logged_in && $banned) { //check if banned
			if ($accesseddirectory != "/ban.php" &&
			$accesseddirectory != "/logout.php") {
				WebContextManager::Redirect($url . "/ban");
			}
		} else if ($GLOBALS['user']->logged_in && !$activated) { //check if activated
			if ($accesseddirectory != "/activate.php" && 
			$accesseddirectory != "/logout.php") {
				WebContextManager::Redirect($url . "/activate");
			}
		} else if ($GLOBALS['user']->logged_in && !$twofactor) { //check if 2fa is unlocked
			if ($accesseddirectory != "/2fa.php") {
				WebContextManager::Redirect($url . "/2fa");
			}
		}

		//pages accessible to users who aren't logged in
		if (!$GLOBALS['user']->logged_in) { //not logged in
			if ($accesseddomain == "www.".$domain) { //accessing www
				if ($accesseddirectory != "/index.php" &&
				$accesseddirectory != "/login/index.php" &&
				$accesseddirectory != "/login/forgotpassword.php" &&
				$accesseddirectory != "/register.php" &&
				$accesseddirectory != "/verifyemail.php" &&
				$accesseddirectory != "/maintenance.php" &&
				$accesseddirectory != "/noJS.php" &&
				$accesseddirectory != "/ban.php" &&
				$accesseddirectory != "/404.php" &&
				$accesseddirectory != "/Game/Negotiate.php" &&
				$accesseddirectory != "/settings/resetpassword.php" &&
				$accesseddirectory != "/secret/localtesting.php") { //for local client testing, doesn't contain anything sensitive
					WebContextManager::Redirect($url);
				}
			}
			else if ($accesseddomain == "api.".$domain) { //api
				if ($accesseddirectory != "/logo.php") {
					WebContextManager::Redirect($url);
				}
			}
			else if ($accesseddomain == "data.".$domain) { //data
				if ($accesseddirectory != "/Error/Dmp.ashx") {
					WebContextManager::Redirect($url);
				}
			}
			else if ($accesseddomain == "setup.".$domain) { //setup
				//do nothing (we arent restricting on this subdomain)
			}
			else if ($accesseddomain == "clientsettings.api.".$domain) { //clientsettings
				//do nothing (we arent restricting on this subdomain)
			} else {
				WebContextManager::Redirect($url);
			}
		}
	}
} 
catch (Exception $e)
{
	die("Alphaland is currently unavailable.");
}