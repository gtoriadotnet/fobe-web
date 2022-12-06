<?php

/*
	Fobe 2021 Registration Page
	TODO: This needs a re-do. This is one of the first pages on this project
*/

use Fobe\Administration\SignupKey;
use Fobe\Common\Email;
use Fobe\Moderation\Filter;
use Fobe\Users\Activation;
use Fobe\Users\ReferralProgram;
use Fobe\Web\WebContextManager;

$body = '';
$error = '';

if (isLoggedIn())
{
	WebContextManager::Redirect("/");
}

if (!canRegister())
{
	$body = '
	<center>
			<section class="main-container">
				<div class="main-wrapper-reg" style="margin-top:26px;">
					<img src="fobe/cdn/imgs/finobe-1024.png" style="width:250px;">
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
		$ip = WebContextManager::GetCurrentIPAddress();
		
		$curl_do = curl_init(); 
        curl_setopt($curl_do, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');   
        curl_setopt($curl_do, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_do, CURLOPT_SSL_VERIFYPEER, false);  
        curl_setopt($curl_do, CURLOPT_SSL_VERIFYHOST, false); 
        curl_setopt($curl_do, CURLOPT_POST, true ); 
        curl_setopt($curl_do, CURLOPT_POSTFIELDS, [
			'secret' => $GLOBALS['recaptchaSecretKey'],
			'response' => $_POST['g-recaptcha-response'],
			'remoteip' => $ip
		] );
        $result = @json_decode(curl_exec($curl_do));
        curl_close($curl_do);
		
		if(@$result->success)
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
				if (Filter::IsTextFiltered($uname))
				{
					$error = '<div class="alert alert-danger" role="alert">Username is not appropriate for Fobe</div>';
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
			
			if (Email::IsEmailRegistered($email))
			{
				$error = '<div class="alert alert-danger" role="alert">Email is already registered</div>';
			}

			if (WebContextManager::IsIpRegistered($ip))
			{
				$error = '<div class="alert alert-danger" role="alert">An unexpected error occurred. Please contact an Administrator if possible.</div>';
			}
			
			if($error == "") 
			{
				/*
				$isUserGen = false;
				$isAdminGen = false;
				if (ReferralProgram::IsUserGeneratedKey($signupkey)) //referral system
				{
					$isUserGen = true;
				}
				else if (SignupKey::ValidateSignupKey($signupkey)) //invite keys from admins
				{
					$isAdminGen = true;
				}
				else 
				{
					$error = '<div class="alert alert-danger" role="alert">Invalid signup key</div>';
				}
				*/

				//if($error == "")
				//{
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
							ReferralProgram::ConfirmSignup($userID, $signupkey);
						}
						
						//first place
						$gamename = $uname . "'s Place Number: 1";
						$gamedesc = $uname . "'s first place";
						createPlace($userID, $gamename, $gamedesc, 12);

						setDefaults($userID); //gives default outfit, body colors and wears the default outfit

						//setup the activation system
						Activation::SetupUserActivation($userID);
						
						//create new session
						$GLOBALS['user']->CreateSession($userID);

						//send verification email
						sendVerificationEmail("info@idk16.xyz", $email);
						WebContextManager::Redirect("/");
					} 
					else 
					{
						$error = '<div class="alert alert-danger" role="alert">An error occurred while registering, contact an Administrator</div>';
					}
				//}	
			} 
		}
		else 
		{
			$error = '<div class="alert alert-danger" role="alert">Please verify the reCAPTCHA and try again.</div>';
		}
	}

	/*
	<div class="form-group">
		<label>Signup Key</label>
		<input type="text" name="signup_key" class="form-control">
	</div>
	*/

	$body = '
	<script src="https://www.google.com/recaptcha/api.js"></script>
	<script>
		function onSubmit(token) {
			document.getElementById("register-form").submit();
		}
	</script>

	<h5 class="text-center">Register</h5>
	<div class="card" style="max-width: 38rem;margin: auto;">
			<div class="card-body">
			'.(($error != "")? $error:"").'
				<form method="post" id="register-form">
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
					<input type="hidden" name="rb" />
					<button data-sitekey="' . $GLOBALS['recaptchaSiteKey'] . '" data-callback="onSubmit" data-action="submit" class="g-recaptcha btn btn-danger">Register</button>
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