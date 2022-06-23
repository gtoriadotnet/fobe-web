<?php

use Finobe\Games\Game;
use Finobe\Web\WebContextManager;

$body = '';

$gameid = (int)$_GET['id'];

if ($gameid)
{
	if(getAssetInfo($gameid)->isPersonalServer == 0)
	{
		WebContextManager::Redirect("/games/config?id=".$gameid);
	}
	
	if (!is_int($gameid) || getAssetInfo($gameid)->AssetTypeId != 9 || getAssetInfo($gameid)->isPersonalServer != 1)
	{
		http_response_code(404);
	}
}
else
{
	http_response_code(404);
}

if (isset($_POST['ConvertToRegular']))
{
	if (Game::CloseAllJobs($gameid))
	{
		if (Game::SetToPlace($gameid))
		{
			handleRenderPlace($gameid);
			WebContextManager::Redirect("/games/config?id=".$gameid);
		}	
		else
		{
			Game::SetToPersonalBuildPlace($gameid);
		}
	}
}

	
	$body = <<<EOT
	<div class="container">
	<div id = "error_alert" class="alert alert-danger" role="alert" style="display:none;"></div>
			<div class="row" id="pbs_config_main" style="display:none;">
				<div class="col-sm">
					<div class="card">
						<div class="card-body">
							<div class="row">
								<div class="col-sm">
									<div class="row mb-2">
										<div class="col-sm">
											<h5>Configure Personal Build Server</h5>
										</div>
										<div class="col-sm-2">
											<btn class="btn btn-danger w-100 float-right" data-toggle="modal" data-target="#converttogame">Convert to Game</btn>
										</div>
									</div>
									<div class="container text-center marg-bot-15">
										<label style="float:left;">Place Name</label>
										<input class="form-control" type="text" id="pbs_game_name" value="">
									</div>
									<div class="text-center">
										<img class="img-fluid" style="width:40rem;height:20rem;" id="pbs_game_render" src="">
									</div>
									<div class="container text-center marg-bot-15">
										<label style="float:left;text-align:top;">Description</label>
										<textarea style="min-height:10rem;max-height:10rem;" class="form-control" type="text" name="description" id="pbs_game_description"></textarea>
									</div>
									<div class="container text-center" container text-center marg-bot-15>
										<div class="custom-control custom-checkbox custom-control-inline">
											<input type="checkbox" name="comments_checkbox" class="custom-control-input" autocomplete="off" id="pbs_game_commentsenabled">
											<label class="custom-control-label" for="pbs_game_commentsenabled">Comments Enabled</label>
										</div>
										<div class="custom-control custom-checkbox custom-control-inline">
											<input type="checkbox" name="pbs_private_checkbox" class="custom-control-input" autocomplete="off" id="pbs_game_privateenabled">
											<label class="custom-control-label" for="pbs_game_privateenabled">Whitelist Only</label>
										</div>
									</div>
									<div class="container text-center marg-bot-15">
									<label for="playerrange" style="float:left;text-align:top;">Max Players</label>
										<input class="form-control-range custom-range" min="1" max="12" name="pbs_max_players[1]" id="pbs_max_players1" value="" step="1" type="range" name="pbsplacemaxplayers" oninput="pbs_max_players_val.value = pbs_max_players1.value" autocomplete="off">
										<output id="pbs_max_players_val" class="output" style="font-size:18px;"></output>
										<datalist id="ticks">
											<option>1</option>
											<option>2</option>
											<option>3</option>
											<option>4</option>
											<option>5</option>
											<option>6</option>
											<option>7</option>
											<option>8</option>
											<option>9</option>
											<option>10</option>
											<option>11</option>
											<option>12</option>
										</datalist>
									</div>
									<div class="container" id="whitelisted_users_box" style="display:none;">
										<hr>
										<div class="container text-center">
											<h5>Whitelisted Users</h5>
										</div>
										<div class="card">
											<div class="card-body">
												<div class="row mb-2">
													<div class="col-sm">
														<label>Whitelist User</label>
														<div class="input-group">
															<input type="text" class="form-control btngrp-appendbadfix" autocomplete="off" placeholder="Username" id="pbs_whitelist_user_input">
															<div class="input-group-append">
																<button class="btn btn-success" type="button" onclick="whitelistUser($('#pbs_whitelist_user_input').val())">Add</button>
															</div>
														</div>
													</div>
												</div>
												<div class="row mb-2">
													<div class="col-sm">
														<div class="pers-servbox">
															<ul id="whitelisted_users_html">

															</ul>
														</div>
													</div>
												</div>
												<div class="text-center mt-2">
													<div id="whitelisted_users_button" class="btn-group game-page-btn-group m-auto">
															
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="container">
									<hr>
									<div class="container text-center">
										<h5>Banned Users</h5>
									</div>
										<div class="card">
											<div class="card-body">
											<div class="row mb-2">
													<div class="col-sm">
														<label>Ban User</label>
														<div class="input-group">
															<input type="text" class="form-control btngrp-appendbadfix" autocomplete="off" placeholder="Username" id="pbs_ban_user_input">
															<div class="input-group-append">
																<button class="btn btn-danger" type="button" onclick="banUsername($('#pbs_ban_user_input').val())">Ban</button>
															</div>
														</div>
													</div>
												</div>
												<div class="row mb-2">
													<div class="col-sm">
														<div class="pers-servbox">
															<ul id="banned_users_html">

															</ul>
														</div>
													</div>
												</div>
												<div class="text-center mt-2">
													<div id="banned_users_buttons" class="btn-group game-page-btn-group m-auto">
														
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="container">
									<hr>
									<div class="container text-center">
										<h5>User Roles</h5>
									</div>
										<div class="card">
											<div class="card-body">
												<div class="row mb-2">
													<div class="col-sm">
														<label>Grant Permissions</label>
														<div class="input-group">
															<input type="text" class="form-control btngrp-appendbadfix" autocomplete="off" placeholder="Username" id="pbs_username_rank">
															<div class="input-group-append">
																<button class="btn btn-success" type="button" data-toggle="dropdown" style="border-top-left-radius: 0!important;border-bottom-left-radius: 0!important;border-radius: .25rem;">Role</button>
																<ul class="bootstrap-dropdown-parent dropdown-menu" style="overflow:hidden;max-width:5rem;" id="roledropdown">
																	<li class="dropdown-custom bootstrap-toggle-dropdown w-100" onclick="rankUsername($('#pbs_username_rank').val(),240)">Admin</li>
																	<li class="dropdown-custom bootstrap-toggle-dropdown w-100" onclick="rankUsername($('#pbs_username_rank').val(),128)">Member</li>
																</ul>
															</div>
														</div>
													</div>
												</div>
												<div class="row mb-2">
													<div class="col-sm">
														<div class="pers-servbox">
															<ul id="user_roles_html">

															</ul>
														</div>
													</div>
												</div>
												<div class="text-center mt-2">
													<div id="user_roles_buttons" class="btn-group game-page-btn-group m-auto">
													
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="container text-center">
										<hr>
										<button type="button" class="btn btn-danger w-100" onclick="updatePBSGen()">Update Place</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal fade" id="converttogame" tabindex="-1" role="dialog" aria-labelledby="converttogameLabel" aria-hidden="true">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Convert to Regular Game</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<h5>WARNING:</h5>
						<p>Converting back to a regular game will remove all auto-saving capabilities, removes the tools and will shut down all open Servers.</p>
					</div>
						<div class="modal-footer">
							<form action="" method="post">
								<button name="ConvertToRegular" class="btn btn-danger"><b>Confirm</b></button>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<script>
