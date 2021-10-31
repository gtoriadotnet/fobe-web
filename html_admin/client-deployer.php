<?php

forceHttpsCloudflare();

if(!($user->isOwner())) {
	if ($user->isAdmin()) {
		redirect("/");
	}
	die('bababooey');
}

adminPanelStats();

$alert = '';
	
	$body = <<<EOT
	<div class="container">
	{$alert}
		<h5>Network Security Key Generator<h5>
		<h6>MAKE SURE TO DEPLOY RCC WITH UPDATED KEY</h6>
		<div class="row">
			<div class="col-sm">
				<div class="card">
					<div class="card-body">
						<div class="card-body">
							<div class="row">
								<div class="col-sm">
								<h6>Game Security Version</h6>
									<div class="row marg-bot-15">
										<div class="col-sm">
											<input style="width:100%!important;" type="text" autocomplete="off" id="newgamesecurityversion" value="{$ws->security_version}" class="form-control">
										</div>
									</div>
									<h6>Generated Security Key</h6>
									<div class="row marg-bot-15">
										<div class="col-sm">
											<input style="width:100%!important;" type="text" autocomplete="off" id="generatedsecuritykey" class="form-control" readonly>
										</div>
									</div>
									<input type="button" onclick="generateSecKey()" value="Generate" class="btn btn-danger">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<hr>
		<h5>Client Deployer</h5>
		<h6>All input fields are required<h6>
		<div class="row">
			<div class="col-sm">
				<div class="card">
					<div class="card-body">
						<div class="card-body">
							<div class="row">
								<div class="col-sm">
									<h6>General Deployment Files</h6>
									<div class="card mb-3">
										<div class="card-body">
											<div class="row">
												<div class="col-sm">
													<link rel="stylesheet" href="style.css" />
													<div id="drop_file_zone" ondrop="upload_file(event)" ondragover="return false">
														<div id="drag_upload_file">
															<p>Drop required files here</p>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<h6>Game Executable Security Version</h6>
									<div class="row marg-bot-15">
										<div class="col-sm">
											<input style="width:100%!important;" type="text" autocomplete="off" id="gamesecurityver" value="{$ws->security_version}" class="form-control">
										</div>
									</div>
									<h6>Game Executable MD5 Hash</h6>
									<div class="row marg-bot-15">
										<div class="col-sm">
											<input style="width:100%!important;" type="text" autocomplete="off" id="gamemd5" value="{$ws->md5_hash}" class="form-control">
										</div>
									</div>
									<h6>Game Executable Version (separated by '.')</h6>
									<div class="row marg-bot-15">
										<div class="col-sm">
											<input style="width:100%!important;" type="text" autocomplete="off" id="gamefilever" value="{$ws->GameFileVersion}" class="form-control">
										</div>
									</div>
									<h6>Game Launcher File Version (separated by ',') Ex:1, 2, 3, 4</h6>
									<div class="row marg-bot-15">
										<div class="col-sm">
											<input style="width:100%!important;" type="text" autocomplete="off" id="launcherfilever" placeholder="Launcher File Version" class="form-control">
										</div>
									</div>
									<input type="button" name="deployclient" onclick="ajax_file_upload('client')" value="Deploy" class="btn btn-danger">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<hr>
		<h5>Studio Deployer</h5>
		<h6>All input fields are required<h6>
		<div class="row">
			<div class="col-sm">
				<div class="card">
					<div class="card-body">
						<div class="card-body">
							<div class="row">
								<div class="col-sm">
									<h6>General Deployment Files</h6>
									<div class="card mb-3">
										<div class="card-body">
											<div class="row">
												<div class="col-sm">
													<link rel="stylesheet" href="style.css" />
													<div id="drop_file_zone" ondrop="upload_file(event)" ondragover="return false">
														<div id="drag_upload_file">
															<p>Drop required files here</p>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									<h6>Studio Executable Version (separated by '.')</h6>
									<div class="row marg-bot-15">
										<div class="col-sm">
											<input style="width:100%!important;" type="text" autocomplete="off" id="studiofilever" value="{$ws->StudioFileVersion}" class="form-control">
										</div>
									</div>
									<h6>Studio Launcher File Version (separated by ',')</h6>
									<div class="row marg-bot-15">
										<div class="col-sm">
											<input style="width:100%!important;" type="text" autocomplete="off" id="studiolauncherfilever" placeholder="Studio Launcher File Version" class="form-control">
										</div>
									</div>
									<input type="button" name="deployclient" onclick="ajax_file_upload('studio')" value="Deploy" class="btn btn-danger">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
</div>
<script>
	var filesTemp;

	function upload_file(e) {
		e.preventDefault();
		filesTemp = e.dataTransfer.files;
	}
	
	function ajax_file_upload(type) {
		var files_obj = filesTemp;
		if(files_obj != undefined) {
			var form_data = new FormData();
			for(i=0; i<files_obj.length; i++) {
				form_data.append('file[]', files_obj[i]);
			}

			var url = "client-deployer-upload";
			if (type == "client") 
			{
				url += "?gamesecurityver="+$("#gamesecurityver").val()+"&gamemd5="+$("#gamemd5").val()+"&gamefilever="+$("#gamefilever").val()+"&launcherfilever="+$("#launcherfilever").val()+"&type=client";
			} 
			else if (type == "studio") 
			{
				url += "?gamefilever="+$("#studiofilever").val()+"&launcherfilever="+$("#studiolauncherfilever").val()+"&type=studio";
			}

			var xhttp = new XMLHttpRequest();
			xhttp.open("POST", url, true);
			xhttp.onload = function(event) {
				var message = this.responseText;
				alert(message);
			}
			xhttp.send(form_data);
		} else {
			alert("No files dropped");
		}
	}

	function generateSecKey() 
	{
		postJSONCDS("https://crackpot.alphaland.cc/generateSecurityKey", JSON.stringify({"version": $('#newgamesecurityversion').val()}))
		.done(function(object) {
			var result = object.result;
			if (result) {
				$("#generatedsecuritykey").val(result);
			}
		});
	}
</script>
EOT;

pageHandler();
$ph->pagetitle = ""; 
$ph->navbar = "";
$ph->body = $body;
$ph->footer = "";
$ph->output();