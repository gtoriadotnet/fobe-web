<?php
$activation = new Alphaland\Users\Activation();

if ($activation->isUserActivated($user->id)) {
	redirect("/");
}

$activationcode = $activation->getUserActivationCode($user->id);

$body = '
<div class="container-fluid" style="display: flex;justify-content: center;align-items: center;text-align: center;min-height: 100vh;">
	<div class="container">
		<div class="row marg-bot-15">
			<div class="col-sm-12 marg-bot-15">
				<div class="card marg-auto">
					<div class="card-body">
						<h4>Activation Required</h4>
						<hr>
						<h5>To play Alphaland, you must activate this account in the <a href=https://discord.gg/RDj4aVh8VS>Discord Server</a>.</h5>
						<h5>After joining, send the activation code below in the activation channel and refresh this page.</h5>
						<hr>
						<code>'.$activationcode.'</code>
						<hr>
						<a href="logout"><button class="btn btn-sm btn-danger mb-1">Logout</button></a>
					</div>
				</div>
			</div>	
		</div>
	</div>
</div>';
	
pageHandler();
$ph->pageTitle("Activate");
$ph->footer = "";
$ph->navbar = "";
$ph->body = $body;
$ph->output();