//global vars
var gameid = new URLSearchParams(window.location.search).get('id');

function updatePBSGen()
{
	var commentsenabled = false;
	var whitelistenabled = false;

	if ($('#pbs_game_commentsenabled').is(":checked"))
	{
		commentsenabled = true;
	}

	if ($('#pbs_game_privateenabled').is(":checked"))
	{
		whitelistenabled = true;
	}

	postJSONCDS("https://api.idk16.xyz/game/pbs/configure?id="+gameid+"&updatesettings=true", JSON.stringify({
	"Name": $("#pbs_game_name").val(), 
	"Description": $("#pbs_game_description").val(), 
	"CommentsEnabled": commentsenabled, 
	"WhitelistEnabled": whitelistenabled,
	"MaxPlayers": $("#pbs_max_players_val").val()
	}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "PBS Updated") {
			initialize();
		}
		else
		{
			$(messageid).text(alert);
			$(messageid).show();
			window.scrollTo({top: 0, behavior: "smooth"});
			setTimeout(function() 
			{
				$(messageid).hide();
			}, 3000);
		}
	});
}

function whitelistUser(username)
{
	updatePBSGen();
	postJSONCDS("https://api.idk16.xyz/game/pbs/configure?id="+gameid+"&whitelist=true", JSON.stringify({"username": username}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "PBS Updated") {
			initialize();
		}
		else
		{
			$(messageid).text(alert);
			$(messageid).show();
			window.scrollTo({top: 0, behavior: "smooth"});
			setTimeout(function() 
			{
				$(messageid).hide();
			}, 3000);
		}	
	});
}

