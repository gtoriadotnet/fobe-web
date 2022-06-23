<?php

use Finobe\Users\Render;
use Finobe\Web\WebContextManager;

WebContextManager::ForceHttpsCloudflare();

if(!($user->IsAdmin())) {
	die('bababooey');
}

adminPanelStats();

if(isset($_POST['renderplayer']))
{
	Render::RenderPlayer($_POST['userid']);
}

$body = <<<EOT
<div class="container text-center">
	<h5>Render Management</h5>
	<form action="" method="post">
		<div class="card" style="max-width: 38rem;margin: auto;">
			<div class="card-body">
				<input type="text" name="userid" class="form-control" placeholder="User ID">
				<br>
				<button type="Submit" name="renderplayer" class="btn btn-lg btn-success w-100" type="button">Re-Render player</button>
			</div>
		</div>
	</form>
</div>
EOT;

pageHandler();
$ph->pagetitle = ""; 
$ph->navbar = "";
$ph->body = $body;
$ph->footer = "";
$ph->output();