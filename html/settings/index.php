<?php

/*
	Alphaland 2021
*/

$body = '';

$body = <<<EOT

<div class="container-fluid" style="margin-bottom:30px;">
<div class="container">
    <div id = "success_alert" class="alert alert-success" role="alert" style="display:none";></div>
    <div id = "error_alert" class="alert alert-danger" role="alert" style="display:none;"></div>
    <h5>Settings</h5>
    <div class="row">
        <div class="col-sm-3" style="margin-bottom: 30px;">
            <div class="card">
                <div class="card-body text-center">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                        <a class="nav-link active red-a-nounder" id="v-pills-account-tab" data-toggle="pill" href="#v-pills-account" role="tab" aria-controls="v-pills-account" aria-selected="true">Account</a>
                        <a class="nav-link red-a-nounder" id="v-pills-2fa-tab" data-toggle="pill" href="#v-pills-2fa" role="tab" aria-controls="v-pills-2fa" aria-selected="false">2FA</a>
                        <a class="nav-link red-a-nounder" id="v-pills-privacy-tab" data-toggle="pill" href="#v-pills-privacy" role="tab" aria-controls="v-pills-privacy" aria-selected="false">Privacy</a>
                        <a class="nav-link red-a-nounder" id="v-pills-theme-tab" data-toggle="pill" href="#v-pills-theme" role="tab" aria-controls="v-pills-theme" aria-selected="false">Theme</a>				
                        <a style="display:none;" class="nav-link red-a-nounder" id="v-pills-referral-tab" data-toggle="pill" href="#v-pills-referral" role="tab" aria-controls="v-pills-referral" aria-selected="false">Referrals</a>							
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
                                        <p id="settings_username"></p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-sm-2">
                                        <b>Email:</b>
                                    </div>
                                    <div class="col-sm-9">
                                        <a id="settings_email"></a> 
                                        <a class="red-a" href="changeemail">Change</a>
                                        <a id="settings_email_verified" style="display:none;" class="green-a-nounder">Verified</a>
                                        <button style="display:none;" type="button" id = "settings_email_unverified" onclick="sendVerificationEmail()" class="btn btn-danger"><a class=""><i class="fas fa-exclamation-triangle"></i></a> Re-Send Verification Email</button>
                                    </div>
                                </div>
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
                                <div class="row">
                                    <div class="col-sm-2">
                                        <b>Blurb:</b>
                                    </div>
                                    <div class="col-sm-9">
                                        <textarea style="min-height:12rem;max-height:12rem;" class="form-control" id="settings_blurb" autocomplete="off"></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm mt-2">
                                        <button type="button" onclick="updateBlurb($('#settings_blurb').val())" class="btn btn-danger float-right">Update Blurb</button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="v-pills-2fa" role="tabpanel" aria-labelledby="v-pills-2fa-tab">
                                <h5>2FA Settings</h5>
                                <div class="row" id="settings_2faenabled" style="display:none;">
                                    <div class="col-sm">
                                        <h6>2FA Is Enabled</h6>
                                    </div>
                                    <div class="col-sm-3">
                                        <button class="btn btn-danger w-100" onclick="disable2FA()">Disable 2FA</button>
                                    </div>
                                </div>
                                <div class="row" id="settings_2fadisabled" style="display:none;">
                                    <div class="col-sm-4" align="center">
                                        <div class="card">
                                            <div class="card-body">
                                                <img class="img-fluid w-100" id="settings_qrcode">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm">
                                        <div class="row">
                                            <div class="col-sm text-center mb-2">
                                                <div class="mb-1">
                                                    <b>Instructions:</b>
                                                </div>
                                                <h6>1. Scan the QR code with Google Authenticator</h6>
                                                <h6>2. Enter the code below and Submit</h6>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm">
                                                <div class="input-group">
                                                    <input class="form-control" id="settings_2fainput" placeholder="Code" autocomplete="off">
                                                    <div class="input-group-append">
                                                        <button class="btn btn-danger" onclick="activate2FA($('#settings_2fainput').val())">Submit</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="v-pills-privacy" role="tabpanel" aria-labelledby="v-pills-privacy-tab">
                                <h5>Privacy Settings</h5>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <b>Who can send me trades:</b>
                                    </div>
                                    <div class="col-sm-5">
                                        <select class="form-control" autocomplete="off" disabled>
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
                                        <select class="form-control" id="settings_joinpref" onchange="updatePrivacyJoinPref(this.value)" autocomplete="off">
                                            <option value="2">Everyone</option>
                                            <option value="1">Friends</option>
                                            <option value="0">Nobody</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                            <div class="tab-pane fade" id="v-pills-theme" role="tabpanel" aria-labelledby="v-pills-theme-tab">
                                <h5>Theme Settings</h5>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <b>Current Theme:</b>
                                    </div>
                                    <div class="col-sm-5">
                                        <select class="form-control" id="settings_theme" onchange="updateTheme(this.value)" autocomplete="off">
                                            <option value="0">Light Theme</option>
                                            <option value="1">Dark Theme</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                            <div class="tab-pane fade" id="v-pills-referral" role="tabpanel" aria-labelledby="v-pills-referral-tab">
                            	<h5>Referral Program</h5>
                                <h6>Maximum of two referral codes every 2 weeks.</h6>
                                <hr>
                                <div class="text-center">
                                    <div class="row">
                                        <div class="col-sm">
                                            <div class="input-group">
                                                <input type="text" id="generatedkey" class="form-control" autocomplete="off" readonly>
                                                <div class="input-group-append">	
                                                    <button type="button" onclick="generateKey()" class="btn btn-success" type="button">Generate</button>
                                                </div>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