function unwhitelistUser(userid)
{
	postJSONCDS("https://api.idk16.xyz/game/pbs/configure?id="+gameid+"&unwhitelist=true", JSON.stringify({"userid": userid}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "PBS Updated") {
			initialize();
		}
		else
		{
			$(messageid).text(alert);
			$(messageid).show();
			window.scrollTo({top: 0, behavior: "smooth"});
			setTimeout(function() 
			{
				$(messageid).hide();
			}, 3000);
		}	
	});
}

function removeUser(userid)
{
	postJSONCDS("https://api.idk16.xyz/game/pbs/configure?id="+gameid+"&remove=true", JSON.stringify({"userid": userid}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "PBS Updated") {
			initialize();
		}
		else
		{
			$(messageid).text(alert);
			$(messageid).show();
			window.scrollTo({top: 0, behavior: "smooth"});
			setTimeout(function() 
			{
				$(messageid).hide();
			}, 3000);
		}	
	});
}

function banUsername(username)
{
	rankUsername(username, 0);
}

function rankUsername(username, rank)
{
	postJSONCDS("https://api.idk16.xyz/game/pbs/configure?id="+gameid+"&rank=true", JSON.stringify({"username": username, "rank": rank}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "PBS Updated") {
			initialize();
		}
		else
		{
			$(messageid).text(alert);
			$(messageid).show();
			window.scrollTo({top: 0, behavior: "smooth"});
			setTimeout(function() 
			{
				$(messageid).hide();
			}, 3000);
		}	
	});
}

function rankUser(userid, rank)
{
	postJSONCDS("https://api.idk16.xyz/game/pbs/configure?id="+gameid+"&rank=true", JSON.stringify({"userid": userid, "rank": rank}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "PBS Updated") {
			initialize();
		}
		else
		{
			$(messageid).text(alert);
			$(messageid).show();
			window.scrollTo({top: 0, behavior: "smooth"});
			setTimeout(function() 
			{
				$(messageid).hide();
			}, 3000);
		}	
	});
}

function whitelistedUsersPage(page)
{
	getWhitelistedUsersPage(page, 5)
}
function getWhitelistedUsersPage(page, limit)
{
	var html = '<li>';
	html += '<div class="card">';
	html += '<div class="card-body text-center">';
	html += '<a class="red-a" href="/profile/view?id={userid}">';
	html += '<img class="img-fluid" src="{thumbnail}">';
	html += '<p class="no-overflow">{username}</p>';
	html += '</a>';
	html += '<button class="btn btn-sm btn-danger w-100 mt-1" onclick="unwhitelistUser({userid})">Remove</button>';
	html += '</div>';
	html += '</div>';
	html += '</li>';
	
	multiPageHelper("whitelistedUsersPage", [], "https://api.idk16.xyz/game/pbs/users", "https://api.idk16.xyz/logo", "#whitelisted_users_html", "#whitelisted_users_button", html, page, limit, "", "No whitelisted users", "&id="+gameid+"&whitelist=true"); //show all ranks besides banned
}

