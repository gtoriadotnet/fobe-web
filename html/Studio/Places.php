<?php

if(!isLoggedIn())
{
	redirect("../login?referral=" . "https://www.alphaland.cc/Studio/Places");
}

$games_html = "";
$games = getAllGames($user->id)->fetchAll(PDO::FETCH_ASSOC);

foreach ($games as $game)
{
	$placethumbnail = handleGameThumb($game[id]);
	$games_html .= <<<EOT
	<li>
		<div class="studio-place-card text-center" style="cursor: pointer;" onclick="openGame({$game[id]})">
			<a href="#">
			<div class="studio-place-card-img">
			<img class="img-fluid" src="{$placethumbnail}">
		</div>
			<p class="no-overflow">{$game[Name]}</p>
			</a>
		</div>
	</li>
EOT;
}

$body = <<<EOT
	<div class="container mt-2">
		<h5>My Places:</h5>
		<hr>
		<div class="catalog-container">
			<ul>
				{$games_html}
			</ul>
		</div>
	</div>
	<script>
	function openGame(id) 
	{
		if (!window.external.StartGame("","","game:Load('https://www.alphaland.cc/asset/?id=" + id + "') game:SetPlaceId(" + id + ")"))
		{
			alert("Failed to open place, please report this");
			window.close();
		}
	}
	</script>
EOT;

pageHandler();
$ph->navbar = "";
$ph->footer = "";
$ph->pageTitle("My Places");
$ph->body = $body;
$ph->output();