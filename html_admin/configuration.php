<?php

use Alphaland\Web\WebContextManager;

WebContextManager::ForceHttpsCloudflare();

if(!($user->isOwner())) {
	if ($user->isAdmin()) {
		redirect("/");
	}
	die('bababooey');
}

adminPanelStats();

//global variables
$devmode = false;

////db queries
$maintenancequery = $pdo->prepare("SELECT * FROM websettings WHERE maintenance = 1");
$maintenancequery->execute();

$status = $pdo->prepare("SELECT * FROM websettings WHERE maintenance = 1");
$status->execute();

$websettings = $pdo->prepare("SELECT * FROM websettings");
$websettings->execute();
$websettings = $websettings->fetch(PDO::FETCH_OBJ);
////end db queries

////Third party web queries
$soap_do = curl_init();
curl_setopt($soap_do, CURLOPT_URL, 'https://api.cloudflare.com/client/v4/zones/6dea541960676cbb10231ce5c8035b4c/settings/development_mode');
curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($soap_do, CURLOPT_TIMEOUT,        30);
curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $cloudflareheader);
$curl_response = curl_exec($soap_do);
$result = json_decode($curl_response,true);
if ($result['result']['value'] == "on")
{
	$devmode = true;
}
else 
{
	$devmode = false;
}
////end Third party web queries
//end queries

if(isset($_POST['maintenanceon'])) 
{
	enableMaintenance($_POST['optionalmaintenancetext']);
	redirect("configuration");
}

if(isset($_POST['maintenanceoff']))
{
	disableMaintenance();
	redirect("configuration");
}

if(isset($_POST['devmodeon'])) 
{
	$content = '{"value":"on"}';

	$soap_do = curl_init();
	curl_setopt($soap_do, CURLOPT_URL, 'https://api.cloudflare.com/client/v4/zones/6dea541960676cbb10231ce5c8035b4c/settings/development_mode');
	curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($soap_do, CURLOPT_TIMEOUT,        30);
	curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, 'PATCH');
	curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($soap_do, CURLOPT_POST,           true);
	curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $content);
	curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $cloudflareheader);

	$curl_response = curl_exec($soap_do);
	
	redirect("configuration");
}

if(isset($_POST['devmodeoff']))
{
	$content = '{"value":"off"}';

	$soap_do = curl_init();
	curl_setopt($soap_do, CURLOPT_URL, 'https://api.cloudflare.com/client/v4/zones/6dea541960676cbb10231ce5c8035b4c/settings/development_mode');
	curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($soap_do, CURLOPT_TIMEOUT,        30);
	curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, 'PATCH');
	curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($soap_do, CURLOPT_POST,           true);
	curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $content);
	curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $cloudflareheader);

	$curl_response = curl_exec($soap_do);
	
	redirect("configuration");
}

if (isset($_POST['clearcachesubmit']))
{
	$content = '{"purge_everything":true}';
	
	$soap_do = curl_init();
	curl_setopt($soap_do, CURLOPT_URL, 'https://api.cloudflare.com/client/v4/zones/6dea541960676cbb10231ce5c8035b4c/purge_cache');
	curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($soap_do, CURLOPT_TIMEOUT,        30);
	curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($soap_do, CURLOPT_POST,           true);
	curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $content);
	curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $cloudflareheader);

	$curl_response = curl_exec($soap_do);
	
	redirect("configuration");
}

if (isset($_POST['submitwskey']))
{
	$key = genHash(16);
	$setwskey = $pdo->prepare("UPDATE websettings SET webservice_key = :k");
	$setwskey->bindParam(":k", $key, PDO::PARAM_STR);
	$setwskey->execute();
	redirect("configuration");
}

if (isset($_POST['setwsipwhitelist']))
{
	$setwsip = $pdo->prepare("UPDATE websettings SET webservice_whitelist = :w");
	$setwsip->bindParam(":w", $_POST['setwsipwhitelist'], PDO::PARAM_STR);
	$setwsip->execute();
	redirect("configuration");
}

if (isset($_POST['cachingon']))
{
	$setapprovals = $pdo->prepare("UPDATE websettings SET avatarCaching = 1");
	$setapprovals->execute();
	redirect("configuration");
}

