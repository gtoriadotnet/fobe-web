<?php

use Finobe\Assets\Render;
use Finobe\Moderation\UserModerationManager;
use Finobe\Users\User;
use Finobe\Web\WebContextManager;

$body = '';
$alert = "";
if(isset($_GET['id'])) 
{
	$id = (int)$_GET['id'];
	
	if (isAssetModerated($id) || !isOwner($id))
	{
		WebContextManager::Redirect("/");
	}
	
	//Query
	$q = $pdo->prepare("SELECT * FROM assets WHERE id = :i");
	$q->bindParam(":i", $id, PDO::PARAM_INT);
	$q->execute();
	
	if($q->rowCount() > 0) 
	{
			//item parameters
			$iteminfo = getAssetInfo($id);
			$itemname = cleanOutput($iteminfo->Name);
			$itemdescription = cleanOutput($iteminfo->Description);
			$itemprice = $iteminfo->PriceInAlphabux;
			$itemtypeint = $iteminfo->AssetTypeId;
			
			$types = assetTypeArray();
			$itemtype = $types[$itemtypeint];
			
			$itemrender = getAssetRender($id);
			//...
			
			//only allow shirts, pants, t shirts and audios to be modified by the end user
			if ($itemtypeint == 2 or $itemtypeint == 11 or $itemtypeint == 12 or $itemtypeint == 3 or $user->isOwner())
			{
				//handle onsale checkbox
				$onsalestatus = "";
				if ($iteminfo->IsForSale == 0)
				{
					$onsalestatus = "";
				}
				else
				{
					$onsalestatus = "checked";
				}
				//...
				
				if (isset($_POST['Submit']))
				{
					//price check parameters
					$minimumprice = 0;
					$pricealert = "";
					if ($itemtypeint == 2)
					{
						$minimumprice = 2; //tshirt
						$pricealert = "Price too low, must be atleast 2 Alphabux";
					}
					elseif ($itemtypeint == 11)
					{
						$minimumprice = 5; //shirt
						$pricealert = "Price too low, must be atleast 5 Alphabux";
					}
					elseif ($itemtypeint == 12)
					{
						$minimumprice = 5; //pants
						$pricealert = "Price too low, must be atleast 5 Alphabux";
					}
					//...
					
					if (strlen($_POST['item_name']) < 3)
					{
						$alert = "<div class='alert alert-danger' role='alert'>Item name too short, must be over 3 characters</div>";
					}
					/*
					elseif(strlen($_POST['item_description']) < 3)
					{
						$alert = "<div class='alert alert-danger' role='alert'>Item description too short, must be over 3 characters</div>";
					}
					*/
					elseif(strlen($_POST['item_price']) < 1 && $itemtypeint != 3) // no audios
					{
						$alert = "<div class='alert alert-danger' role='alert'>Item price too short, must be at least 1 character</div>";
					}
					elseif(strlen($_POST['item_name']) > 50)
					{
						$alert = "<div class='alert alert-danger' role='alert'>Item name too long, must be under 50 characters</div>";
					}
					elseif(strlen($_POST['item_description']) > 1000)
					{
						$alert = "<div class='alert alert-danger' role='alert'>Item description too long, must be under 1k characters</div>";
					}
					elseif(strlen($_POST['item_price']) > 8 && $itemtypeint != 3) // no audios
					{
						$alert = "<div class='alert alert-danger' role='alert'>Item price too short, must be under 8 characters</div>";
					}
					elseif($_POST['item_price'] < $minimumprice && $itemtypeint != 3) // no audios
					{
						$alert = "<div class='alert alert-danger' role='alert'>{$pricealert}</div>";
					}
					else
					{
						if ($user->IsStaff())
						{
							UserModerationManager::LogAction("Configure Item ".$id);
						}

						//update item name
						$c = $pdo->prepare("UPDATE assets SET Name = :n, Updated = UNIX_TIMESTAMP() WHERE id = :i");
						$c->bindParam(":n", $_POST['item_name'], PDO::PARAM_STR); //item name
						$c->bindParam(":i", $id, PDO::PARAM_INT); //catalog id
						$c->execute();
						// ...
						
						//update item description
						$c = $pdo->prepare("UPDATE assets SET Description = :n, Updated = UNIX_TIMESTAMP() WHERE id = :i");
						$c->bindParam(":n", $_POST['item_description'], PDO::PARAM_STR); //item description
						$c->bindParam(":i", $id, PDO::PARAM_INT); //catalog id
						$c->execute();
						// ...

						if($itemtypeint != 3) // Audios
						{
							//update item price
							$c = $pdo->prepare("UPDATE assets SET PriceInAlphabux = :n, Updated = UNIX_TIMESTAMP() WHERE id = :i");
							$c->bindParam(":n", $_POST['item_price'], PDO::PARAM_INT); //item price
							$c->bindParam(":i", $id, PDO::PARAM_INT); //catalog id
							$c->execute();
							// ...
						}
							
						if (isset($_POST['onsale_checkbox']))
						{
							if ($user->IsStaff())
							{
								UserModerationManager::LogAction("Configure Item Onsale ".$id);
							}

							//update onsale
							$onsale = 1;
							$c = $pdo->prepare("UPDATE assets SET IsForSale = :n, Updated = UNIX_TIMESTAMP() WHERE id = :i");
							$c->bindParam(":n", $onsale, PDO::PARAM_INT); //item name
							$c->bindParam(":i", $id, PDO::PARAM_INT); //catalog id
							$c->execute();
							// ...
						}
						else
						{
							UserModerationManager::LogAction("Configure Item Offsale ".$id);

							//update onsale
							$onsale = 0;
							$c = $pdo->prepare("UPDATE assets SET IsForSale = :n, Updated = UNIX_TIMESTAMP() WHERE id = :i");
							$c->bindParam(":n", $onsale, PDO::PARAM_INT); //item name
							$c->bindParam(":i", $id, PDO::PARAM_INT); //catalog id
							$c->execute();
							// ...
						}
						
						WebContextManager::Redirect("config?id={$id}");
					}
				}
				elseif (isset($_POST['RegenItem'])) //for admin regen stuff
				{
					if ($user->IsStaff() && $itemtypeint != 3) // Staff and not audio
					{
						$script = "";
						$scripttype = "";

						UserModerationManager::LogAction("Render Item ".$id);
						if ($itemtypeint == 8)
						{
							//Hat
							if (!Render::RenderHat($id))
							{
								$alert = "<div class='alert alert-danger' role='alert'>Render Hat Failed</div>";
							}
							else
							{
								$alert = "<div class='alert alert-success' role='alert'>Render Hat Succeeded</div>";
							}
						}
						elseif ($itemtypeint == 2)
						{
							//T Shirt
							if (!Render::RenderTShirt($id))
							{
								$alert = "<div class='alert alert-danger' role='alert'>Render TShirt Failed</div>";
							}
							else
							{
								$alert = "<div class='alert alert-success' role='alert'>Render TShirt Succeeded</div>";
							}
						}		
						elseif ($itemtypeint == 4)
						{
							//Mesh
							if (!Render::RenderMesh($id))
							{
								$alert = "<div class='alert alert-danger' role='alert'>Render TShirt Failed</div>";
							}
							else
							{
								$alert = "<div class='alert alert-success' role='alert'>Render TShirt Succeeded</div>";
							}
						}								
						elseif ($itemtypeint == 11)
						{
							//Shirt
							if (!Render::RenderShirt($id))
							{
								$alert = "<div class='alert alert-danger' role='alert'>Render Shirt Failed</div>";
							}
							else
							{
								$alert = "<div class='alert alert-success' role='alert'>Render Shirt Succeeded</div>";
							}
						}						
						elseif ($itemtypeint == 12)
						{
							//Pants
							if (!Render::RenderPants($id))
							{
								$alert = "<div class='alert alert-danger' role='alert'>Render Pants Failed</div>";
							}
							else
							{
								$alert = "<div class='alert alert-success' role='alert'>Render Pants Succeeded</div>";
							}
						}
						elseif ($itemtypeint == 18)
						{
							//Faces
							if (!Render::RenderFace($id))
							{
								$alert = "<div class='alert alert-danger' role='alert'>Render Face Failed</div>";
							}
							else
							{
								$alert = "<div class='alert alert-success' role='alert'>Render Face Succeeded</div>";
							}
						}
						elseif ($itemtypeint == 19)
						{
							//Gears
							if (!Render::RenderGear($id))
							{
								$alert = "<div class='alert alert-danger' role='alert'>Render Gear Failed</div>";
							}
							else
							{
								$alert = "<div class='alert alert-success' role='alert'>Render Gear Succeeded</div>";
							}
						}
						elseif ($itemtypeint == 17)
						{
							//Heads
							if (!Render::RenderHead($id))
							{
								$alert = "<div class='alert alert-danger' role='alert'>Render Head Failed</div>";
							}
							else
							{
								$alert = "<div class='alert alert-success' role='alert'>Render Head Succeeded</div>";
							}
						}
						elseif ($itemtypeint == 32)
						{
							//Packages
							if (!Render::RenderPackage($id))
							{
								$alert = "<div class='alert alert-danger' role='alert'>Render Package Failed</div>";
							}
							else
							{
								$alert = "<div class='alert alert-success' role='alert'>Render Package Succeeded</div>";
							}
						}
						elseif ($itemtypeint == 10)
						{
							//Models
							if (!Render::RenderModel($id))
							{
								$alert = "<div class='alert alert-danger' role='alert'>Render Model Failed</div>";
							}
							else
							{
								$alert = "<div class='alert alert-success' role='alert'>Render Model Succeeded</div>";
							}
						}
					}
					else
					{
						$alert = "<div class='alert alert-danger' role='alert'>An error occurred</div>";
					}
				}
				elseif (isset($_POST['ModerateItem'])) //for mods
				{
					if ($user->IsStaff())
					{
						$moderation = moderateAsset($id);

						if ($moderation !== TRUE)
						{
							$alert = "<div class='alert alert-danger' role='alert'>".$moderation."</div>";
						}
						else
						{
							WebContextManager::Redirect("/catalog/view?id=".$id);
						}
					}
				}
			}
			else
			{
				//not a modifiable asset (to the end user)
				WebContextManager::Redirect("/");
			}
	}
	else
	{
		//catalog item doesnt exist
		WebContextManager::Redirect("/");
	}
}
else
{
	//no url parameter
	WebContextManager::Redirect("/");	
}

