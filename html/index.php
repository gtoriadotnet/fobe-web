<?php

$body = '';
if(!isLoggedIn()) 
{
	$body = <<<EOT
	<style type="text/css">
	html, body {
		font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji"!important;
		height: 100%!important;
	}

	body {
		margin: 0!important;
	}

	#videoBG {
		object-fit: cover!important;
		width: 100vw!important;
		height: 100vh!important;
		position: fixed!important;
		top: 0!important;
		left: 0!important;
		z-index: 0!important;
	}

	#overlayshadow {
		object-fit: cover!important;
		width: 100vw!important;
		height: 100vh!important;
		position: fixed!important;
		top: 0!important;
		left: 0!important;
		z-index: 1!important;
		background-color: black!important;
		opacity: 0.5!important;
	}

	.viewport-header {
		position: relative!important;
		height: 100vh!important;
		text-align: center!important;
		display: flex!important;
		align-items: center!important;
		justify-content: center!important;
		z-index: 3!important;
	}

	index-img {
		width: 40vw!important;
		text-align: center!important;
		display: inline-block!important;
	}

	#head-text {
		color: white!important;
		display: inline-block!important;
		font-size: 2vw!important;
		padding: .5vw!important;
	}

	#paragraph-text {
		color: white!important;
		display: inline-block!important;
		font-size: 1.2vw!important;
	}

	#button-group {
		color: white!important;
		margin-top: .6vw!important;
	}

	#button-group button {
		color: white!important;
		margin-top: .6vw!important;
		cursor: pointer!important;
		display: inline-block!important;
		font-weight: 400!important;
		color: #212529!important;
		text-align: center!important;
		vertical-align: middle!important;
		-webkit-user-select: none!important;
		-moz-user-select: none!important;
		-ms-user-select: none!important;
		user-select: none!important;
		background-color: transparent!important;
		border: 1px solid transparent!important;
		padding: .32vw .7vw!important;
		font-size: .8vw!important;
		line-height: 1.5!important;
		border-radius: .2vw!important;
		transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out!important;
		color: #fff!important;
		background-color: #dc3545!important;
		border-color: #dc3545!important;
	}

	#button-group button:hover {
		color: #fff!important;
		background-color: #c82333!important;
		border-color: #bd2130!important;
	}

	.index-row {
		-ms-flex-wrap: wrap!important;
		flex-wrap: wrap!important;
		margin-right: -15px!important;
		margin-left: -15px!important;
	}

	.index-container {
		display: block!important;
	}

	#ortxt {
		color: white!important;
		margin-top:10px;
	}
	</style>

	<div id="overlayshadow"></div>
	<video id="videoBG" autoplay loop playsinline muted>
		<source src="/alphaland/cdn/vids/index-vid.m4v" type="video/mp4">
	</video>

	<div class="viewport-header">
		<div class="index-container">
			<div class="index-row">
				<img class="img-fluid" style="width:50rem;padding:10px;" src="/alphaland/cdn/imgs/alphaland-long.png">
			</div>
			<div class="index-row">
				<div style="margin-top:20px;">
					<a href="login"><button class="btn btn-danger">Login</button></a><h> </h><a href="register"><button class="btn btn-danger">Register</button></a>
				</div>
			</div>
		</div>
	</div>
EOT;
}
else
{
	//user is logged in, send to home page
	header('Location: home');
}

pageHandler();
$ph->navbar = "";
$ph->footer = "";
$ph->pageTitle("Alphaland");
$ph->body = $body;
$ph->output();