<?php

forceHttpsCloudflare();

if(!($user->isOwner())) {
	if ($user->isAdmin()) {
		redirect("/");
	}
	die('bababooey');
}

adminPanelStats();

$alert = "";

if(isset($_POST['rankuser'])) 
{
	if (empty($_POST['rankuser']))
	{
		$alert = "<div class='alert alert-danger' role='alert'>No username provided</div>";
	}
	elseif(usernameExists($_POST['rankuser']) == false)
	{
		$alert = "<div class='alert alert-danger' role='alert'>No account with that username found</div>";
	}
	else
	{
		$userid = getID($_POST['rankuser']);
		$rank = $_POST['newrank'];
		
		if ($user->id > 2 && userInfo(getID($userid))->rank == 2) //cant modify rank if this condition is met
		{
			$alert = "<div class='alert alert-danger' role='alert'>Cannot modify rank</div>";
		}
		else
		{
			if ($rank > 2 || $rank < 0)
			{
				$alert = "<div class='alert alert-danger' role='alert'>Invalid Rank</div>";
			}
			else
			{
				setUserRank($rank, $userid);
				
				$badge = $pdo->prepare("DELETE FROM user_badges WHERE uid = :u AND (bid = 2 OR bid = 3)");
				$badge->bindParam(":u", $userid, PDO::PARAM_INT);
				$badge->execute();
				if ($rank > 0)
				{
					$newbadge = 0;
					if ($rank == 1)
					{
						$newbadge = 2;
					}
					elseif($rank == 2)
					{
						$newbadge = 3;
					}
					
					giveBadge($newbadge, $userid);
				}
			}
		}
	}
}

$b = $pdo->prepare("SELECT * FROM users WHERE rank > 0 ORDER BY rank DESC");
$b->execute();

$rankshtml = "";
if ($b->rowCount() > 0)
{
	foreach ($b as $staffinfo)
	{
		$username = $staffinfo['username'];
		$rank = $staffinfo['rank'];

		switch ($rank)
		{
			case 1:
				$rank = "Moderator";
				break;
			case 2: 
				$rank = "Administrator";
				break;
			case 3:
				$rank = "Owner";
				break;
			default:
				break;
		}
		
		$rankshtml .= <<<EOT
		<tr>
			<td>{$username}</td>
			<td>{$rank}</td>
		</tr>
EOT;
	}
}

$body = <<<EOT
<div class="container text-center">
{$alert}
	<h5>Rank Management</h5>
	<div class="card" style="max-width: 38rem;margin: auto;">
		<div class="card-body">
			<form method="post">
				<div class="row">
					<div class="col-sm">
						<div class="input-group">
							<input type="text" name="rankuser" class="form-control" placeholder="Username">
							<select class="form-control" name="newrank">
								<option value="0">User</option>
								<option value="1">Moderator</option>
								<option value="2">Administrator</option>
							</select>
							<div class="input-group-append">
								<button type="submit" class="btn btn-success" type="button">Rank</button>
							</div>
						</div>
					</div>
				</div>
				<br>
			</form>
			<div class="text-center">
				<p>
					<button class="btn btn-success w-100" type="button" data-toggle="collapse" data-target="#stafflist" aria-expanded="false" aria-controls="stafflist">Staff</button>
				</p>
				<div class="collapse" id="stafflist">
					<table class="table atable-dark">
						<thead>
							<tr>
								<th>Username</th>
								<th>Rank</th>
							</tr>
						</thead>
						<tbody>
							{$rankshtml}
						</tbody>
					</table>
				</div>
			</div>
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