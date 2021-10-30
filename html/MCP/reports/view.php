<?php

header('Content-Type: application/json');

$body = <<<EOT
<h5 class="text-center">Report Data</h5>
<hr>
<div class="container-fluid">
	<div class="container">
		<div class="col-sm marg-bot-15">
			<div class="card marg-auto" style="min-height:16rem;">
				<div class="card-body">
					<h6>Reporter ID: </h6>
                    <h6>Place ID: </h6>
                    <h6>Job ID: ""</h6>
                    <hr>
                    <h6>Abuser ID: </h6>
                    <h6>Report Reason: ""</h6>
                    <h6>Description: ""</h6>
					<hr>
                    <h5>Chat Log:</h5>
					<div id="message-container">
							
					</div>
					<div class="container mt-2 mb-2 text-center">
						<div id="page-buttons" class="btn-group" role="group" aria-label="First group">

						</div>
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
</script>

EOT;

pageHandler();
$ph->pageTitle("Chat Logs");
$ph->body = $body;
$ph->output();