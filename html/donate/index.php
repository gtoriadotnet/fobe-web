<?php

$alert = '';

	$body = <<<EOT
<div class="container-fluid">
	<div class="container">
	<h3>If you found this page, now you know the upcoming donation rewards</h3>
	<h4>Help keep Finobe alive with a donation!</h4>
		<h5>Perks:</h5>
		<h5>Instant reward of Alphabux, 300</h5>
		<h5>More Alphabux rewarded daily, increased to 40</h5>
		<h5>Allowance of more games (8)</h5>
		<h5>Special Item to show support</h5>
		<h5>Special Badge to show support</h5>
		<h5>Special Badge in-game</h5>
		<h5>
	</div>
</div>
EOT;

pageHandler();
$ph->pageTitle("Donation");
$ph->body = $body;
$ph->output();