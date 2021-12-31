<?php

use Alphaland\Web\WebContextManager;
use Alphaland\Web\WebsiteSettings;

WebContextManager::ForceHttpsCloudflare();

//permissions
if(!($user->IsOwner())) {
	if ($user->IsAdmin()) {
		WebContextManager::Redirect("/");
	}
	die('bababooey');
}

//vars
$setup_html = $GLOBALS['setupHtmlPath']; //path to the setup "cdn"
$newgameversion = "version-" . bin2hex(openssl_random_pseudo_bytes(8));

//get params
$gamesecurityversion = $_GET['gamesecurityver'];
$gamemd5hash = $_GET['gamemd5'];
$gamefileversion = $_GET['gamefilever'];
$launcherfileversion = $_GET['launcherfilever'];
$deploytype = $_GET['type'];

//posted files
$files = $_FILES['file']['name'];
$file = $_FILES['file']['tmp_name'];

//expected get parameters
if ($deploytype == "client") {
	if (empty($gamesecurityversion) || 
	empty($gamemd5hash) || 
	empty($gamefileversion) || 
	empty($launcherfileversion) ||
	empty($deploytype)) {
		echo "Missing Parameters";
		die();
	};
} else if ($deploytype == "studio") {
	if (empty($gamefileversion) || 
	empty($launcherfileversion)) {
		echo "Missing Parameters";
		die();
	};
} else {
	echo "Invalid Deploy Type";
	die();
}

$expectedfiles = 0;
if ($deploytype == "client") {
	$expectedfiles = 13;
} else if ($deploytype == "studio") {
	$expectedfiles = 16;
}

//expected files count
if (count($files) != $expectedfiles) {
	echo "Missing files";
	die();
}

//verify the files
$pass = false;
foreach ($files as $key=>$val) {
    $filename = $files[$key];

	//files to deploy
	if ($deploytype == "client") 
	{
		if ($filename == "AlphalandApp.zip" ||
		$filename == "AlphalandLauncher.exe" ||
		$filename == "content-fonts.zip" ||
		$filename == "content-music.zip" ||
		$filename == "content-particles.zip" ||
		$filename == "content-sky.zip" ||
		$filename == "content-sounds.zip" ||
		$filename == "content-terrain.zip" ||
		$filename == "content-textures.zip" ||
		$filename == "content-textures2.zip" ||
		$filename == "Libraries.zip" ||
		$filename == "redist.zip" ||
		$filename == "shaders.zip") {
			$pass = true;
		} else {
			$pass = false;
			break;
		}
	}
	else if ($deploytype == "studio")
	{
		if ($filename == "AlphalandStudio.zip" ||
		$filename == "content-scripts.zip" ||
		$filename == "BuiltInPlugins.zip" ||
		$filename == "imageformats.zip" ||
		$filename == "AlphalandStudioLauncher.exe" ||
		$filename == "content-fonts.zip" ||
		$filename == "content-music.zip" ||
		$filename == "content-particles.zip" ||
		$filename == "content-sky.zip" ||
		$filename == "content-sounds.zip" ||
		$filename == "content-terrain.zip" ||
		$filename == "content-textures.zip" ||
		$filename == "content-textures2.zip" ||
		$filename == "Libraries.zip" ||
		$filename == "redist.zip" ||
		$filename == "shaders.zip") {
			$pass = true;
		} else {
			$pass = false;
			break;
		}
	}
}

if ($pass) {
	//delete old files
	
	$previousdeployversion = "";
	if ($deploytype == "client") {
		$previousdeployversion = WebsiteSettings::GetSetting("AlphalandVersion");
	} else if ($deploytype == "studio") {
		$previousdeployversion = WebsiteSettings::GetSetting("AlphalandStudioVersion");
	}
	
	//deploy type specific stuff
	if ($deploytype == "client") {
		unlink($setup_html . $previousdeployversion . "-AlphalandApp.zip");
	} else if ($deploytype == "studio") {
		unlink($setup_html . $previousdeployversion . "-AlphalandStudio.zip");
		unlink($setup_html . $previousdeployversion . "-content-scripts.zip");
		unlink($setup_html . $previousdeployversion . "-BuiltInPlugins.zip");
		unlink($setup_html . $previousdeployversion . "-imageformats.zip");
	}

	//these files are common between builds
	unlink($setup_html . $previousdeployversion . "-content-fonts.zip");
	unlink($setup_html . $previousdeployversion . "-content-music.zip");
	unlink($setup_html . $previousdeployversion . "-content-particles.zip");
	unlink($setup_html . $previousdeployversion . "-content-sky.zip");
	unlink($setup_html . $previousdeployversion . "-content-sounds.zip");
	unlink($setup_html . $previousdeployversion . "-content-terrain.zip");
	unlink($setup_html . $previousdeployversion . "-content-textures.zip");
	unlink($setup_html . $previousdeployversion . "-content-textures2.zip");
	unlink($setup_html . $previousdeployversion . "-Libraries.zip");
	unlink($setup_html . $previousdeployversion . "-redist.zip");
	unlink($setup_html . $previousdeployversion . "-shaders.zip");
	unlink($setup_html . $previousdeployversion . "-AlphalandLauncher.exe");
	unlink($setup_html . $previousdeployversion . "-BootstrapperVersion.txt");

	//move all the files
	foreach ($files as $key=>$val) {
		move_uploaded_file($file[$key], $setup_html . $newgameversion . "-" . $files[$key]);
	}

	//write appropriate version txt
	$versiontextname = "";
	if ($deploytype == "client") {
		$versiontextname = "-BootstrapperVersion.txt";
	} else if ($deploytype == "studio") {
		$versiontextname = "-BootstrapperQTStudioVersion.txt";
	}

	$fp = fopen($setup_html . $newgameversion . $versiontextname,"wb");
	fwrite($fp,$launcherfileversion);
	fclose($fp);

	//update in db
	if ($deploytype == "client") {
		WebsiteSettings::UpdateSetting("AlphalandVersion", $newgameversion);
		WebsiteSettings::UpdateSetting("security_version", $gamesecurityversion);
		WebsiteSettings::UpdateSetting("md5_hash", $gamemd5hash);
		WebsiteSettings::UpdateSetting("GameFileVersion", $gamefileversion);
	} else if ($deploytype == "studio") {
		WebsiteSettings::UpdateSetting("AlphalandStudioVersion", $newgameversion);
		WebsiteSettings::UpdateSetting("StudioFileVersion", $gamefileversion);
	}

	//output the new version

	if ($deploytype == "client") {
		echo "Deployed Client: " . $newgameversion;
	} else if ($deploytype == "studio") {
		echo "Deployed Studio: " . $newgameversion;
	}
} else {
	echo "Invalid Files";
}