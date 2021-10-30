<?php

$body = '';
if(!($user->isStaff())) 
{
    redirect("../404"); //u not admin nigga
}

$alert = "";
if(isset($_POST['unbanuser'])) 
{
	$id = getID($_POST['unbanuser']);
	if (unbanUser($id))
	{
		logStaffAction("Unbanned User ".$id);
		$alert = "<div class='alert alert-success' role='alert'>Unbanned {$_POST['unbanuser']}</div>";
	}
	else
	{
		$alert = "<div class='alert alert-danger' role='alert'>Failed to unban user</div>";
	}
}

if(isset($_POST['banuser'])) 
{
	$postcount = count($_POST);
	
	if ($postcount > 3)
	{
		$alert = "<div class='alert alert-danger' role='alert'>An error occurred</div>";
	}
	elseif (empty($_POST['banuser']))
	{
		$alert = "<div class='alert alert-danger' role='alert'>No username provided</div>";
	}
	elseif(usernameExists($_POST['banuser']) == false)
	{
		$alert = "<div class='alert alert-danger' role='alert'>No account with that username found</div>";
	}
	elseif (empty($_POST['banreason']))
	{
		$alert = "<div class='alert alert-danger' role='alert'>No ban reason provided</div>";
	}	
	elseif ($postcount < 3)
	{
		$alert = "<div class='alert alert-danger' role='alert'>Please select a ban type</div>";
	}
	else
	{
		$bantype = 0; //default warning bantype
		$banexpiration = 0;
		if (isset($_POST['temp_checkbox']))
		{
			//tempban
			$bantype = 1;
			$banexpiration = time() + 86400; //add one day to current time
		}
		elseif (isset($_POST['perm_checkbox']))
		{
			//perm ban
			$bantype = 2;
		}
		
		$id = getID($_POST['banuser']);
		if (banUser($id, cleanInput($_POST['banreason']), $banexpiration, $bantype))
		{
			logStaffAction("Banned User ".$id);
			$alert = "<div class='alert alert-success' role='alert'>Banned {$_POST['banuser']}</div>";
		}
		else
		{
			$alert = "<div class='alert alert-danger' role='alert'>Failed to ban user</div>";
		}
	}
}

$b = $pdo->prepare("SELECT * FROM user_bans WHERE valid = 1");
$b->bindParam(":i", $id, PDO::PARAM_INT);
$b->execute();

$banneduser = "";
if ($b->rowCount() > 0)
{
	foreach ($b as $bannedplayer)
	{
		$banneddate = date("m/d/Y", $bannedplayer['whenBanned']);
		$bannedusername = getUsername($bannedplayer['uid']);
		$bannedreason = cleanOutput($bannedplayer['banReason']);
		$bannedExpiration = (int)$bannedplayer['banExpiration'];
		$bannedType = (int)$bannedplayer['banType'];
		
		if ($bannedType == 0)
		{
			$bannedExpiration = "Warning";
		}
		elseif ($bannedType == 2)
		{
			$bannedExpiration = "Permanent";
		}
		else
		{
			$bannedExpiration = date("m/d/Y", $bannedplayer['banExpiration']);
		}
		
		$banneduser .= <<<EOT
		<tr>
			<td>{$banneddate}</td>
			<td>{$bannedusername}</td>
			<td>{$bannedreason}</td>
			<td>{$bannedExpiration}</td>
		</tr>
EOT;
	}
}

$body = <<<EOT
<div class="container text-center">
{$alert}
	<h5>User Management</h5>
	<div class="card" style="max-width: 38rem;margin: auto;">
		<div class="card-body">
			<form method="post">
				<div class="row">
					<div class="col-sm">
						<div class="input-group">
							<input type="text" name="banuser" class="form-control" placeholder="Username">
							<input type="text" name="banreason" class="form-control" placeholder="Ban Reason">
							<div class="input-group-append">
								<button type="submit" class="btn btn-danger" type="button">Ban</button>
							</div>
						</div>
					</div>
				</div>
				<br>
				<div class="custom-control custom-checkbox custom-control-inline">
					<input type="checkbox" name="warning_checkbox" class="custom-control-input sev_check" id="warning">
					<label class="custom-control-label" for="warning">Warning</label>
				</div>
				<div class="custom-control custom-checkbox custom-control-inline">
					<input type="checkbox" name="temp_checkbox" class="custom-control-input sev_check" id="temp">
					<label class="custom-control-label" for="temp">Temporary (1 day)</label>
				</div>
				<div class="custom-control custom-checkbox custom-control-inline">
					<input type="checkbox" name="perm_checkbox" class="custom-control-input sev_check" id="perm">
					<label class="custom-control-label" for="perm">Permanent</label>
				</div>
				<script type="text/javascript">
					$('.sev_check').click(function() {
						$('.sev_check').not(this).prop('checked', false);
					});
				</script>
			</form>
			<hr>
			<form method="post">
				<div class="row">
					<div class="col-sm">
						<div class="input-group">
							<form action="" method="post">
								<input type="text" name="unbanuser" class="form-control" placeholder="Username">
								<div class="input-group-append">	
									<button type="submit" class="btn btn-success" type="button">Unban</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</form>
			<hr>
			<div class="text-center">
				<p>
					<button class="btn btn-danger w-100" type="button" data-toggle="collapse" data-target="#banlisttemp" aria-expanded="false" aria-controls="banlisttemp">Banlist</button>
				</p>
				<div class="collapse" id="banlisttemp">
					<table class="table atable-dark">
						<thead>
							<tr>
								<th>Date</th>
								<th>Username</th>
								<th>Reason</th>
								<th>Expiration</th>
							</tr>
						</thead>
						<tbody>
							{$banneduser}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
EOT;

pageHandler();
$ph->pageTitle("User Manage");
$ph->body = $body;
$ph->output();
?>