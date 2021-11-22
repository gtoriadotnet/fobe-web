<?php

/*
	Alphaland 2021 Registration Page
	TODO: This needs a re-do. This is one of the first pages on this project
*/

$body = '';
$error = '';

if (!canRegister())
{
	$body = '
	<center>
			<section class="main-container">
				<div class="main-wrapper-reg" style="margin-top:26px;">
					<img src="alphaland/cdn/imgs/alphaland-1024.png" style="width:250px;">
					<h2><a style="text-decoration:none;color:black;">Registration temporarily disabled</a></h2>
					<a href="login"><button class="nav-item login-button btn btn-danger">Login</button></a> 
				</div>
			</section>
		</center>
	';
}
else
{
	if(isset($_POST['rb'])) 
	{
		$verifyResponse = @file_get_contents('apiurl'.cleanInput($_POST['g-recaptcha-response']));
		$responseData = @json_decode($verifyResponse);
		if(!(@$responseData->success))
		{
			$uname = cleanInput((string)$_POST['username']);
			$passw = cleanInput((string)$_POST['password']);
			$hpasw = password_hash($passw, PASSWORD_DEFAULT);
			$cpasw = (string)$_POST['cpassword'];
			$email = cleanInput((string)$_POST['email']);
			$signupkey = cleanInput((string)$_POST['signup_key']);
			
			if(usernameExists($uname) == true) 
			{
				$error = '<div class="alert alert-danger" role="alert">The username you entered already exists</div>';
			} 
			else 
			{
				if(!(ctype_alnum($uname))) 
				{
					$error = '<div class="alert alert-danger" role="alert">Your username must contain letters and numbers only</div>';
				}
				if(strlen($uname) < 3 || strlen($uname) > 20) 
				{
					$error = '<div class="alert alert-danger" role="alert">Your username must be between 3 and 20 characters long</div>';
				}
				if(isFiltered($uname))
				{
					$error = '<div class="alert alert-danger" role="alert">Username is not appropriate for Alphaland</div>';
				}
			}
			
			if(strlen($passw) < 8) 
			{
				$error = '<div class="alert alert-danger" role="alert">Passwords must be atleast 8 characters long</div>';
			} 
			else if(!($passw == $cpasw)) 
			{
				$error = '<div class="alert alert-danger" role="alert">The passwords you entered do not match</div>';
			}
			
			if(FILTER_VAR($email, FILTER_VALIDATE_EMAIL) == false) 
			{
				$error = '<div class="alert alert-danger" role="alert">The email you entered is invalid</div>';
			}
			
			if (emailRegistered($email))
			{
				$error = "Email is already registered";
				//redirect("/login"); //email already exists, redirect to login
			}

			$ip = getIP();
			/*
			if (isIPAssociatedWithAccount($ip))
			{
				$error = "IP Associated with another account";
				redirect("/login"); //ip is associated with another account, redirect to login
			}
			*/

			if($error == "") 
			{
				$isUserGen = false;
				$isAdminGen = false;
				if (isSignupKeyUserGenerated($signupkey)) //referral system
				{
					$isUserGen = true;
				}
				else if (verifySignupKey($signupkey)) //old invite keys from admins
				{
					$isAdminGen = true;
				}
				else 
				{
					$error = '<div class="alert alert-danger" role="alert">Invalid signup key</div>';
				}

				if($error == "")
				{
					//register the user
					$ruid = $pdo->prepare("INSERT INTO users(email,username,pwd,joindate,ip) VALUES(:e, :u, :p, UNIX_TIMESTAMP(), :i)");
					$ruid->bindParam(":u", $uname, PDO::PARAM_STR);
					$ruid->bindParam(":p", $hpasw, PDO::PARAM_STR);
					$ruid->bindParam(":e", $email, PDO::PARAM_STR);
					$ruid->bindParam(":i", $ip, PDO::PARAM_STR);
					if($ruid->execute()) 
					{
						//grab the new user's id
						$userID = getID($uname);
						
						//referral system
						if ($isUserGen)
						{
							handleSignupWithUserKey($userID, $signupkey);
						}
						
						//first place
						$gamename = $uname . "'s Place Number: 1";
						$gamedesc = $uname . "'s first place";
						createPlace($userID, $gamename, $gamedesc, 12);

						setDefaults($userID); //gives default outfit, body colors and wears the default outfit

						//setup the activation system
						$activation = new Alphaland\Users\Activation();
						$activation::setupUserActivation($userID);
						
						//create new session
						createSession($userID);

						//send verification email
						sendVerificationEmail("info@alphaland.cc", $email);
						redirect("/");
					} 
					else 
					{
						$error = '<div class="alert alert-danger" role="alert">An error occurred while registering, contact an Administrator</div>';
					}
				}	
			} 
		}
		else 
		{
			$error = '<div class="alert alert-danger" role="alert">Please verify the reCAPTCHA and try again.</div>';
		}
	}

	$body = '
	<h5 class="text-center">Register</h5>
	<div class="card" style="max-width: 38rem;margin: auto;">
			<div class="card-body">
			'.(($error != "")? $error:"").'
				<form method="post">
				<div class="form-group">
					<label>Username</label>
					<input type="text" name="username" value="'.$uname.'" class="form-control">
				</div>
				<div class="form-group">
					<label>Email</label>
					<input type="text" name="email" value="'.$email.'" class="form-control">
				</div>
				<div class="form-group">
					<label>Password</label>
					<input type="password" name="password" class="form-control">
				</div>
				<div class="form-group">
					<label>Confirm Password</label>
					<input type="password" name="cpassword" class="form-control">
				</div>
				<div class="form-group">
					<label>Signup Key</label>
					<input type="text" name="signup_key" class="form-control">
				</div>
					<button type="submit" name="rb" class="btn btn-danger">Register</button>
					<a class="red-a ml-2" href="login">Already have an account? login here!</a>
				</form>
			</div>
		</div>
	</div>';
}

pageHandler();
$ph->footer = "";
$ph->pageTitle("Register");
$ph->body = $body;
$ph->output();