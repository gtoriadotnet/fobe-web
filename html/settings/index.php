<?php

$alert = '';
$body = '';

function obfuscate_email($email)
{
    $em   = explode("@",$email);
    $name = implode('@', array_slice($em, 0, count($em)-1));
    $len  = floor(strlen($name)/2);

    return substr($name,0, $len) . str_repeat('.', $len) . "@" . end($em);   
}

$info = userInfo($GLOBALS['user']->id); // add true as a second param if u wanna use usernames instead
$username = $info->username;
$email = $info->email;
$obfuscatedemail = obfuscate_email($info->email);
$blurb = $info->blurb;
$verified = (int)$info->verified;

//blurb stuff
if(isset($_POST['blurbtext'])) 
{
	setBlurb($_POST['blurbtext']);
	$alert = "<div class='alert alert-success' role='alert'>Blurb set</div>";
	//redirect("settings.php");
}
// ...

//theme stuff
if(isset($_POST['submittheme']))
{
	if(isset($_POST['theme']))
	{
		if (!setTheme($_POST['theme']))
		{
			$alert = "<div class='alert alert-danger' role='alert'>An error occurred, contact an Administrator</div>";
		}
	}
	else
	{
		$alert = "<div class='alert alert-danger' role='alert'>An error occurred, contact an Administrator</div>";
	}
}
// ...

//canjoin stuff
if(isset($_POST['submitcanjoin']))
{
	if(isset($_POST['canjoin']))
	{
		if (!setCanJoinUser($_POST['canjoin']))
		{
			$alert = "<div class='alert alert-danger' role='alert'>An error occurred, contact an Administrator</div>";
		}
	}
	else
	{
		$alert = "<div class='alert alert-danger' role='alert'>An error occurred, contact an Administrator</div>";
	}
}
// ...

//email verification stuff
if(isset($_POST['verifyemail'])) 
{
	$send = sendVerificationEmail("info@alphaland.cc", $email);
	if ($send == 3)
	{
		$alert = "<div class='alert alert-danger' role='alert'>Please contact an admin</div>";
	}
	elseif ($send == 2)
	{
		$alert = "<div class='alert alert-danger' role='alert'>Please wait before re-sending a verification Email</div>";
	}
	elseif ($send == 1)
	{
		$alert = "<div class='alert alert-success' role='alert'>Verification email sent</div>";
	}
	//redirect("settings.php");
}

//referral program stuff
$referralbuttonhtml = ""; //i know this is a terrible implementation but this page isnt js powered yet
if (inReferralProgram($GLOBALS['user']->id))
{
	$referralbuttonhtml = '<a class="nav-link red-a-nounder" id="v-pills-referral-tab" data-toggle="pill" href="#v-pills-referral" role="tab" aria-controls="v-pills-referral" aria-selected="false">Referrals</a>';
}

$verified_html = '';
if ($verified == 1)
{
	$verified_html = '<a class="green-a-nounder"><i class="fas fa-check"></i></a> <a class="green-a-nounder">Verified</a></p>';
}
elseif ($verified == 0)
{
	$verified_html = '<button type="submit" name="verifyemail" value="Submit" class="btn btn-danger"><a class=""><i class="fas fa-exclamation-triangle"></i></a> Re-Send Verification Email</button>';
}
// ...

