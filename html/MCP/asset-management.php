<?php

use Fobe\Web\WebContextManager;

if(!$user->IsStaff())
{
    WebContextManager::Redirect("/");
}

$body = <<<EOT
<h5 class="text-center">Asset Approvals</h5>
<h5 class="text-center">If you deny an asset, it will Moderate it and delete all associated files. Be sure of your decision</h5>
<h5 class="text-center">Report any issues in the staff chat in the Discord server</h5>
<div class="container">
<div id = "error_alert" class="alert alert-danger" role="alert" style="display:none;"></div>
	<div class = "card">
		<div class="row">
			<div class="container pb-3">
				<div id = "assets-container" class="card-body text-center">

				</div>
				<div class="container mt-2 mb-2 text-center">
					<div id="page-buttons" class="btn-group" role="group" aria-label="First group">

					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>	
var currentPage = 1;
function moderateAsset(id)
{
	getJSONCDS("https://www.idk16.xyz/MCP/moderateasset?id="+id)
	.done(function(object) 
	{
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Moderated Asset") {
			assetPage(currentPage);
		}
		else
		{
			$(messageid).text(alert);
			$(messageid).show();
			window.scrollTo({top: 0, behavior: "smooth"});
			setTimeout(function() 
			{
				$(messageid).hide();
			}, 3000);
		}	
	});
}
function approveAsset(id)
{
	getJSONCDS("https://www.idk16.xyz/MCP/approveasset?id="+id)
	.done(function(object) 
	{
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Approved Asset") {
			assetPage(currentPage);
		}
		else
		{
			$(messageid).text(alert);
			$(messageid).show();
			window.scrollTo({top: 0, behavior: "smooth"});
			setTimeout(function() 
			{
				$(messageid).hide();
			}, 3000);
		}	
	});
}

function assetTypeToThumb(object) {
	var html = ``;
	if (object.assettypeid != 3) {
		html = `<img class="img-fluid" src="` + object.image + `" style="min-width:128px;max-width:128px;min-height:128px;max-height:128px;" />`;
	} else {
		html = `<audio controls="" style="min-width: 128px;max-width: 128px;" src="` + object.image + `"></audio>`;
	}
	return html;
}

function assetPage(num, keyword = "")
{
	currentPage = num;

	var html= '<div class="mod-ul-container">';
	html +='<ul>';
	html +='<li>';
	html +='<div class="card">';
	html +='<a href="/catalog/view?id={assetid}">';
	html +='<div class="card-body">';
	html +='[assetTypeToThumb]';
	html +='<a style="color:grey;text-decoration:none;" class="no-overflow mb-1">{name}</a>';
	html +='<button onclick="approveAsset({assetid})" class="btn btn-sm btn-success w-100">Approve</button>';
	html +='<div class="w-100 mb-1"></div>';
	html +='<button onclick="moderateAsset({assetid})" class="btn btn-sm btn-danger w-100">Deny</button>';
	html +='</div>';
	html +='</a>';
	html +='</div>';
	html +='</li>';
	html +='</ul>';
	html +='</div>';

	multiPageHelper("assetPage", [], "https://www.idk16.xyz/MCP/pendingassets", "https://api.idk16.xyz/logo", "#assets-container", "#page-buttons", html, num, 10, "", "No pending assets");
}
assetPage(currentPage);
</script>
EOT;
pageHandler();
$ph->pageTitle("Asset Approvals");
$ph->body = $body;
$ph->output();