if (isset($_POST['cachingoff']))
{
	$setapprovals = $pdo->prepare("UPDATE websettings SET avatarCaching = 0");
	$setapprovals->execute();
	redirect("configuration");
}

$maintenancestatus = "";
if ($maintenancequery->rowCount() > 0)
{
	$maintenancestatus = '<b style="background-color:#c9c9c9;color:green;padding:2px;">ON</b>';
}
else
{
	$maintenancestatus = '<b style="background-color:#c9c9c9;color:red;padding:2px;">OFF</b>';
}

$developmentmodestatus = "";
if ($devmode)
{
	$developmentmodestatus = '<b style="background-color:#c9c9c9;color:green;padding:2px;">ON</b>';
}
else
{
	$developmentmodestatus = '<b style="background-color:#c9c9c9;color:red;padding:2px;">OFF</b>';
}

$body = <<<EOT
<div class="container text-center">
	<h5>General Configuration</h5>
	<div class="card" style="max-width: 38rem;margin: auto;">
		<div class="card-body">
			<form method="post">
				<div class="row">
					<div class="col-sm">
						<div class="form-group marg-auto">
							<h6>Maintenance ON/OFF</h6>
							<div class="row marg-bot-15">
								<div class="col-sm">
									<div class="container">
										<button type="submit" name="maintenanceon" style="width:15rem;" class="btn btn-lg btn-success marg-bot-15">ON</button>
									</div>
								</div>
								<div class="col-sm">
									<div class="container marg-bot-15">
										<button type="submit" name="maintenanceoff" style="width:15rem;" class="btn btn-lg btn-danger">OFF</button>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-sm">
									<div class="input-group mb-3">
										<input type="text" name="optionalmaintenancetext" class="form-control" placeholder="Optional Override Text">
									</div>
								</div>
							</div>
							<h6 class="marg-bot-15">Maintenance is currently: {$maintenancestatus}</h6>
							<hr>
							<h6>Development Mode (no cache) ON/OFF</h6>
							<div class="row marg-bot-15">
								<div class="col-sm">
									<div class="container">
										<button type="submit" name="devmodeon" style="width:15rem;" class="btn btn-lg btn-success marg-bot-15">ON</button>
									</div>
								</div>
								<div class="col-sm">
									<div class="container marg-bot-15">
										<button type="submit" name="devmodeoff" style="width:15rem;" class="btn btn-lg btn-danger">OFF</button>
									</div>
								</div>
							</div>
							<h6 class="marg-bot-15">Development Mode is currently: {$developmentmodestatus}</h6>
							<hr>
							<h6>Clear Cache</h6>
							<div class="row marg-bot-15">
								<div class="col-sm">
									<div class="container">
										<button type="submit" name="clearcachesubmit" style="width:15rem;" class="btn btn-lg btn-danger marg-bot-15">CLEAR</button>
									</div>
								</div>
							</div>
							<hr>
							<form method="post">
								<h6>Generate RCC Backend Key</h6>
								<div class="row">
									<div class="col-sm">
										<div class="input-group mb-3">
											<form action="" method="post">
												<input type="text" name="setwskey" class="form-control" value="{$websettings->webservice_key}" autocomplete="off" disabled>
												<div class="input-group-append">
													<button type="submit" name="submitwskey" class="btn btn-danger" type="button">Generate</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							</form>
							<hr>
							<form method="post">
								<h6>Set Backend IP Whitelist</h6>
								<div class="row">
									<div class="col-sm">
										<div class="input-group mb-3">
											<form action="" method="post">
												<input type="text" name="setwsipwhitelist" class="form-control" placeholder="WS IP">
												<div class="input-group-append">
													<button type="submit" class="btn btn-danger" type="button">Submit</button>
												</div>
											</form>
										</div>
									</div>
								</div>
							</form>
							<div class="container text-center marg-bot-15">
								<h6>Current Backend Whitelisted IP's: <hr><b style="background-color:#c9c9c9;color:red;padding:2px;">{$websettings->webservice_whitelist}</b></h6>
							</div>
							<hr>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

EOT;

pageHandler();
$ph->pagetitle = ""; 
$ph->navbar = "";
$ph->body = $body;
$ph->footer = "";
$ph->output();