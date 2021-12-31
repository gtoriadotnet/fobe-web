<?php

use Alphaland\Web\WebContextManager;
use Alphaland\Web\WebsiteSettings;

WebContextManager::ForceHttpsCloudflare();

if(!($user->IsOwner())) {
	die('bababooey');
}

adminPanelStats();

$alert = "";
if (isset($_POST['setannouncement']))
{
	if (empty($_POST['setannouncement']))
	{
		WebsiteSettings::UpdateSetting('announcement', "");
		WebsiteSettings::UpdateSetting('announcement_color', "");
	}
	else
	{
		$count = count($_POST); //post variable count
		if ($count < 2)
		{
			$alert = "<div class='alert alert-danger' role='alert'>Please select a color</div>";
		}
		else
		{
			if ($count > 2)
			{
				$alert = "<div class='alert alert-danger' role='alert'>Please select only one color</div>";
			}
			else
			{
				$color = "";
				if ($_POST['blue_checkbox'])
				{
					$color = "blue";
				}
				elseif ($_POST['green_checkbox'])
				{
					$color = "green";
				}
				elseif ($_POST['red_checkbox'])
				{
					$color = "red";
				}
				WebsiteSettings::UpdateSetting('announcement', $_POST['setannouncement']);
				WebsiteSettings::UpdateSetting('announcement_color', $color);
			}
		}
	}
}

$body = <<<EOT
<div class="container text-center">
	{$alert}
	<h5>Create Announcement</h5>
	<div class="card" style="max-width: 38rem;margin: auto;">
		<div class="card-body">
			<form method="post">
				<div class="row">
					<div class="col-sm">
						<form action="" method="post">
							<div class="input-group mb-3">
									<input type="text" name="setannouncement" class="form-control" placeholder="Announcement">
								<div class="input-group-append">
									<button type="submit" class="btn btn-danger" type="button">Submit</button>
								</div>
							</div>
							<div class="custom-control custom-checkbox custom-control-inline">
								<input type="checkbox" name="blue_checkbox" class="custom-control-input" id="bluecheck">
								<label class="custom-control-label" for="bluecheck">Blue</label>
							</div>
							<div class="custom-control custom-checkbox custom-control-inline">
								<input type="checkbox" name="green_checkbox" class="custom-control-input" id="greencheck">
								<label class="custom-control-label" for="greencheck">Green</label>
							</div>
							<div class="custom-control custom-checkbox custom-control-inline">
								<input type="checkbox" name="red_checkbox" class="custom-control-input" id="redcheck">
								<label class="custom-control-label" for="redcheck">Red</label>
							</div>
						</form>
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