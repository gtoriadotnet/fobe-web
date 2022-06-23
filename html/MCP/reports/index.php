<?php

/*
    Finobe 2021
    Active Reports
*/

use Finobe\Web\WebContextManager;

if(!$user->IsStaff()) {
    WebContextManager::Redirect("/");
}

$body = <<<EOT
<h5 class="text-center">Open Reports</h5>
<div class="container">
<div id = "error_alert" class="alert alert-danger" role="alert" style="display:none;"></div>
	<div class = "card">
		<div class="row">
			<div class="container pb-3">
				<div id = "reports-container" class="card-body text-center">
                   
				</div>
			</div>
		</div>
	</div>
</div>
<script>
function populateReports()
{
    var html = `<div class="mod-ul-container">
    <ul>
    <li>
    <div class="card">
    <a>
    <div class="card-body">
    <a href="/MCP/reports/view?id={id}" style="color:grey;text-decoration:none;" class="no-overflow mb-1"><b><h5>{reported}</h5></b></a>
    </div>
    </a>
    </div>
    </li>
    </ul>
    </div>`;
    
    staticPageHelper("https://www.idk16.xyz/MCP/reports/data/reports", "https://api.idk16.xyz/logo", "#reports-container", html, "", 1000, "", "No active reports");
}
populateReports();
</script>

EOT;

pageHandler();
$ph->pageTitle("Reports");
$ph->body = $body;
$ph->output();