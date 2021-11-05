<?php

if (isLoggedIn())
{
	redirect("/");
}

$alert = '';
if(isset($_POST['Submit'])) 
{
}

$body = <<<EOT
<div class="container">
{$alert}
	<form action="" method="post">
		<div class="card m-auto" style="max-width: 40rem;">
			<div class="card-body text-center" style="padding: 2rem;">
				<h5>2-Step Verification</h5>
				<h6>If you cannot login, please contact an Administrator on Discord</h6>
				<div class="row mb-3">
					<input class="form-control" type="text" name="2facode" placeholder="Code">
				</div>
				<div class="row">
					<button type="Submit" name="Submit" class="btn btn-danger m-auto">Submit</button>
				</div>
			</div>
		</div>
	</form>
</div>
EOT;

pageHandler();
$ph->footer = "";
$ph->pageTitle("2-Step");
$ph->body = $body;
$ph->output();