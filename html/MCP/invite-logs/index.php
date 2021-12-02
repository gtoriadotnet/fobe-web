<?php

use Alphaland\Web\WebContextManager;

if(!$user->isStaff())
{
    WebContextManager::Redirect("/");
}

$alert = '';
	
$body = <<<EOT
<div class="container">
	<div class="row">
		<div class="col-sm">
			<div class="card">
				<div class="card-body">
				<h4>User Invitation Logs</h4>
					<div class="card-body">
						<table class="table atable-dark">
							<thead>
								<tr>
									<th>Invited</th>
									<th>whoInvited</th>
									<th>whenAccepted</th>
								</tr>
							</thead>
							<tbody id="invite_logs">
								
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
function activeKeys()
{
	var html = '<tr>';
	html += '<td>{invitedUsername} ({invitedUser})</td>';
	html += '<td>{whoInvitedUsername} ({whoInvited})</td>';
	html += '<td>{whenAccepted}</td>';
	html += '</tr>';
		
	staticPageHelper("https://www.alphaland.cc/MCP/invite-logs/inviteLogs", "", "#invite_logs", html, "", 999999999999, "", "");
}
activeKeys();
</script>

EOT;

pageHandler();
$ph->pageTitle("Invite Logs");
$ph->body = $body;
$ph->output();