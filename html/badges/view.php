<?php

use Finobe\Web\WebContextManager;

$body = "";
if(isset($_GET['id'])) 
{
	$id = (int)$_GET['id'];
	
	$q = $pdo->prepare("SELECT * FROM badges WHERE id = :i");
	$q->bindParam(":i", $id, PDO::PARAM_INT);
	$q->execute();
	
	if($q->rowCount() > 0)
	{
		$i = $q->fetch(PDO::FETCH_OBJ);

		$creatorid = getUserBadgeOwner($i->id);
		$badgeimage = getUserBadgeImage($id);
		$awardingplace = $i->AwardingPlaceID;
		$awardingplacethumbnail = handleGameThumb($awardingplace);
		$awardingplacename = getAssetInfo($awardingplace)->Name;
		$ownerrender = getPlayerRender($creatorid);
		
		$description = '';
		if (empty($i->Description)) {
			$description = 'No description available.';
		} else {
			$description = cleanOutput($i->Description);
		}
		// ...
	
		$body = '
			<div class="container-fluid">
				<div class="container">
					<div id = "success_alert" class="alert alert-success" role="alert" style="display:none";></div>
					<div id = "error_alert" class="alert alert-danger" role="alert" style="display:none";></div>
					'.$alert.'
					<div class="row marg-bot-15">
						<div class="col-sm-12 marg-bot-15">
							<div class="card marg-auto">
								<div class="card-body">
									<div class="row">
										<div class="col-sm" style="padding-left:16px;">
											<h4>'.cleanOutput($i->Name).'</h5>
											<h6>By: <a class="red-a" href="/profile/view?id='.$creatorid.'">'.getUsername($creatorid).'</a></h4>
											<h6>Description: '.cleanOutput($description).'</h6>
											<h6>Created: '.date("m/d/Y", $i->Created).'</h6>
										</div>
									</div>
									<div class="container text-center">
										<img class="img-fluid" style="width:20rem;" src="'.$badgeimage.'">
									</div>
									<hr>
									<h6>Earn this badge at: <a class="red-a" href="/games/view?id='.$awardingplace.'">'.$awardingplacename.'</a></h6>
								</div>
							</div>
						</div>	
					</div>
				</div>
			</div>';
	}
	else
	{
		//item doesnt exist
		WebContextManager::Redirect("../../404");
	}
}
else 
{
	//no url parameter
	WebContextManager::Redirect("../../404");
}
pageHandler();
$ph->pageTitle(cleanOutput($i->Name));
$ph->body = $body;
$ph->output();