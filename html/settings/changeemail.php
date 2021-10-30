<?php

$alert = '';

if(isset($_POST['Submit'])) 
{
	if($_POST['email'] == $_POST['cemail']) //if email and confirm email matches
	{
		if(FILTER_VAR($_POST['email'], FILTER_VALIDATE_EMAIL) == false) 
		{
    		$alert = "<div class='alert alert-danger' role='alert'>Invalid email provided</div>";
    	}
		else
		{
			$password = cleanInput($_POST['password']);
			if(passwordCorrect($user->id, $password)) 
			{
				$changeemail = changeEmail($_POST['email']);
				
				if ($changeemail == 1)
				{
					$alert = "<div class='alert alert-success' role='alert'>Email reset, check your email</div>";
				}
				elseif ($changeemail == 2)
				{
					$alert = "<div class='alert alert-danger' role='alert'>Please wait before attempting again</div>";
				}
				elseif ($changeemail == 3)
				{
					$alert = "<div class='alert alert-danger' role='alert'>An error occurred, contact an admin</div>";
				}
			}
		}
	}
}

$body = <<<EOT
<div class="container text-center">
{$alert}
	<form action="" method="post">
		<h5>Reset Email</h5>
		<div class="card m-auto" style="max-width:40rem;">
			<div class="card-body text-center">
				<input type="text" name="email" class="form-control mb-3" placeholder="New Email">
				<input type="text" name="cemail" class="form-control mb-3" placeholder="Confirm New Email">
				<input type="password" name="password" class="form-control mb-3" placeholder="Current Password">
				<button type="Submit" name="Submit" class="btn btn-danger">Email Reset</button>
			</div>
		</div>
	</form>
</div>
EOT;

pageHandler();
$ph->pageTitle("Email Reset");
$ph->body = $body;
$ph->output();