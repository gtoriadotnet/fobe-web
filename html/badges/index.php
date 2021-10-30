<?php

$body = '';

//badges
$allbadges = allOfficialBadges();
$badges_html = "";
if($allbadges->rowCount() > 0)
{
	foreach($allbadges as $badges)
	{	
		$badges_html .= <<<EOT
		<div class="row mb-3">
			<div class="col-sm">
				<div class="card">
					<div class="row">
						<div class="col-sm-3 text-center">
							<img class="img-fluid" width="170" src="{$badges['image']}">
						</div>
						<div class="col-sm text-center" style="padding: 26px;">
							<h5>{$badges['name']}</h5>
							<p>{$badges['description']}</p>
						</div>
					</div>
				</div>
			</div>
		</div>
EOT;
	}	
}

$body = <<<EOT
<div class="container">
	<div class="text-center">
		<h5>Official Badges</h5>
	</div>
	<div class="row">
		<div class="col-sm">
			<div class="card">
				<div class="card-body">
				{$badges_html}
				</div>
			</div>
		</div>
	</div>
</div>
EOT;

pageHandler();
$ph->pageTitle("Badges");
$ph->body = $body;
$ph->output();