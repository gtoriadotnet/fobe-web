<?php

$games_html = "";
$games = getAllGames($user->id)->fetchAll(PDO::FETCH_ASSOC);
foreach ($games as $game)
{
	$placethumbnail = handleGameThumb($game[id]);
	$games_html .= <<<EOT
	<li>
		<div class="studio-place-card text-center" style="cursor: pointer;" onclick="updateGame({$game[id]})">
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
		<h5>Save as:</h5>
		<hr>
		<div class="catalog-container">
			<ul>
				{$games_html}
			</ul>
		</div>
	</div>
	<script>
	function updateGame(id)
	{
		if (window.external.SaveUrl('https://www.alphaland.cc/Studio/Data/UploadData?id=' + id + '\''))
		{
			alert("Uploaded");
			window.close();
		}
		else
		{
			alert("Failed to Upload");
			window.close();
		}
	}
	</script>
EOT;

pageHandler();
$ph->navbar = "";
$ph->footer = "";
$ph->pageTitle("Upload");
$ph->body = $body;
$ph->output();