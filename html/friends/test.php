<?php

$alert = '';


	$body = <<<EOT
<div class="container">
    <h5>Username's Friends</h5>
    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-sm">
                    <div class="card">
                        <div class="card-body">
                        </div>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="card">
                        <div class="card-body">
                        </div>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="card">
                        <div class="card-body">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
EOT;

pageHandler();
$ph->pageTitle("Friends");
$ph->body = $body;
$ph->output();