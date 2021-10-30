<?php

$body = <<<EOT
<div class="container-fluid">
	<div class="container">
		<div class="card">
			<div class="card-body text-center">
				<img style="width: 15rem;" src="https://api.alphaland.cc/logo">
				<h2 class="mt-3">404 | Page not found</h2>
				<hr>
				<a onclick="javascript:history.back()"><button class="btn btn-danger">Back</button></a><h> </h><a onclick="location.href = '/';""><button class="btn btn-danger">Home</button></a>
			</div>
		</div>
	</div>
</div>
EOT;

pageHandler();
if(!isLoggedIn())
{
	$ph->footer = ""; //no footer for no login
}
$ph->body = $body;
$ph->output();