function twofactorEnabled()
{
	$("#settings_2fadisabled").hide()
	$("#settings_2faenabled").show()
}

function twofactorDisabled()
{
	$("#settings_2faenabled").hide()
	$("#settings_2fadisabled").show()
}

function disable2FA()
{
	getJSONCDS("https://api.alphaland.cc/settings/twofactor/disable")
	.done(function(object) {
		if (object.success) {
			twofactorDisabled();
			initializeSettings();
			successMessage("2FA Disabled");
		} else {
			errorMessage("Error Occurred");
		}
	});
}

function activate2FA(code)
{
	postJSONCDS("https://api.alphaland.cc/settings/twofactor/activate", JSON.stringify({"code": code}))
	.done(function(object) {
		if (object.success) {
			twofactorEnabled();
			successMessage("2FA Enabled");
		} else {
			errorMessage("Invalid code");
		}
	});
}

function set2FAQR()
{
	getJSONCDS("https://api.alphaland.cc/settings/twofactor/qr")
	.done(function(object) {
		$("#settings_qrcode").attr("src",object.qr);
	});
}

function errorMessage(message)
{
	$("#error_alert").text(message);
	$("#error_alert").show();
	window.scrollTo({top: 0, behavior: "smooth"});
	setTimeout(function() 
	{
		$("#error_alert").hide();
	}, 1500);
}

function successMessage(message)
{
	$("#success_alert").text(message);
	$("#success_alert").show();
	window.scrollTo({top: 0, behavior: "smooth"});
	setTimeout(function() 
	{
		$("#success_alert").hide();
	}, 1500);
}

function updatePrivacyJoinPref(id)
{
	postJSONCDS("https://api.alphaland.cc/settings/update/joinprivacy", JSON.stringify({"preference": id}))
	.done(function(object) {
		successMessage("Updated");
	});
}

function updateTheme(id)
{
	postJSONCDS("https://api.alphaland.cc/settings/update/theme", JSON.stringify({"theme": id}))
	.done(function(object) {
		if (object.success) {
			location.reload();
		}
	});
}

function updateBlurb(text)
{
	postJSONCDS("https://api.alphaland.cc/settings/update/blurb", JSON.stringify({"blurb": text}))
	.done(function(object) {
		if (object.success) {
			successMessage("Updated");
		}
	});
}

function initializeSettings()
{
	getJSONCDS("https://api.alphaland.cc/settings/")
	.done(function(object) {
		$("#settings_username").html(object.username);
		$("#settings_email").html(object.email);
		$("#settings_blurb").html(object.blurb);
		$('#settings_theme').val(object.theme);
		$('#settings_joinpref').val(object.joinpref);

		if (object.twofactorenabled) {
			twofactorEnabled();
		} else {
			set2FAQR();
			twofactorDisabled();
		}

		if (object.referralprogram) {
			$("#v-pills-referral-tab").show()
		}

		if (object.verified) {
			$("#settings_email_verified").show()
		} else {
			$("#settings_email_unverified").show()
		}
	});
}

initializeSettings()

//referral program
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
</script>
EOT;

pageHandler();
$ph->pageTitle("Settings");
$ph->body = $body;
$ph->output();