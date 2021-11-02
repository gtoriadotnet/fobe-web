<?php

/*
	Alphaland 2021 
*/

$gameID = $_GET['id'];
$gInfo = getAssetInfo($gameID);

if($gInfo !== false) 
{
	$gameName = $gInfo->Name;
	if ($gInfo->AssetTypeId != 9) //make sure its actually a place
	{
		redirect("/404");
	}
}
else
{
	redirect("/404");
}

checkForDeadJobs($gameID);

$body = '

<div style="display:none"; id="main-game-info-element">
	<div class="container-fluid">
		<div class="container">
			<div id = "success_alert" class="alert alert-success" role="alert" style="display:none";></div>
			<div id = "error_alert" class="alert alert-danger" role="alert" style="display:none";></div>
			<div class="row marg-bot-15" id="game-general-info">
				
			</div>
		</div>
	</div>
	<div class="container-fluid">
		<div class="container">
			<div class="row marg-bot-15">
				<div class="col-sm marg-bot-15">
					<div class="card marg-auto">
						<div class="card-body">
							<div class="row">
								<div class="col-sm">
									<h5>Server List</h5>
								</div>
								<div class="col-sm">
									<a style="float:right;" class="btn btn-danger text-white" onclick="serversList(1)">Refresh</a>
								</div>
							</div>
							<hr>
							<div id="active-server-list">
						
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="launching" tabindex="-1" role="dialog" aria-hidden="true" style="display:none;">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Launching</h5>
				</div>
				<div class="modal-body" id="linfo">
					
				</div>
				<div class="modal-footer" style="display:none;" id="closediv">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>
	<div id="comments_html" class="container-fluid" style="display:none">
		<div class="container">
			<div class="row">
				<div class="col-sm">
					<div class="card">
						<div class="card-body">
							<h5 class="mb-2">Comments</h5>
								<div class="input-group">
									<input type="text" class="form-control" id="comment_input" autocomplete="off" placeholder="New Comment">
									<div class="input-group-append">
										<button onclick="comments.submitComment($(\'#comment_input\').val())" class="btn btn-danger">Submit</button>	
									</div>
								</div>
							<hr>
							<div id="comments-container" class="container">
													
							</div>
							<div id="page-buttons-container" class="text-center mt-2">
								<div id="page-buttons" class="btn-group" role="group" aria-label="First group">

								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
var getparam = new URLSearchParams(window.location.search).get("id");

function playGame(id) {
	$.get("https://www.alphaland.cc/Game/authticket", function(data) {
		$("#linfo").html("Starting Alphaland...");
		$("#launching").modal("show");
		$("#closediv").hide();
		location.href = "alphaland-player-cc:1+launchmode:play+gameinfo:" + data + "+placelauncherurl:https://www.alphaland.cc/Game/PlaceLauncher.ashx?request=RequestGame&placeid=" + id;
		setTimeout(function() {
			$("#launching").modal("hide");
		}, 2500);	
	});
}

var jobExistCount = 0;
function handleUIJobShutdown(id) {
	setTimeout(function() {
		jobExistCount ++;
		getJSONCDS("https://api.alphaland.cc/game/jobExists?placeid="+getparam+"&jobid="+id)
		.done(function(object) {
			if (!object.result) {
				$("#server-"+id).remove();
				serversList(1);
			} else {
				if (jobExistCount < 8) {
					handleUIJobShutdown(id);
				} else {
					jobExistCount = 0;
					$("#shutdown-button-jobid-"+id).prop("disabled", false);
					$("#shutdown-button-jobid-"+id).text("Shut down");
				}
			}
		});
	}, 1000);
}

function shutdown(id) {
	$("#shutdown-button-jobid-"+id).prop("disabled", true);
	$("#shutdown-button-jobid-"+id).text("Shutting down...");
	getJSONCDS("https://api.alphaland.cc/game/management/manageJob?placeid="+getparam+"&jobid=" + id + "&shutdown=true")
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Success") {
			handleUIJobShutdown(id);
		} else {
			$(messageid).text(alert);
			$(messageid).show();
			window.scrollTo({top: 0, behavior: "smooth"});
			setTimeout(function() {
				$(messageid).hide();
			}, 2000);
		}	
	});
}

