<?php

use Alphaland\Web\WebContextManager;

if (isLoggedIn())
{
	WebContextManager::Redirect('/');
}

$alert = '';
if(isset($_GET['token'])) 
{
	$token = $_GET['token'];

	if (isValidPasswordResetToken($token))
	{
		if(isset($_POST['Submit'])) 
		{
			if ($_POST['password'] == $_POST['cpassword'])
			{
				$newpassword = cleanInput($_POST['password']);
				if(strlen($newpassword) < 8) 
				{
					$alert = "<div class='alert alert-danger' role='alert'>Passwords must be atleast 8 characters long</div>";
				}
				else
				{
					$userid = (int)confirmPasswordReset($token);
					if ($userid > 0)
					{
						if (changePasswordUid($userid, $newpassword))
						{
							logoutAllSessions($userid);
							$alert = "<div class='alert alert-success' role='alert'>Password updated</div>";
						}
						else
						{
							$alert = "<div class='alert alert-danger' role='alert'>An error occurred, contact an admin</div>";
						}
					}
					else
					{
						WebContextManager::Redirect("/forgotpassword");
					}
				}
			}
			else
			{
				$alert = "<div class='alert alert-danger' role='alert'>The passwords you entered do not match</div>";
			}
		}
	}
	else
	{
		WebContextManager::Redirect("/");
	}
}
else
{
	WebContextManager::Redirect("/");
}

$body = <<<EOT
<div class="container text-center">
{$alert}
	<form action="/settings/resetpassword?token={$_GET['token']}" method="post">
		<h5>Reset Password</h5>
		<div class="card m-auto" style="max-width:40rem;">
			<div class="card-body text-center">
				<input type="password" name="password" class="form-control mb-3" placeholder="New Password">
				<input type="password" name="cpassword" class="form-control mb-3" placeholder="Confirm New Password">
				<button type="Submit" name="Submit" class="btn btn-danger">Reset Password</button>
			</div>
		</div>
	</form>
</div>
EOT;

pageHandler();
$ph->pageTitle("Reset Password");
$ph->body = $body;
$ph->output();
?>