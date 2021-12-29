<?php

use Alphaland\Users\User;
use Alphaland\Web\WebContextManager;

$body = "";
if(isset($_GET['id'])) 
{
	$id = (int)$_GET['id'];
	$aInfo = getAssetInfo($id);
	$alert = "";
	
	//handle purchasing items
	if(isset($_POST['buyitem'])) 
	{
		$result = buyItem($id);
		if ($result == 0)
		{
			$alert = "<div class='alert alert-danger' role='alert'>You don't have enough Alphabux</div>";
		}
		elseif ($result == 1)
		{
			$alert = "<div class='alert alert-danger' role='alert'>You already own this item</div>";
		}
		elseif ($result == 2)
		{
			WebContextManager::Redirect("/catalog/view?id=". $id . "");
		}
	}
	// ...
	
	//Query
	$q = $pdo->prepare("SELECT * FROM assets WHERE id = :i");
	$q->bindParam(":i", $id, PDO::PARAM_INT);
	$q->execute();
	
	if($q->rowCount() > 0)
	{
		$i = $q->fetch(PDO::FETCH_OBJ);
		
	    $sales = itemSalesCount($id);
		$itemrender = getAssetRender($id);
		$ownerrender = getPlayerRender($i->CreatorId);
		$itemtypeint = $i->AssetTypeId;
		
		$description = '';
		//handle item descriptions
		if (empty($i->Description))
		{
			$description = 'No description available.'; //default description if none is set
		}
		else
		{
			$description = cleanOutput($i->Description);
		}
		// ...
		
		//proper named category
		$types = assetTypeArray();
		//so we can show the item type and view the proper image
		$itemtype = $types[$i->AssetTypeId];
		// ...
		
		//redirect if a game
		if ($i->AssetTypeId == 9)
		{
			WebContextManager::Redirect("/games/view?id=" . $id);
		}
		// ...
		
		$buy_button = "";
		$confirmbuy_button = "";
		if (User::OwnsAsset($user->id, $id))
		{
			//already owns the hat
			$buy_button = '<button class="btn btn-danger" style="width:12rem;" disabled><b>Owned</b></button>';
			
			$confirmbuy_button = '<button class="btn btn-danger" style="width:12rem;" disabled><b>Owned</b></button>';
			// ...
		}
		else
		{
			if ($i->IsForSale == 0 || isAssetModerated($id))
			{
				//offsale
				$buy_button = '<button class="btn btn-secondary" style="width:12rem;" disabled><b>Offsale</b></button>';
				
				$confirmbuy_button = '<button class="btn btn-secondary" style="width:12rem;" disabled><b>Offsale</b></button>';
				// ...
			}
			else
			{
				$price = 0;
				if ($i->PriceInAlphabux == 0)
				{
					$ButtonText = "";
					$src = "";
					if ($i->AssetTypeId == 10) //models
					{
						$ButtonText = "Take";
					}
					else 
					{
						$ButtonText = "FREE!";
						$src = getCurrentThemeAlphabuxLogo();
					}
					
					$buy_button = '<button type="button" data-toggle="modal" data-target="#buyitem" class="btn btn-danger w-100"><img style="max-width:20px;" src="'.$src.'"> <b>'.$ButtonText.'</b></button>';
					$confirmbuy_button = '<button type="submit" name="buyitem" class="btn btn-danger" style="width:12rem;"><img style="max-width:20px;" src="'.$src.'"> <b>'.$ButtonText.'</b></button>';
					// ...
				}
				else
				{
					//aw, its paid
					$buy_button = '<button type="button" data-toggle="modal" data-target="#buyitem" class="btn btn-danger w-100"><img style="max-width:20px;" src="/alphaland/cdn/imgs/alphabux-white-1024.png"> <b>'.cleanOutput($i->PriceInAlphabux).'</b></button>';
					
					$confirmbuy_button = '<button type="submit" name="buyitem" class="btn btn-danger" style="width:12rem;"><img style="max-width:20px;" src="/alphaland/cdn/imgs/alphabux-white-1024.png"> <b>'.cleanOutput($i->PriceInAlphabux).'</b></button>';
					// ...
				}
			}
		}
		
		//only allow shirts, pants and t shirts to be modified by the end user (admins can regardless)
		$configbutton_html = "";
		if ($itemtypeint == 2 or $itemtypeint == 11 or $itemtypeint == 12 or $user->IsAdmin())
		{
			if (isOwner($id) && !isAssetModerated($id)) //owner of the item or admin
			{
				
				$configbutton_html = <<<EOT
				<div class="col-sm">
					<a style="float:right;" class="btn btn-danger text-white" href="config?id={$id}">Configure</a>
				</div>
EOT;
			}
		}
		// ...
	
		$body = '
			<div class="container-fluid">
				<div class="container">
					<div id = "success_alert" class="alert alert-success" role="alert" style="display:none";></div>
					<div id = "error_alert" class="alert alert-danger" role="alert" style="display:none";></div>
					'.$alert.'
					<div class="row marg-bot-15">
						<div class="col-sm-9 marg-bot-15">
							<div class="card marg-auto">
								<div class="card-body">
									<div class="row">
										<div clas="col-sm" style="padding-left:16px;">
											<h5>'.cleanOutput($i->Name).'</h5>
										</div>
										'.$configbutton_html.'
									</div>
									<div class="container text-center">
										<img class="img-fluid" style="width:22rem;'.($itemtypeint == 18 ? 'background-color: rgba(255, 255, 255, 0.15);' : '').'" src="'.$itemrender.'">
									</div>
									<h5>Item Description</h5>
									<hr>
									<div style="overflow:hidden;">
									<p>'.$description.'</p>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-3 marg-bot-15">
							<div class="card">
								<div class="card-body marg-auto">
									<div class="card marg-bot-15" style="width:12rem;">
									<a href="/profile/view?id='.($i->CreatorId).'">
										<div class="card-body">
											<img class="card-img" src="'.$ownerrender.'">
										</div>
									</a>
									</div>
									<div class="text-center">
										<p>Creator: <a class="red-a" href="/profile/view?id='.$i->CreatorId.'">'.getUsername($i->CreatorId).'</a></p>
									</div>
									<hr>
									<div class="text-center">
										<h6>Item Stats</h6>
									</div>
									<div class="row mt-2">
										<div class="col-sm">
											<b>Sales:</b>
											<p>'.$sales.'</p>
											<b>Created:</b>
											<p> '.date("m/d/Y", $i->Created).'</p>
											<b>Item Type:</b>
											<p>'.$itemtype.'</p>
											<hr>
											'.$buy_button.'
											<div class="modal fade" id="buyitem" tabindex="-1" role="dialog" aria-labelledby="buyitemLabel" aria-hidden="true">
												<div class="modal-dialog" role="document">
													<div class="modal-content">
														<div class="modal-header">
														<h5 class="modal-title" id="exampleModalLabel">Are you sure?</h5>
														<button type="button" class="close" data-dismiss="modal" aria-label="Close">
															<span aria-hidden="true">&times;</span>
														</button>
													</div>
													<div class="modal-body">
														Are you sure you want to purchase this item for <img style="max-width:16px;" src="'.getCurrentThemeAlphabuxLogo().'"> <b>'.cleanOutput($i->PriceInAlphabux).'</b> ?
													</div>
														<div class="modal-footer">
															<form action="" method="post">
																'.$confirmbuy_button.'
															</form>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div id="comments_html" class="row" style="display:none">
						<div class="col-sm">
							<div class="card">
								<div class="card-body">
									<h5 class="mb-2">Comments</h5>
										<div class="input-group">
											<input type="text" class="form-control" id="comment_input" autocomplete="off" placeholder="New Comment">
											<div class="input-group-append">
												<button onclick="comments.submitComment($(\'#comment_input\').val())" class="btn btn-danger">Submit</button>	
											</div>
										</div>
									<hr>
									<div id="comments-container" class="container">
																
									</div>
									<div id="page-buttons-container" class="text-center mt-2">
										<div id="page-buttons" class="btn-group" role="group" aria-label="First group">

										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<script>
			comments = new Comments(new URLSearchParams(window.location.search).get("id"), "#comments_html", "#comments-container", "#page-buttons", "#success_alert", "#error_alert", "#comment_input", 2000, "comments")
			</script>';
	}
	else
	{
		//item doesnt exist
		WebContextManager::Redirect("/404");
	}
}
else 
{
	//no url parameter
	WebContextManager::Redirect("/");
}
pageHandler();
$ph->pageTitle(cleanOutput($i->Name));
$ph->body = $body;
$ph->output();