function returnShutdownButton(object) {
	var html = ``;
	if (object.isOwner) {
		html = `<button type=\"button\" class=\"btn btn-danger\" id=\'shutdown-button-jobid-`+object.jobid+`\' onclick=\"shutdown(\'`+object.jobid+`\')\">Shut down</button>`;
	}
	return html;
}

function returnServerPlayers(object) {
	var html = `
	<div class=\"mr-3 mb-2 inline-block\">
		<a href=\"/profile/view?id={userid}\"><img title=\"{username}\" class=\"img-fluid\" style=\"width:3rem;\" src=\"{thumbnail}\"></a>
	</div>
	`;

	return parseHtml(html, object.maxPlayers+5, object.players, "");
}

function serversList(page) {
	$("#active-server-list").html(""); //get rid of previous html

	var html = `
	<div class=\"row\" id=\"server-{jobid}\">
		<div class=\"col-sm\">
			<div class=\"card marg-auto mb-3\">
				<div class=\"card-body\">
					<p>
						{playerscount} of {maxPlayers} Players
					</p>
						[returnShutdownButton]
					<hr>
					<div class=\"row marg-auto\">
						[returnServerPlayers]
					</div>
				</div>
			</div>
		</div>
	</div>
	`;
	
	multiPageHelper("serversList", [], "https://api.alphaland.cc/game/jobList", "https://api.alphaland.cc/logo", "#active-server-list", "#page-buttons-here", html, page, 10, "", "No servers available.", "&placeid="+getparam);
}

function setupPage() {
	getJSONCDS("https://api.alphaland.cc/game/info?id="+getparam)
	.done(function(jsonData) {
		var data = jsonData;

		$("#main-game-info-element").show();

		var html = `
		<div class="col-sm-9 marg-bot-15">
		<div class="card marg-auto">
		<div class="card-body">
		<div class="row mb-2">
		<div class="col-sm" style="overflow:hidden;">
		<h5>{Name}</h5>
		</div>
		`;
		
		if (data.canManage) {
			if (data.isPersonalServer) {
				html += `
				<div class="col-sm">
				<a style="float:right;" class="btn btn-danger text-white" href="pbs/config?id={id}">Configure</a>
				</div>
				`;
			} else {
				html += `
				<div class="col-sm">
				<a style="float:right;" class="btn btn-danger text-white" href="config?id={id}">Configure</a>
				</div>
				`;
			}
		}
										
		html += `
		</div>
		<div class="card marg-bot-15">
		<div class="card-body">
		<img class="card-img game-thumb" src="{placeThumbnail}">
		</div>
		</div>
		<h5>Game Description</h5>
		<hr>
		<div style="overflow:hidden;">
		<p>{Description}</p>
		</div>
		</div>
		</div>
		</div>
		<div class="col-sm-3 marg-bot-15">
		<div class="card">
		<div class="card-body marg-auto">
		<div class="card marg-bot-15" style="width:12rem;">
		<a href="/profile/view?id={CreatorId}">
		<div class="card-body">
		<img class="card-img" src="{creatorThumbnail}">
		</div>
		</a>
		</div>
		<div class="text-center">
		<p>Creator: <a class="red-a" href="/profile/view?id={CreatorId}">{Creator}</a></p>
		</div>
		<hr>
		<div class="text-center">
		<h6>Game Stats</h6>
		</div>
		<div class="row mt-2">
		<div class="col-sm">
		<b>Place Visits:</b>
		<p>{Visits}</p>
		<b>Max Players:</b>
		<p>{MaxPlayers}</p>
		<b>Created:</b>
		<p>{Created}</p>
		</div>
		</div>
		<div class="row mt-2">
		<div class="col-sm text-center">
		`;
			
		if (data.playPermission) {				
			html += `<button onclick="playGame({id})" type="button" class="btn btn-lg btn-danger marg-auto" style="width:12rem!important;">Play <i class="fas fa-caret-right"></i></button>`;
		} else {
			html += `This game is private.`;
		}
				
		html += `
		</div>
		</div>
		</div>
		</div>
		</div>
		`;

		$("#game-general-info").html(parseHtml(html, 1, jsonData, "Error occurred", true));
	});
}

setupPage();
serversList(1);
comments = new Comments(getparam, "#comments_html", "#comments-container", "#page-buttons", "#success_alert", "#error_alert", "#comment_input", 2000, "comments")
</script>

';

pageHandler();
$ph->pageTitle($gameName);
$ph->body = $body;
$ph->output();