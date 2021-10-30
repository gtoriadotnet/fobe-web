<?php

$body = <<<EOT
<div class="container">
	<div class="input-group mb-3">
		<input type="text" class="form-control" placeholder="Search Groups" id="search_query" autocomplete="off">
		<div class="input-group-append">
			<button class="btn btn-danger" type="button" onclick="groupsPage(1,$('#search_query').val())">Search</button>
		</div>
	</div>
	<div id="groups">

	</div>
	<div class="text-center mt-2">
		<div id="groups-buttons" class="btn-group game-page-btn-group m-auto">

		</div>
	</div>
</div>
<script>

//global vars
var keyword = "";
var getparam = new URLSearchParams(window.location.search).get('keyword');
if (getparam)
{
	keyword = getparam;
}

//shit
function groupsPage(page,keyword="")
{
	var html = '<a href="/groups?id={id}" class="black-a-nounder w-100">';
	html += '<div class="card mb-2">';
	html += '<div class="card-body">';
	html += '<div class="row">';
	html += '<div class="col-sm-2 text-center">';
	html += '<img style="width:6rem;" src="{emblem}">';
	html += '</div>';
	html += '<div class="col-sm">';
	html += '<h5>{name}</h5>';
	html += '<p class="no-overflow" style="max-width:44rem;">{description}</p>';
	html += '</div>';
	html += '<div class="col-sm-2 text-center mt-3" style="font-size:1.2rem;">';
	html += '<i class="fas fa-users"></i>';
	html += '<p>{members}</p>';
	html += '</div>';
	html += '</div>';
	html += '</div>';
	html += '</div>';
	html += '</a>';
	
	if (keyword) {
		window.history.replaceState(null, null, "?keyword="+keyword);
	} else {
		window.history.replaceState(null, null, "/groups/search");
	}

	multiPageHelper("groupsPage", "https://api.alphaland.cc/groups/", "https://api.alphaland.cc/logo", "#groups", "#groups-buttons", html, page, 5, keyword, "No results");
}

groupsPage(1,keyword);
</script>
EOT;

pageHandler();
$ph->pageTitle("Search Groups");
$ph->body = $body;
$ph->output();