<?php

if (isLoggedIn())
{
	redirect("/");
}

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