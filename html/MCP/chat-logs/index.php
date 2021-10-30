<?php

if(!$user->isStaff())
{
    redirect("/");
}

$body = <<<EOT
<h5 class="text-center">Chat Logs</h5>
<h5 class="text-center">You can sort by Censored, by username and search for words</h5>
<h5 class="text-center">If you believe a user deserves a ban, start off with a warning</h5>
<hr>
<div class="container-fluid">
	<div class="container">
		<div class="col-sm marg-bot-15">
			<div class="card marg-auto" style="min-height:16rem;">
				<div class="card-body">
					<h5>Chat Logs</h5>
					<div class="row">
						<div class="col-sm">
							<div class="input-group">
								<div class="input-group">
									<input type="text" id="username_query" class="form-control" autocomplete="off" placeholder="Username">
									<div class="input-group-append">
										<button type="button" onclick="setUsername($('#username_query').val())" class="btn btn-success" type="button">Sort</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm">
							<div class="input-group">
								<div class="input-group">
									<input type="text" id="search_query" class="form-control" autocomplete="off" placeholder="Search">
									<div class="input-group-append">
										<button type="button" onclick="setSearchQuery($('#search_query').val())" class="btn btn-success" type="button">Search</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<hr>
					<div id="message-container">
							
					</div>
					<div class="container mt-2 mb-2 text-center">
						<div id="page-buttons" class="btn-group" role="group" aria-label="First group">

						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
/*
	Alphaland 2021
*/

var usernamequery = "";
var searchquery = "";

function setSearchQuery(query)
{
	searchquery = query;
	logsPage(1);
}

function setUsername(username)
{
	usernamequery = username;
	logsPage(1);
}

function logsPage(num)
{
	var html = '<div class="row">';
	html+= '<div class="col-sm marg-bot-15">';
	html+= '<div class="card">';
	html+= '<div class="card-body">';
	html+= '<h6>From <a class="red-a" href="/profile/view?id={userid}"> {username}</a> : <a style="color:grey;"> {date}</a></h6>';
	html+= '<h6>Game:<a class="red-a" href="/games/view?id={placeId}"> {placeName}</a></h6>';
	html+= '<div class="row marg-bot-15">';
	html+= '<div class="col-sm-2">';
	html+= '<a href="/profile/view?id={userid}"><img class="card-img-top marg-bot-15" src="{thumbnail}" style="width:4rem;border-radius:100%;"></a>';
	html+= '</div>';
	html+= '<div class="col-sm" style="overflow:hidden;">';
	html+= '<p>"{message}"</p>';
	html+= '</div>';
	html+= '</div>';
	html+= '</div>';
	html+= '</div>';
	html+= '</div>';
	html+= '</div>';
		
	multiPageHelper("logsPage", "https://www.alphaland.cc/MCP/chat-logs/loggedChats", "https://api.alphaland.cc/logo", "#message-container", "#page-buttons", html, num, 40, searchquery, "No Results", "&username="+usernamequery);
}

logsPage(1);
</script>

EOT;

pageHandler();
$ph->pageTitle("Chat Logs");
$ph->body = $body;
$ph->output();