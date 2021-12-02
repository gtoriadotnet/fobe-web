<?php

use Alphaland\Web\WebContextManager;

$error = "";

if (isLoggedIn())
{
	WebContextManager::Redirect("/");
}

if(isset($_POST['lg'])) 
{
	$username = cleanInput($_POST['username']);
	$password = cleanInput($_POST['password']);
	if(usernameExists($username)) 
	{
		$userID = getID($username);
		if(passwordCorrect($userID, $password)) 
		{
			createSession($userID);
			
			if (isset($_GET['referral']))
			{
				header("Location: " . $_GET['referral']);
				die();
			}	
			header("Location: /");
			die();
		} 
		else 
		{
			$error = "Username or password incorrect";
		}
	} 
	else 
	{
		$error = "Username or password incorrect";
	}
}

$body = '
				<h5 class="text-center">Login</h5>
				<div class="card" style="max-width: 38rem;margin: auto;">
					<div class="card-body">
					'.(($error != "")? '<div class="alert alert-danger" role="alert">'.$error.'</div>':"").'
						<form method="post">
							<div class="form-group">
								<label>Username</label>
								<input type="text" name="username" class="form-control">
							</div>
							<div class="form-group">
								<label>Password</label>
								<input type="password" name="password" class="form-control">
							</div>
								<button type="submit" name="lg" class="btn btn-danger">Login</button>
								<a class="red-a ml-2" href="/register">Don\'t have an account? Register here!</a>
						</form>
						<div class="mt-2">
						<a class="grey-hov" href="/login/forgotpassword">Forgot your password? Reset it here!</a>
						</div>
					</div>
				</div>';
pageHandler();
$ph->footer = "";
$ph->pageTitle("Login");
$ph->body = $body;
$ph->output();