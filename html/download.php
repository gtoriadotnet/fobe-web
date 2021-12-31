<?php

use Alphaland\Web\WebsiteSettings;

$alphalandVersion = WebsiteSettings::GetSetting("AlphalandVersion");
$alphalandStudioVersion = WebsiteSettings::GetSetting("AlphalandStudioVersion");

if (isset($_POST['SubmitClient']))
{
	$loc = $GLOBALS['setupHtmlPath'].$alphalandVersion."-AlphalandLauncher.exe";
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=AlphalandLauncher.exe");
	echo file_get_contents($loc);
}

if (isset($_POST['SubmitStudio']))
{
	$loc = $GLOBALS['setupHtmlPath'].$alphalandStudioVersion."-AlphalandStudioLauncher.exe";
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=AlphalandStudioLauncher.exe");
	echo file_get_contents($loc);
}

$body = <<<EOT
<style type="text/css">
body, html {
	height: 100%;
	margin: 0;
}

body {
	background-image: url("/alphaland/cdn/imgs/download-bg.png");
	background-position: center center;
	background-repeat: no-repeat;
	background-attachment: fixed;
	background-size: cover;
}
</style>
<div class="container">
	<div class="jumbotron" style="background-color:rgb(200,200,200,0.8);backdrop-filter:blur(10px);">
		<div class="text-center">
			<h1 class="display-4">Download Alphaland</h1>
			<img width="400" class="img-fluid" src="https://api.alphaland.cc/logo">
			<form action="" method="post">
				<button type="SubmitClient" name="SubmitClient" class="btn btn-lg btn-danger" type="button">Download Client</button>
				<button type="SubmitStudio" name="SubmitStudio" class="btn btn-lg btn-danger" type="button">Download Studio</button>
			</form>
		</div>
	</div>
</div>
EOT;

pageHandler();
$ph->pageTitle("Download");
$ph->body = $body;
$ph->output();