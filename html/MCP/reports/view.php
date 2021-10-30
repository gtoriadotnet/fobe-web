<?php

if(!$user->isStaff())
{
    redirect("/");
}

if (!$_GET['id'])
{
    redirect("/MCP/reports/");
}

$body = <<<EOT
<h5 class="text-center">Report Data</h5>
<hr>
<div class="container-fluid">
	<div class="container">
		<div class="col-sm marg-bot-15">
			<div class="card marg-auto" style="min-height:16rem;">
				<div class="card-body">
                    <button onclick="closeReport()" class="btn btn-danger" style="float:right;">Mark Closed</button>
					<h6 id="reporter-id"></h6>
                    <h6 id="place-id"></h6>
                    <h6 id="job-id"></h6>
                    <hr>
                    <h6 id="abuser-id"></h6>
                    <h6 id="report-reason"></h6>
                    <h6 id="report-description"></h6>
					<hr>
                    <h5>Chat Log:</h5>
					<div id="message-container">				
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
/*
	Alphaland 2021
*/
var getparam = new URLSearchParams(window.location.search).get("id");

function populateReport()
{
    getJSONCDS("https://www.alphaland.cc/MCP/reports/data/?id="+getparam)
	.done(function(jsonData) {
        $("#reporter-id").html("Reporter ID: "+jsonData.ReporterUid);
        $("#place-id").html("Place ID: "+jsonData.PlaceId);
        $("#job-id").html('Job ID: "'+jsonData.JobId+'"');
        $("#abuser-id").html("Abuser ID: "+jsonData.AbuserId);
        $("#report-reason").html('Report Reason: "'+jsonData.Reason+'"');
        $("#report-description").html('Report Description: "'+jsonData.Description+'"');


        var html = `<div class="row">
        <div class="col-sm marg-bot-15">
        <div class="card">
        <div class="card-body">
        <h6>Username: <a class="red-a" href="/profile/view?id={userid}"> {username}</a></h6>
        <div class="row marg-bot-15">
        <div class="col-sm" style="overflow:hidden;">
        <p>"{chat}"</p>
        </div>
        </div>
        </div>
        </div>
        </div>
        </div>`;

        $("#message-container").html(parseHtml(html, 1000, jsonData, "No logged chats"));
    });
}

function closeReport()
{
    alert("will close");
}

populateReport();
</script>

EOT;

pageHandler();
$ph->pageTitle("Report");
$ph->body = $body;
$ph->output();