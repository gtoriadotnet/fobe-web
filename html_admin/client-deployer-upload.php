<?php

use Fobe\Web\WebContextManager;

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
	$expectedfiles = 14;
} else if ($deploytype == "studio") {
	$expectedfiles = 17;
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
		if ($filename == "FinobeApp.zip" ||
		$filename == "FinobeLauncher.exe" ||
		$filename == "content-fonts.zip" ||
		$filename == "content-music.zip" ||
		$filename == "content-particles.zip" ||
		$filename == "content-sky.zip" ||
		$filename == "content-sounds.zip" ||
		$filename == "content-terrain.zip" ||
		$filename == "content-textures.zip" ||
		$filename == "content-textures2.zip" ||
		$filename == "content-textures3.zip" ||
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
		if ($filename == "FinobeStudio.zip" ||
		$filename == "content-scripts.zip" ||
		$filename == "BuiltInPlugins.zip" ||
		$filename == "imageformats.zip" ||
		$filename == "FinobeStudioLauncher.exe" ||
		$filename == "content-fonts.zip" ||
		$filename == "content-music.zip" ||
		$filename == "content-particles.zip" ||
		$filename == "content-sky.zip" ||
		$filename == "content-sounds.zip" ||
		$filename == "content-terrain.zip" ||
		$filename == "content-textures.zip" ||
		$filename == "content-textures2.zip" ||
		$filename == "content-textures3.zip" ||
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
		$previousdeployversion = $ws->FinobeVersion;
	} else if ($deploytype == "studio") {
		$previousdeployversion = $ws->FinobeStudioVersion;
	}
	
	//deploy type specific stuff
	if ($deploytype == "client") {
		unlink($setup_html . $previousdeployversion . "-FinobeApp.zip");
	} else if ($deploytype == "studio") {
		unlink($setup_html . $previousdeployversion . "-FinobeStudio.zip");
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
	unlink($setup_html . $previousdeployversion . "-content-textures3.zip");
	unlink($setup_html . $previousdeployversion . "-Libraries.zip");
	unlink($setup_html . $previousdeployversion . "-redist.zip");
	unlink($setup_html . $previousdeployversion . "-shaders.zip");
	unlink($setup_html . $previousdeployversion . "-FinobeLauncher.exe");
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

	file_put_contents($setup_html . $newgameversion . '-rbxManifest.txt','');

	$fp = fopen($setup_html . $newgameversion . $versiontextname,"wb");
	fwrite($fp,$launcherfileversion);
	fclose($fp);

	//update in db
	if ($deploytype == "client") {
		$updatewebsettings = $pdo->prepare("UPDATE websettings SET AlphalandVersion = :av, security_version = :sv, md5_hash = :mh, GameFileVersion = :gv");
		$updatewebsettings->bindParam(":av", $newgameversion, PDO::PARAM_STR);
		$updatewebsettings->bindParam(":sv", $gamesecurityversion, PDO::PARAM_STR);
		$updatewebsettings->bindParam(":mh", $gamemd5hash, PDO::PARAM_STR);
		$updatewebsettings->bindParam(":gv", $gamefileversion, PDO::PARAM_STR);
		$updatewebsettings->execute();
	} else if ($deploytype == "studio") {
		$updatewebsettings = $pdo->prepare("UPDATE websettings SET AlphalandStudioVersion = :asv, StudioFileVersion = :sfv");
		$updatewebsettings->bindParam(":asv", $newgameversion, PDO::PARAM_STR);
		$updatewebsettings->bindParam(":sfv", $gamefileversion, PDO::PARAM_STR);
		$updatewebsettings->execute();
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