$currentcanjoinstatus = getCurrentCanJoinStatus();
$currenttheme = getCurrentTheme();
$body = <<<EOT
<div class="container-fluid" style="margin-bottom:30px;">
	<div class="container">
	<div id = "error_alert" class="alert alert-danger" role="alert" style="display:none;"></div>
	{$alert}
		<h5>Settings</h5>
		<div class="row">
			<div class="col-sm-3" style="margin-bottom: 30px;">
				<div class="card">
					<div class="card-body text-center">
						<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
							<a class="nav-link active red-a-nounder" id="v-pills-account-tab" data-toggle="pill" href="#v-pills-account" role="tab" aria-controls="v-pills-account" aria-selected="true">Account</a>
							<a class="nav-link red-a-nounder" id="v-pills-security-tab" data-toggle="pill" href="#v-pills-security" role="tab" aria-controls="v-pills-security" aria-selected="false">Security</a>
							<a class="nav-link red-a-nounder" id="v-pills-privacy-tab" data-toggle="pill" href="#v-pills-privacy" role="tab" aria-controls="v-pills-privacy" aria-selected="false">Privacy</a>
							<a class="nav-link red-a-nounder" id="v-pills-theme-tab" data-toggle="pill" href="#v-pills-theme" role="tab" aria-controls="v-pills-theme" aria-selected="false">Theme</a>
							{$referralbuttonhtml}
						</div>
					</div>
				</div>
			</div>
		<div class="col-sm">
			<div class="card">
				<div class="card-body">
					<div class="tab-content" id="v-pills-tabContent">
						<div class="tab-content" id="v-pills-tabContent">
							<div class="tab-pane fade show active" id="v-pills-account" role="tabpanel" aria-labelledby="v-pills-account-tab">
								<h5>Account Settings</h5>
								<div class="row">
									<div class="col-sm-2">
										<b>Username:</b>
									</div>
									<div class="col-sm-9">
										<p>{$username}</p>
									</div>
								</div>
								<hr>
								<form action="" method="post">
									<div class="row">
										<div class="col-sm-2">
											<b>Email:</b>
										</div>
										<div class="col-sm-9">
											<p>{$obfuscatedemail} <a class="red-a" href="changeemail">Change</a> 
											{$verified_html}
										</div>
									</div>
								</form>
								<hr>
								<div class="row">
									<div class="col-sm-2">
										<b>Password:</b>
									</div>
									<div class="col-sm-9">
										<p><a class="red-a" href="changepassword">Change</a></p>
									</div>
								</div>
								<hr>
								<form action="" method="post">
									<div class="row">
										<div class="col-sm-2">
											<b>Blurb:</b>
										</div>
										<div class="col-sm-9">
											<textarea style="min-height:12rem;max-height:12rem;" class="form-control" name="blurbtext">{$blurb}</textarea>
										</div>
									</div>
									<div class="row">
										<div class="col-sm mt-2">
											<button type="submit" name="submitblurb" value="Submit" class="btn btn-danger float-right">Update Blurb</button>
										</div>
									</div>
								</form>
							</div>
							<div class="tab-pane fade" id="v-pills-security" role="tabpanel" aria-labelledby="v-pills-security-tab">
								<h5>Security Settings</h5>
								More advanced security features coming soon!
							</div>
							<div class="tab-pane fade" id="v-pills-privacy" role="tabpanel" aria-labelledby="v-pills-privacy-tab">
								<h5>Privacy Settings</h5>
								<form action="" method="post">
									<div class="row">
										<div class="col-sm-4">
											<b>Who can send me trades:</b>
										</div>
										<div class="col-sm-5">
											<select class="form-control" name="whocansendtrade" id="whocansendtrade" disabled>
												<option value="everyone">Everyone</option>
												<option value="friends">Friends</option>
												<option value="mobody">Nobody</option>
											</select>
										</div>
									</div>
									<hr>
									<div class="row">
										<div class="col-sm-4">
											<b>Who can join me:</b>
										</div>
										<div class="col-sm-5">
											<select class="form-control" name="canjoin" id="canjoin">
												<option value="2">Everyone</option>
												<option value="1">Friends</option>
												<option value="0">Nobody</option>
											</select>
										</div>
									</div>
									<hr>
									<div class="row">
										<div class="col-sm">
											<button type="Submit" value="Submit" name="submitcanjoin" class="btn btn-danger float-right">Update</button>
										</div>
									</div>
								</form>
							</div>
							<div class="tab-pane fade" id="v-pills-theme" role="tabpanel" aria-labelledby="v-pills-theme-tab">
								<h5>Theme Settings</h5>
								<form action="" method="post">
									<div class="row">
										<div class="col-sm-4">
											<b>Current Theme:</b>
										</div>
										<div class="col-sm-5">
											<select class="form-control" name="theme" id="theme" >
												<option value="0">Light Theme</option>
												<option value="1">Dark Theme</option>
											</select>
										</div>
									</div>
									<hr>
									<div class="row">
										<div class="col-sm">
											<button type="Submit" value="Submit" name="submittheme" class="btn btn-danger float-right">Update</button>
										</div>
									</div>
								</form>
							</div>
							<div class="tab-pane fade" id="v-pills-referral" role="tabpanel" aria-labelledby="v-pills-referral-tab">
								<h5>Referral Program</h5>
								<h6>Maximum of two referral codes every 2 weeks.</h6>
								<hr>
								<form action="" method="post">
									<div class="text-center">
										<div class="row">
											<div class="col-sm">
												<div class="input-group">
													<form action="" method="post">
														<input type="text" id="generatedkey" class="form-control" autocomplete="off" readonly>
														<div class="input-group-append">	
															<button type="button" onclick="generateKey()" class="btn btn-success" type="button">Generate</button>
														</div>
													</form>
												</div>
											</div>
										</div>
										<hr>
										<p>
											<button class="btn btn-danger w-50" type="button" data-toggle="collapse" data-target="#signupkeyslist" aria-expanded="false" aria-controls="signupkeyslist" onclick="activeKeys()">Active Keys</button>
										</p>
										<div class="collapse" id="signupkeyslist">
											<table class="table atable-dark">
												<thead>
													<tr>
														<th>Date Generated</th>
														<th>Signup Key</th>
													</tr>
												</thead>
												<tbody id="userKeys">
													
												</tbody>
											</table>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
function generateKey()
{
	getJSONCDS("https://api.alphaland.cc/referrals/generateSignupKey")
	.done(function(object) {
		var alert = object.alert;
		var key = object.key;
		var messageid = "#error_alert";
		if (alert == "") {
			$("#generatedkey").val(key);
			activeKeys();
		}
		else
		{
			$("#error_alert").text(alert);
			$("#error_alert").show();
			window.scrollTo({top: 0, behavior: "smooth"});
			setTimeout(function() 
			{
				$("#error_alert").hide();
			}, 3000);
		}	
	});
}
function activeKeys()
{
	var html = '<tr>';
	html += '<td>{whenGenerated}</td>';
	html += '<td>{key}</td>';
	html += '</tr>';
		
	staticPageHelper("https://api.alphaland.cc/referrals/activeUserKeys", "", "#userKeys", html, "", 100, "", "");
}

$('#theme').val('{$currenttheme}');
$('#canjoin').val('{$currentcanjoinstatus}');
</script>
EOT;

pageHandler();
$ph->pageTitle("Settings");
$ph->body = $body;
$ph->output();