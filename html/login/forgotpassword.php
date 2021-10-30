<?php

$alert = '';
if(isset($_POST['Submit'])) 
{
	$username = cleanInput($_POST['username']);
	$email = cleanInput($_POST['email']);
	$userdetails = $pdo->prepare("SELECT * FROM users WHERE email = :e AND username = :u");
	$userdetails->bindParam(":e", $email, PDO::PARAM_STR);
	$userdetails->bindParam(":u", $username, PDO::PARAM_STR);
	$userdetails->execute();

	if ($userdetails->rowCount() > 0)
	{
		$userdetails2 = $userdetails->fetch(PDO::FETCH_OBJ);
		$userid = (int)$userdetails2->id;
		$resetstatus = sendPasswordReset("info@alphaland.cc", $email, $userid);
		
		if ($resetstatus == 1)
		{
			$alert = "<div class='alert alert-success' role='alert'>Check your email!</div>";
		}
		elseif ($resetstatus == 2)
		{
			$alert = "<div class='alert alert-danger' role='alert'>Please wait before attempting again</div>";
		}
		elseif ($resetstatus == 3)
		{
			$alert = "<div class='alert alert-danger' role='alert'>An error occurred, contact an admin</div>";
		}
		
	}
	else
	{
		$alert = "<div class='alert alert-danger' role='alert'>No account found</div>";
	}
}

$body = <<<EOT
<div class="container">
{$alert}
	<form action="" method="post">
		<div class="card m-auto" style="max-width: 40rem;">
			<div class="card-body text-center" style="padding: 2rem;">
				<div class="row">
					<img class="img-fluid m-auto" src="../alphaland/cdn/imgs/forgot-password-1024.png" style="width: 20rem;">
				</div>
				<div class="row mb-2">
					<strong class="m-auto">Forgot password? use the form below for a password reset link!</strong>
				</div>
				<div class="row mb-3">
					<input class="m-auto form-control" type="text" name="username" placeholder="Username">
				</div>
				<div class="row mb-3">
					<input class="m-auto form-control" type="text" name="email" placeholder="User Email">
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
$ph->pageTitle("Forgot Password");
$ph->body = $body;
$ph->output();