<?php

use Alphaland\Web\WebContextManager;

WebContextManager::ForceHttpsCloudflare();

if(!($user->isOwner())) {
	if ($user->isAdmin()) {
		redirect("/");
	}
	die('bababooey');
}

adminPanelStats();

$alert = '';
if(isset($_POST['submitgiveasset']))
{
	$username = $_POST['username'];
	$catalogid = (int)$_POST['catalogid'];
	
	if(empty($username))
	{
		$alert = "<div class='alert alert-danger' role='alert'>Please provide a Username</div>";
	}
	elseif(!usernameExists($username))
	{
		$alert = "<div class='alert alert-danger' role='alert'>User doesn't exist</div>";
	}
	elseif(empty($catalogid))
	{
		$alert = "<div class='alert alert-danger' role='alert'>Please provide a valid Asset ID</div>";
	}
	else
	{
		$checkforitem = $pdo->prepare("SELECT * FROM assets WHERE id = :i");
		$checkforitem->bindParam(":i", $catalogid, PDO::PARAM_INT);
		$checkforitem->execute();
		
		if ($checkforitem->rowCount() > 0) //check if item exist in the catalog
		{
			$userid = getID($username);
			
			$checkuserforitem = $pdo->prepare("SELECT * FROM owned_assets WHERE uid = :ui AND aid = :ad");
			$checkuserforitem->bindParam(":ui", $userid, PDO::PARAM_INT);
			$checkuserforitem->bindParam(":ad", $catalogid, PDO::PARAM_INT);
			$checkuserforitem->execute();
			
			if ($checkuserforitem->rowCount() > 0) //check if the user already owns the item
			{
				$alert = "<div class='alert alert-danger' role='alert'>User already owns the Item</div>";
			}
			else
			{
				if (giveItem($userid, $catalogid))
				{
					$alert = "<div class='alert alert-success' role='alert'>Successfully gave user the item</div>";
				}
				else
				{
					$alert = "<div class='alert alert-danger' role='alert'>Failed to give user the item</div>";
				}
			}
		}
		else
		{
			$alert = "<div class='alert alert-danger' role='alert'>Asset ID doesn't exist</div>";
		}
	}
}
	
$body = <<<EOT
<div class="container text-center">
	{$alert}
	<h5>Give Asset</h5>
	<div class="card" style="max-width: 38rem;margin: auto;">
		<div class="card-body">
			<form method="post">
				<div class="row">
					<div class="col-sm">
						<form action="" method="post">
							<div class="input-group">
									<input type="text" name="username" class="form-control" placeholder="Username">
									<input type="text" name="catalogid" class="form-control" placeholder="Asset ID">
								<div class="input-group-append">
									<button type="submit" name="submitgiveasset" class="btn btn-success" type="button">Give</button>
								</div>
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