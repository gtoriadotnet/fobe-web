<?php

use Alphaland\Web\WebContextManager;

WebContextManager::ForceHttpsCloudflare();

if(!($user->isAdmin())) {
	die('bababooey');
}

//die (phpinfo());
adminPanelStats();

$body = '
<div class="container-fluid" style="margin-bottom:30px;">
	<div class="container text-center">
		<h5>Admin Control Panel</h5>
		<div class="row">
			<div class="col-sm">
				<div class="card marg-bot-30">
					<div class="card-body text-center">
						<a class="purple-a-nounder" href="create-signupkey"><p style="color:#933ffa;font-size:10rem;"><i class="fas fa-lock-open"></i></p></a>
						<a class="purple-a-nounder" href="create-signupkey"><h5>Create Signup Key</h5></a>
					</div>
				</div>
			</div>
			<div class="col-sm">
				<div class="card marg-bot-30">
					<div class="card-body text-center">
						<a class="green-a-nounder" href="create-asset"><p style="color:#3ffa42;font-size:10rem;"><i class="fas fa-hammer"></i></p></a>
						<a class="green-a-nounder" href="create-asset"><h5>Create Asset</h5></a>
					</div>
				</div>
			</div>
			<div class="col-sm">
				<div class="card marg-bot-30">
					<div class="card-body text-center">
						<a class="red-a-nounder" href="give-asset"><p style="color:#dc3545;font-size:10rem;"><i class="fas fa-plus"></i></p></a>
						<a class="red-a-nounder" href="give-asset"><h5>Give Asset</h5></a>
					</div>
				</div>
			</div>
			<div class="col-sm">
				<div class="card marg-bot-30">
					<div class="card-body text-center">
						<a class="blue-a-nounder" href="announcements"><p style="color:#3f7dfa;font-size:10rem;"><i class="fas fa-volume-up"></i></p></a>
						<a class="blue-a-nounder" href="announcements"><h5>Announcements</h5></a>
					</div>
				</div>
			</div>
			<div class="w-100"></div>
			<div class="col-sm">
				<div class="card marg-bot-30">
					<div class="card-body text-center">
						<a class="yellow-a-nounder" href="configuration"><p style="color:#fae13f;font-size:10rem;"><i class="fas fa-cogs"></i></p></a>
						<a class="yellow-a-nounder" href="configuration"><h5>Configuration</h5></a>
					</div>
				</div>
			</div>
			<div class="col-sm">
				<div class="card marg-bot-30">
					<div class="card-body text-center">
						<a class="cyan-a-nounder" href="rendermanagement"><p style="color:#03f8fc;font-size:10rem;"><i class="fas fa-vector-square"></i></p></a>
						<a class="cyan-a-nounder" href="rendermanagement"><h5>Render Management</h5></a>
					</div>
				</div>
			</div>
			<div class="col-sm">
				<div class="card marg-bot-30">
					<div class="card-body text-center">
						<a class="orange-a-nounder" href="client-deployer"><p style="color:#f5821f;font-size:10rem;"><i class="fas fa-plus"></i></p></a>
						<a style="color:#f5821f;" href="client-deployer"><h5>Deployment</h5></a>
					</div>
				</div>
			</div>
			<div class="col-sm">
				<div class="card marg-bot-30">
					<div class="card-body text-center">
						<a class="red-a-nounder" href="rank-management"><p style="font-size:10rem;"><i class="fas fa-user-shield"></i></p></a>
						<a class="red-a-nounder" href="rank-management"><h5>Rank Management</h5></a>
					</div>
				</div>
			</div>
			<div class="w-100"></div>
			<div class="col-sm">
				<div class="card marg-bot-30">
					<div class="card-body text-center">
						<a class="orange-a-nounder" href="/lua-executer/"><p style="color:#f5821f;font-size:10rem;"><i class="fas fa-plus"></i></p></a>
						<a style="color:#f5821f;" href="/lua-executer/"><h5>Lua Executer</h5></a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
';

pageHandler();
$ph->pagetitle = ""; 
$ph->navbar = "";
$ph->body = $body;
$ph->footer = "";
$ph->output();