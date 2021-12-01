<?php

use Alphaland\Web\WebContextManager;

if (!WebContextManager::IsUnderMaintenance())
{
	redirect("/");
}

$websettings = $pdo->prepare("SELECT * FROM websettings");
$websettings->execute();
$websettings = $websettings->fetch(PDO::FETCH_OBJ);

$status = '';
if (!empty($websettings->maintenance_text))
{
	$status = $websettings->maintenance_text; //use custom text
}
else
{
	$status = $websettings->default_maintenance_text; //default maintenance text
}

$body = <<<EOT
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
	margin: 0;
	font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
	background-image: url(https://thumbs.gfycat.com/RingedSpicyKodiakbear-size_restricted.gif);
	color: white;
	text-align: center;
}

.container {
	margin: auto;
	padding: 0;
	width: 480px;
    height: 860px;
	background-color: rgb(0, 0, 0, 0.2);
	text-align: center;
}

@media (min-width: 992px) {
    .container {
    margin: auto;
	padding: 0;
	width: 100%;
	height: 100%;
	background-color: rgb(0, 0, 0, 0.2);
	text-align: center;
    }
}
</style>
<div class="container" style="flex-direction: column!important;justify-content: center!important;display: flex!important;">
	<img style="max-width: 30rem;max-width: 30rem;margin-right: auto;margin-left: auto;" src="alphaland/cdn/imgs/alphaland-white-1024.png">
	<h1 style="text-align:center; ">{$status}</h1>
</div>
EOT;

echo $body;