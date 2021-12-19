<?php

use Alphaland\Web\WebContextManager;

WebContextManager::ForceHttpsCloudflare();

if(!($user->IsAdmin())) {
	die('bababooey');
}

adminPanelStats();

$alert = "";
$generated_key = "";
if(isset($_POST['Submit'])) 
{
	$generated_key = genSignupKey();
	if (!empty($generated_key))
	{
		$alert = "<div class='alert alert-success' role='alert'>Generated</div>";
	}
}

$body = <<<EOT
<div class="container text-center">
{$alert}
	<h5>Generate Key</h5>
	<div class="card" style="max-width: 38rem;margin: auto;">
		<div class="card-body">
			<form method="post">
				<div class="row">
					<div class="col-sm">
						<div class="input-group">
							<form action="" method="post">
								<input type="text" name="generatedkey" class="form-control" value="{$generated_key}" readonly>	
								<button type="Submit" name="Submit" class="btn btn-success" type="button">Generate</button>
							</form>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
EOT;

pageHandler();
$ph->pagetitle = ""; 
$ph->navbar = "";
$ph->body = $body;
$ph->footer = "";
$ph->output();