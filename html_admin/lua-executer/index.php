<?php

use Alphaland\Web\WebContextManager;

WebContextManager::ForceHttpsCloudflare();

if(!($user->isAdmin())) {
	die('bababooey');
}

adminPanelStats();

$alert = '';
	
$body = <<<EOT
<div class="container-fluid" style="margin-bottom:30px;">
	<div class="container text-center">
	<h5>SOAP Script Execution</h5>
	<h6>ACTIONS HERE ARE LOGGED</h6>
	<div id = "error_alert" class="alert alert-danger" role="alert" style="display:none;"></div>
		<div class="col-sm">
			<div class="card">
				<div class="card-body">
					<div class="row">
						<div class="col-sm-2"><div class="text-center"></div></div>
						<div class="col-sm-9">
							<p>
								<button class="btn btn-success w-100" type="button" onclick="activeJobs()" data-toggle="collapse" data-target="#activejobs" aria-expanded="false">Active Jobs</button>
							</p>
							<div class="collapse" id="activejobs">
								<table class="table atable-dark">
									<thead>
										<tr>
											<th>Job ID</th>
											<th>Place ID</th>
										</tr>
									</thead>
									<tbody id="active-jobs">
										
									</tbody>
								</table>
							</div>
							<input type="text" id="jobid-input" class="form-control" autocomplete="off" placeholder="Job ID">
							<div class="mt-2"></div>
							<textarea style="min-height:12rem;max-height:12rem;" id="script-input" class="form-control" placeholder="Script" autocomplete="off"></textarea>
							<div class="mt-2"></div>
							<textarea style="min-height:12rem;max-height:12rem;" id="script-output" class="form-control" autocomplete="off" readonly>Script Errors</textarea>
							<div class="mt-2"></div>
							<input type="button" onclick="executeScript()" value="Execute Script" class="btn btn-danger w-100">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
function executeScript()
{
	var jobid = $('#jobid-input').val();
	var script = $('#script-input').val();

	postJSONCDS("https://crackpot.alphaland.cc/lua-executer/executeScript", JSON.stringify({"jobid":jobid,"script":script}))
	.done(function(object) {
		var result = object.result;
		if (!jobid || !script)
		{
			$("#error_alert").text("Missing parameters");
			$("#error_alert").show();
			window.scrollTo({top: 0, behavior: "smooth"});
				
			setTimeout(function() 
			{
				$("#error_alert").hide();
			}, 3000);
		}
		else
		{
			$("#script-output").val(result);
		}
	});
}
function activeJobs()
{
	var html = '<tr>';
	html += '<td>{JobID}</td>';
	html += '<td>{PlaceID}</td>';
	html += '</tr>';
		
	staticPageHelper("https://crackpot.alphaland.cc/lua-executer/activeJobs", "", "#active-jobs", html, "", 100, "", "");
}
</script>

EOT;

pageHandler();
$ph->pagetitle = ""; 
$ph->navbar = "";
$ph->body = $body;
$ph->footer = "";
$ph->output();