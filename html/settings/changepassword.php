<?php

use Fobe\Users\User;

$alert = '';
if(isset($_POST['Submit'])) 
{
	$currentpassword = cleanInput($_POST['curpassword']);
	if (User::ValidatePassword($user->id, $currentpassword))
	{
		if ($_POST['npassword'] == $_POST['cnpassword'])
		{
			$newpassword = cleanInput($_POST['npassword']);
			if(strlen($newpassword) < 8) 
			{
				$alert = "<div class='alert alert-danger' role='alert'>Passwords must be atleast 8 characters long</div>";
			}
			else
			{
				if (changePassword($newpassword))
				{
					$alert = "<div class='alert alert-success' role='alert'>Password updated</div>";
				}
				else
				{
					$alert = "<div class='alert alert-danger' role='alert'>An error occurred, contact an admin</div>";
				}
			}
		}
		else
		{
			$alert = "<div class='alert alert-danger' role='alert'>The passwords you entered do not match</div>";
		}
	}
	else
	{
		$alert = "<div class='alert alert-danger' role='alert'>Incorrect current password</div>";
	}
}


$body = <<<EOT
<div class="container text-center">
{$alert}
	<form action="" method="post">
		<h5>Change Password</h5>
		<div class="card m-auto" style="max-width:40rem;">
			<div class="card-body text-center">
				<input type="password" name="curpassword" class="form-control mb-3" placeholder="Current Password">
				<input type="password" name="npassword" class="form-control mb-3" placeholder="New Password">
				<input type="password" name="cnpassword" class="form-control mb-3" placeholder="Confirm New Password">
				<button type="Submit" name="Submit" class="btn btn-danger">Change Password</button>
			</div>
		</div>
	</form>
</div>
EOT;
pageHandler();
$ph->pageTitle("Change Password");
$ph->body = $body;
$ph->output();