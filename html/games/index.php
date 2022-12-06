<?php

$body = <<<EOT
<div class="container">
	<div class="row">
		<div class="col-sm-1">
			<h5>Games</h5>
		</div>
		<div class="col-sm-3 mb-3">
			<a href="/download"><button class="btn btn-danger w-100">Download Fobe</button></a>
		</div>
		<div class="col-sm">

				<div class="input-group mb-3 justify-content-end">
					<input autocomplete="off" type="text" name="keyword" id="keyword_input" class="form-control game-search-input" placeholder="Search Games">
					<div class="input-group-append">
						<button onclick="gamePage(1, $('#keyword_input').val())" class="btn btn-danger" type="button">Search</button>
					</div>
				</div>
			
		</div>
	</div>
	<div class="card">
		<div class="card-body game-container">
			<ul id="games-container" class="">
				
			</ul>
		</div>
	</div>
</div>
<div class="text-center mt-2">
	<div id="page-buttons" class="btn-group game-page-btn-group m-auto" role="group" aria-label="First group">
	</div>
</div>
<script>
function gamePage(num, keyword = "")
{
	var html= '<li>'
	html+= '<div>';
	html+= '<div class="game-card">';
	html+= '<a href=/games/view?id={id}>';
	html+= '<img src = "{thumbnail}">';
	html+= '<span>';
	html+= '<p>{name}</p>';
	html+= '<p>By: {creatorName}</p>';
	html+= '<div class="w-100 text-center mb-1">';
	html+= '<div class="inline-flex">';
	html+= '<p><i class="fas fa-users"></i> {playerCount}</p>';
	html+= '</div>';
	html+= '<div class="inline-flex">';
	html+= '<p><i class="fas fa-door-open"></i> {visits}</p>';
	html+= '</div>';
	html+= '</div>';
	html+= '</span>';
	html+= '</a>';
	html+= '</div>';
	html+= '</div>';
	html+= '</li>';

	multiPageHelper("gamePage", [keyword], "https://api.idk16.xyz/games/sitegames", "https://api.idk16.xyz/logo", "#games-container", "#page-buttons", html, num, 21, keyword, "No results");
}

$('#keyword_input').keypress(function(event) {
    if (event.keyCode == 13 || event.which == 13) {
		gamePage(1, $('#keyword_input').val());
    }
});

gamePage(1);
</script>
EOT;

pageHandler();
$ph->body = $body;
$ph->pageTitle("Games");
$ph->output();