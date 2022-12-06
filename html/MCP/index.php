<?php

use Fobe\Web\WebContextManager;

if(!($user->IsStaff())) 
{
    WebContextManager::Redirect("/");
}
$body = <<<EOT
<h5 class="text-center">Moderation Control Panel</h5>
<div class="container text-center">
	<div class="row">
		<div class="col-sm">
			<div class="card marg-bot-30">
				<div class="card-body text-center">
					<a class="red-a-nounder" href="user-management"><p style="font-size:10rem;"><i class="fas fa-user-shield"></i></p></a>
					<a class="red-a-nounder" href="user-management"><h5>User Management</h5></a>
				</div>
			</div>
		</div>
		<div class="col-sm">
			<div class="card marg-bot-30">
				<div class="card-body">
					<a class="blue-a-nounder" href="asset-management"><p style="font-size:10rem;"><i class="fab fa-redhat"></i></p></a>
					<a class="blue-a-nounder" href="asset-management"><h5>Asset Approval</h5></a>
				</div>
			</div>
		</div>
		<div class="col-sm">
			<div class="card marg-bot-30">
				<div class="card-body text-center">
					<a class="green-a-nounder" href="chat-logs"><p style="color:#3ffa42;font-size:10rem;"><i class="fas fa-hammer"></i></p></a>
					<a class="green-a-nounder" href="chat-logs"><h5>Chat Logs</h5></a>
				</div>
			</div>
		</div>
		<div class="col-sm">
			<div class="card marg-bot-30">
				<div class="card-body text-center">
					<a class="red-a-nounder" href="invite-logs"><p style="font-size:10rem;"><i class="fas fa-user-shield"></i></p></a>
					<a class="red-a-nounder" href="invite-logs"><h5>Invite Logs</h5></a>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm">
			<div class="card marg-bot-30">
				<div class="card-body text-center">
					<a class="red-a-nounder" href="reports"><p style="font-size:10rem;"><i class="fas fa-user-shield"></i></p></a>
					<a class="red-a-nounder" href="reports"><h5>Reports</h5></a>
				</div>
			</div>
		</div>
	</div>
</div>
EOT;
pageHandler();
$ph->pageTitle("Moderation");
$ph->body = $body;
$ph->output();
?>