$moderatebutton = '';
$regenbutton = '';
$itempricebutton = '';
$itemimage = '';
if($itemtypeint != 3) {
	$itempricebutton = '<div class="container input-group mb-3">
		<div class="input-group-prepend">
			<span class="input-group-text"><img style="width:1rem;" src="/finobe/cdn/imgs/alphabux-grey-1024.png"></span>
		</div>
		<input type="text" name="item_price" class="form-control" value="' . $itemprice . '">
	</div>';
}

$itemimage = '<img class="img-fluid" style="width:20rem;" src="' . ($itemtypeint != 3 ? $itemrender : getImageFromAsset(1466)) . '">';

if ($user->IsStaff())
{
	if($itemtypeint != 3) {
		$regenbutton = '<button type="Submit" name="RegenItem" class="btn btn-danger w-100 mb-2">Regen '.$itemtype.'</button>';
	}
	
	$moderatebutton = '<button type="Submit" name="ModerateItem" class="btn btn-danger w-100 mb-2">Moderate '.$itemtype.'</button>';
}

$body = <<<EOT
<div class="container-fluid">
	<form action="" method="post">
		<div class="container">
		{$alert}
			<div class="card">
				<div class="card-body">
					<div class="row">
						<div class="col-sm">
							<h5>Configure {$itemname}</h5>
							<hr>
							<div class="container text-center marg-bot-15">
								<label style="float:left;">{$itemtype} Name</label>
								<input class="form-control" type="text" name="item_name" value="{$itemname}">
							</div>
							<div class="container text-center">
								{$itemimage}
							</div>
							<div class="container text-center">
								<div class="custom-control custom-checkbox custom-control-inline">
									<input type="checkbox" name="onsale_checkbox" {$onsalestatus} class="custom-control-input" id="onsale">
									<label class="custom-control-label" for="onsale">On-Sale</label>
								</div>
							</div>
							<div class="container text-center marg-bot-15">
								<label style="float:left;text-align:top;">{$itemtype} Description</label>
								<textarea style="min-height:10rem;max-height:10rem;" class="form-control" type="text" name="item_description">{$itemdescription}</textarea>
							</div>
							{$itempricebutton}
							<div class="container text-center">
								{$moderatebutton}
								{$regenbutton}
								<button type="Submit" name="Submit" class="btn btn-danger w-100">Update {$itemtype}</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
EOT;

pageHandler();
$ph->body = $body;
$ph->output();