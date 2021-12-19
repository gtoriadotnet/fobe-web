<?php

use Alphaland\Moderation\UserModerationManager;
use Alphaland\Web\WebContextManager;

if (UserModerationManager::IsBanned($user->id))
{
	$banInfo = $pdo->prepare("SELECT * FROM user_bans WHERE uid = :id AND valid = 1");
	$banInfo->bindParam(":id", $user->id, PDO::PARAM_INT);
	$banInfo->execute();
	$banInfo = $banInfo->fetch(PDO::FETCH_OBJ);

	$banType = (int)$banInfo->banType;
	$banDate = date("m/d/Y", $banInfo->whenBanned);
	$banExpiration = date("m/d/Y", $banInfo->banExpiration);
	$banreason = cleanOutput($banInfo->banReason);
								
	if(isset($_POST['logout'])) 
	{
		$user->Logout();
		WebContextManager::Redirect("/");
	}
								
	if(isset($_POST['agree'])) 
	{
		if ($banType == 0) //warning
		{
			//user agreed to read the warning, remove the ban
			$unban = $pdo->prepare("UPDATE user_bans SET valid = 0 WHERE uid = :u");
			$unban->bindParam(":u", $user->id, PDO::PARAM_INT);
			$unban->execute();
			WebContextManager::Redirect("/");
		}
		elseif ($banType == 1) //temporary
		{
			if ($banInfo->banExpiration <= time()) //ban expired, make sure the user agreeing isn't sending a post request without
			{
				//user agreed to read the warning, remove the ban
				$unban = $pdo->prepare("UPDATE user_bans SET valid = 0 WHERE uid = :u");
				$unban->bindParam(":u", $user->id, PDO::PARAM_INT);
				$unban->execute();
				WebContextManager::Redirect("/");
			}
		}
	}
								
	$bandisplay = "";
	$date = "";
	$banexpirationdisplay = "";
	$banagreement = "";
	if ($banType == 0)	
	{
		//warning stuff
		$bandisplay = '<h5 class="text-center mb-3">You\'ve received a warning</h5>';
		$date = '<p><b>Reviewed:</b> '.$banDate.'</p>';
		$banagreement = '<button type="submit" name="agree" class="btn btn-success">I\'ve read the warning</button><br><br>';
	}
	elseif ($banType == 1)	
	{
		//temporary ban stuff
		$bandisplay = '<h5 class="text-center mb-3">You\'ve been temporarily banned</h5>';
		$date = '<p><b>Reviewed:</b> '.$banDate.'</p>';
		$banexpirationdisplay = '<p><b>Expiration:</b> '.$banExpiration.'</p>';
										
		if ($banInfo->banExpiration <= time()) //ban expired
		{
			$banagreement = '<button type="submit" name="agree" class="btn btn-success">I\'ve read the ban reason</button><br><br>';
		}
	}
	elseif ($banType == 2)	
	{
		//permanent ban stuff
		$bandisplay = '<h5 class="text-center mb-3">You\'ve been permanently banned</h5>';
		$date = '<p><b>Reviewed:</b> '.$banDate.'</p>';
	}
	
	echo getCSS(); //print out site css

	echo "<title>Notice</title>"; //set page title
	
	echo '
	<div class="container mt-5">
		<div class="card">
			<div class="card-body">
				'.$bandisplay.'
				'.$date.'
				'.$banexpirationdisplay.'
				<p><b>Reason:</b> '.$banreason.'</p>
				<hr>
				<div class="container text-center">
					<form method="post">
						'.$banagreement.'
						<button type="submit" name="logout" class="btn btn-danger">Logout</button>
					</form>
				</div>
			</div>
		</div>
	</div>
	';	
}
else
{
	//not banned
	WebContextManager::Redirect("/");	
}