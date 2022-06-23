<?php

/*
	Finobe 2021 site configuration
	This is extremely sensitive.
	TODO: not ideal to hardcode paths like this, clean up

	Fuck u nsg
	Fuck you too Austin :)
	my balls yo jaws
	from m.2 ssd
	TODO: kill nsg
*/

use Finobe\Users\Activation;
use Finobe\Users\TwoFactor;
use Finobe\Moderation\UserModerationManager;
use Finobe\Web\WebContextManager;
use Finobe\Common\System;
use Finobe\Users\Session;

try 
{
	//php config
	ini_set("display_errors", "Off");
	ignore_user_abort(true);
	
	//PDO
	$pdoOptions = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //bad for prod?
		//PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_PERSISTENT => true
	);
								//host				//db name					//db user						  //db password              //options
	$pdo = new PDO("mysql:host=localhost;dbname=finobedatabase", "aa9205c5b776b2368833bec1e8b34e1c", "68adae776e087fb1b34baf439710cf94", $pdoOptions);

	//general vars
	$jsversion = "12.00"; //update this after updating JS, this will re-cache the latest js for users
	$cssversion = "12.00"; //update this after updating CSS, this will re-cache the latest css for users

	$siteName = "Finobe"; //site name
	$domain = "idk16.xyz";
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
	$renderCDN = "https://trcdn.idk16.xyz"; //endpoint for renders
	$assetCDN = "https://acdn.idk16.xyz"; //endpoint for assets
	$thumbnailCDN = "https://tcdn.idk16.xyz"; //endpoint for thumbnails
	
	//cdn paths
	$renderCDNPath = "D:/Finobe/html_renders_cdn/"; //path to where renders are stored
	$thumbnailCDNPath = "D:/Finobe/html_thumbs_cdn/"; //path to where thumbnails are stored
	$assetCDNPath = "D:/Finobe/html_assets_cdn/"; //path to where assets are stored
	
	//lua script paths
	$avatarthumbnailscript = "D:/Finobe/luascripts/thumbnails/AvatarScript.lua";
	$facethumbnailscript = "D:/Finobe/luascripts/thumbnails/FaceScript.lua";
	$hatthumbnailscript = "D:/Finobe/luascripts/thumbnails/HatScript.lua";
	$tshirtthumbnailscript = "D:/Finobe/luascripts/thumbnails/TShirtScript.lua";
	$shirtthumbnailscript = "D:/Finobe/luascripts/thumbnails/ShirtScript.lua";
	$pantsthumbnailscript = "D:/Finobe/luascripts/thumbnails/PantsScript.lua";
	$headthumbnailscript = "D:/Finobe/luascripts/thumbnails/HeadScript.lua";
	$placethumbnailscript = "D:/Finobe/luascripts/thumbnails/PlaceScript.lua";
	$modelthumbnailscript = "D:/Finobe/luascripts/thumbnails/ModelScript.lua";
	$gearthumbnailscript = "D:/Finobe/luascripts/thumbnails/GearScript.lua";
	$avatarcloseupthumbnailscript = "D:/Finobe/luascripts/thumbnails/AvatarCloseupScript.lua";
	$meshthumbnailscript = "D:/Finobe/luascripts/thumbnails/MeshScript.lua";
	$packagescript = "D:/Finobe/luascripts/thumbnails/PackageScript.lua"; 
	$gameserverscript = "D:/Finobe/luascripts/game/gameserver.lua"; 

	//soap paths
	$RCCwsdl = "D:/Finobe/RCCService.wsdl"; //wsdl path for SOAP

	//misc paths
	$pbsOverlayPath = "D:/Finobe/PersonalServerOverlay.png";
	$setupHtmlPath = "D:/Finobe/html_setup/";
	$defaultPlacesPath = "D:/Finobe/default_places/"; //path to where the default places are stored
	$defaultPbsPlacesPath = "D:/Finobe/default_pbs_places/"; //path to where the default pbs places are stored
	$defaultXmlsPath = "D:/Finobe/default_xmls/"; //path to where the default xmls stored
	$privateKeyPath = "D:/Finobe/FinobeRawKey.txt"; //path to where the private key is stored

	//machine ip's
	$gameMachine = "167.114.96.92"; //IP address of the machine that runs gameservers
	$renderMachine = "192.168.1.234"; //IP address of the machine that renders thumbnails

	//arbiter ip's
	$gamesArbiter = "192.168.1.169:64989";	//IP address/port of the Arbiter running on the gameserver machine
	$thumbnailArbiter = $renderMachine.":64989"; //IP address/port of the Arbiter running on the render machine
	
	//autoloader include
	require 'D:/Finobe/vendor/autoload.php';
	
	//finobe specfic dependencies (listing manually for now due to active rewrite of stuff)
	include "D:/Finobe/globals/Dependencies/Users/Activation.php";
	include "D:/Finobe/globals/Dependencies/Users/TwoFactor.php";
	include "D:/Finobe/globals/Dependencies/Users/ReferralProgram.php";
	include "D:/Finobe/globals/Dependencies/Moderation/UserModerationManager.php";
	include "D:/Finobe/globals/Dependencies/Common/HashingUtiltity.php";
	include "D:/Finobe/globals/Dependencies/Web/WebContextManager.php";
	include "D:/Finobe/globals/Dependencies/Common/System.php";
	include "D:/Finobe/globals/Dependencies/Assets/Asset.php";
	include "D:/Finobe/globals/Dependencies/Games/Game.php";
	include "D:/Finobe/globals/Dependencies/Grid/RccServiceHelper.php";
	include "D:/Finobe/globals/Dependencies/Assets/Render.php";
	include "D:/Finobe/globals/Dependencies/UI/ImageHelper.php";
	include "D:/Finobe/globals/Dependencies/Users/Render.php";
	include "D:/Finobe/globals/Dependencies/Common/Signing.php";
	include "D:/Finobe/globals/Dependencies/Common/Email.php";
	include "D:/Finobe/globals/Dependencies/Games/Ticket.php";
	include "D:/Finobe/globals/Dependencies/Users/User.php";
	include "D:/Finobe/globals/Dependencies/Users/Session.php";
	include "D:/Finobe/globals/Dependencies/Users/Outfit.php";
	include "D:/Finobe/globals/Dependencies/Moderation/Filter.php";
	include "D:/Finobe/globals/Dependencies/Users/Badge.php";
	include "D:/Finobe/globals/Dependencies/Administration/SignupKey.php";
	include "D:/Finobe/globals/Dependencies/Economy/EconomyHelper.php";
	include "D:/Finobe/globals/Dependencies/Groups/Group.php";
	include "D:/Finobe/globals/Dependencies/Web/WebsiteSettings.php";
	include "D:/Finobe/globals/Dependencies/Web/IpRange.php";

	//authenticator 
	$authenticator = new PHPGangsta_GoogleAuthenticator();

	//mailer
	$mail = new PHPMailer\PHPMailer\PHPMailer(true);
	$mail->IsSMTP();
	$mail->SMTPAuth   = TRUE;
	$mail->SMTPSecure = "tls";
	$mail->Port       = 587;
	$mail->Host       = "smtp.gmail.com";
	$mail->Username   = "finobetemp@gmail.com"; //google for now (easy and free)
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
	die("Finobe is currently unavailable.");
}