function userRolesPage(page)
{
	getUserRolesPage(page, 5)
}
function getUserRolesPage(page, limit)
{
	var html = '<li>';
	html += '<div class="card">';
	html += '<div class="card-body text-center">';
	html += '<a class="red-a" href="/profile/view?id={userid}">';
	html += '<img class="img-fluid" src="{thumbnail}">';
	html += '<p class="no-overflow">{username}</p>';
	html += '</a>';
	html += '<div class="mb-2 mt-2 dropdown">';
	html += '<button class="dropdown-custom dropdown" type="button" data-toggle="dropdown">{rankname}</button>';
	html += '<ul class="bootstrap-dropdown-parent dropdown-menu w-100" style="overflow:hidden;" id="roledropdown">';
	html += '<li class="dropdown-custom bootstrap-toggle-dropdown w-100" onclick="rankUser({userid},240)">Admin</li>';
	html += '<li class="dropdown-custom bootstrap-toggle-dropdown w-100" onclick="rankUser({userid},128)">Member</li>';
	html += '<li class="dropdown-custom bootstrap-toggle-dropdown w-100" onclick="rankUser({userid},0)">Banned</li>';
	html += '</ul>';
	html += '</div>';
	html += '<button type="button" class="btn btn-sm btn-danger w-100 mt-1" onclick="removeUser({userid})">Remove</button>';
	html += '</div>';
	html += '</div>';
	html += '</li>';
	
	multiPageHelper("userRolesPage", [], "https://api.idk16.xyz/game/pbs/users", "https://api.idk16.xyz/logo", "#user_roles_html", "#user_roles_buttons", html, page, limit, "", "No users", "&id="+gameid+"&nobanned=true"); //show all ranks besides banned
}

function bannedMembersPage(page)
{
	getBannedMembersPage(page, 5)
}
function getBannedMembersPage(page, limit)
{
	var html = '<li>';
	html += '<div class="card">';
	html += '<div class="card-body text-center">';
	html += '<a class="red-a" href="/profile/view?id={userid}">';
	html += '<img class="img-fluid" src="{thumbnail}">';
	html += '<p class="no-overflow">{username}</p>';
	html += '</a>';
	html += '<button class="btn btn-sm btn-danger w-100 mt-1" onclick="removeUser({userid})">Pardon</button>';
	html += '</div>';
	html += '</div>';
	html += '</li>';
	
	multiPageHelper("bannedMembersPage", [], "https://api.idk16.xyz/game/pbs/users", "https://api.idk16.xyz/logo", "#banned_users_html", "#banned_users_buttons", html, page, limit, "", "No banned users", "&id="+gameid+"&excluderank=10"); //will show only ranks below 10
}

//initialize
function initialize()
{
	getJSONCDS("https://api.idk16.xyz/game/info?id=" + gameid)
	.done(function(jsonData) {
		var data = jsonData;
		$("#pbs_game_name").val(data.Name);
		$("#pbs_game_description").val(data.Description);
		$("#pbs_max_players_val").html(data.MaxPlayers);
		$("#pbs_max_players1").val(data.MaxPlayers);
		$("#pbs_game_commentsenabled").prop('checked', data.CommentsEnabled);

		if (data.PersonalServerWhitelist)
		{
			$("#whitelisted_users_box").show();
			$("#pbs_game_privateenabled").prop('checked', data.PersonalServerWhitelist);
		}
		
		$("#pbs_game_render").attr("src", data.placeThumbnail);
		$("#pbs_config_main").show();
	});
	whitelistedUsersPage(1);
	bannedMembersPage(1);
	userRolesPage(1);
}

$("#pbs_game_privateenabled").change(function() {
    if(this.checked) {
		$("#whitelisted_users_box").show();
    } else {
		$("#whitelisted_users_box").hide();
	}
});

initialize();
</script>
EOT;

pageHandler();
$ph->body = $body;
$ph->pageTitle("PBS Config");
$ph->output();