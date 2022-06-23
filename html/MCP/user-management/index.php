<?php

use Finobe\Web\WebContextManager;

$body = '';
if(!($user->IsStaff())) {
    WebContextManager::Redirect("/");
}

$body = <<<EOT
<div class="container text-center">
    <div id = "error_alert" class="alert alert-danger" role="alert" style="display:none;"></div>
	<div id = "success_alert" class="alert alert-success" role="alert" style="display:none;"></div>
    <h5>User Management</h5>
	<h6><b>PLEASE MAKE SURE YOU READ THE DISCORD CHANNEL "staff-resources"</b></h6>
    <div class="card" style="max-width: 38rem;margin: auto;">
        <div class="card-body">
            <div class="row">
                <div class="col-sm">
                    <div class="input-group">
                        <input type="text" name="banuser" class="form-control" id="ban_username" placeholder="Username" autocomplete="off">
                        <input type="text" name="banreason" class="form-control" id="ban_reason" placeholder="Moderation Reason" autocomplete="off">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-danger" type="button" onclick="banUser()">Moderate</button>
                        </div>
                    </div>
                </div>
            </div>
            <br>
            <div title="Warn the account, can be immediately reactivated" class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" name="warning_checkbox" class="custom-control-input sev_check" id="warning" autocomplete="off">
                <label class="custom-control-label" for="warning">Warning</label>
            </div>
            <div title="Ban the account for 1 day" class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" name="temp_checkbox" class="custom-control-input sev_check" id="temp" autocomplete="off">
                <label class="custom-control-label" for="temp">Temporary (1 day)</label>
            </div>
            <div title="Permanently bans the account" class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" name="perm_checkbox" class="custom-control-input sev_check" id="perm" autocomplete="off">
                <label class="custom-control-label" for="perm">Permanent</label>
            </div>
			<hr>
            <div title="Permanently ban all accounts associated with the accounts ip address" class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" name="poison_perm_checkbox" class="custom-control-input sev_check" id="poison_perm" autocomplete="off">
                <label class="custom-control-label" for="poison_perm">Poison Ban (Permanent)</label>
            </div>
            <div title="Permanently ban the user and every user they invited" class="custom-control custom-checkbox custom-control-inline">
                <input type="checkbox" name="limb_perm_checkbox" class="custom-control-input sev_check" id="limb_perm" autocomplete="off">
                <label class="custom-control-label" for="limb_perm">Limb Ban (Permanent)</label>
            </div>
            <hr>
            <div class="row">
                <div class="col-sm">
                    <div class="input-group">
                        <input type="text" name="unbanuser" class="form-control" id="unban_username" placeholder="Username" autocomplete="off">
                        <div class="input-group-append">	
                            <button type="submit" class="btn btn-success" type="button" onclick="unbanUser()">Unban</button>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>
                    <button class="btn btn-danger w-100" type="button" data-toggle="collapse" data-target="#banlisttemp" aria-expanded="false" aria-controls="banlisttemp" onclick="getBanlist()">Banlist</button>
                </p>
                <div class="collapse" id="banlisttemp">
                    <table class="table atable-dark">
                        <thead>
                            <tr>
								<th>Moderator</th>
                                <th>Date</th>
                                <th>Username</th>
                                <th>Reason</th>
                                <th>Expiration</th>
                            </tr>
                        </thead>
                        <tbody id="user_ban_list">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

$('.sev_check').click(function() {
    $('.sev_check').not(this).prop('checked', false);
 });

function showError(message)
{
	$("#error_alert").text(message);
	$("#error_alert").show();
			
	setTimeout(function() 
	{
		$("#error_alert").hide();
	}, 3000);
}

function showSuccess(message)
{
	$("#success_alert").text(message);
	$("#success_alert").show();
			
	setTimeout(function() 
	{
		$("#success_alert").hide();
	}, 3000);
}

function unbanUser()
{
	var unbanusername = $('#unban_username').val();

	if (!unbanusername) {
		showError("Missing Username");
		return;
	}

	postJSONCDS("https://www.idk16.xyz/MCP/user-management/unban", JSON.stringify({
		"username":unbanusername
	}))
	.done(function(object) {
		if (!object.success) {
			showError("Failed to unban user");
		} else {
			showSuccess("Unbanned User");
			getBanlist();
		}
	});
}

function banUser()
{
	var expiration = 0;
	var type = "";

	if ($('#warning').prop('checked')) {
		type = "warn"
	} else if ($('#temp').prop('checked')) {
		expiration = Math.floor(Date.now() / 1000) + 86400;
		type = "temp"
	} else if ($('#perm').prop('checked')) {
		type = "perm"
	} else if ($('#poison_perm').prop('checked')) {
		type = "poison"
	} else if ($('#limb_perm').prop('checked')) {
		type = "limb"
	} else {
		showError("No ban type chosen");
		return;
	}

	var banusername = $('#ban_username').val();
	var banreason = $('#ban_reason').val();

	if (!banusername || !banreason) {
		showError("Missing parameters");
		return;
	}

	postJSONCDS("https://www.idk16.xyz/MCP/user-management/ban", JSON.stringify({
		"username":banusername,
		"reason":banreason,
		"expiration":expiration,
		"type":type	
	}))
	.done(function(object) {
		if (!object.success) {
			showError("Failed to moderate user");
		} else {
			showSuccess("Moderated User");
			getBanlist();
		}
	});
}

function getBanlist()
{
	html = `<tr>
		<td>{whoBannedUser}</td>
		<td>{whenBanned}</td>
		<td>{bannedUser}</td>
		<td>{banReason}</td>
		<td>{banExpiration}</td>
	</tr>`;
		 
	staticPageHelper("https://www.idk16.xyz/MCP/user-management/banlist", "", "#user_ban_list", html, "", 100, "", "");
}

</script>
EOT;

pageHandler();
$ph->pageTitle("User Manage");
$ph->body = $body;
$ph->output();