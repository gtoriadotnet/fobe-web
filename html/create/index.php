<?php

/*
Alphaland 2021
*/

$body = '';
$alert = '';
$cosmuploadsuccess = $_GET['cosSuccess'];
$placesuccess = $_GET['placeSuccess'];
$pbssuccess = $_GET['pbsSuccess'];

//dont want to be posting same data after refresh (ghetto until JS implementation)
if ($cosmuploadsuccess)
{
	$alert = "<div class='alert alert-success' role='alert'>Uploaded asset</div>";
}
if ($placesuccess)
{
	$alert = "<div class='alert alert-success' role='alert'>Created place</div>";
}
if ($pbssuccess)
{
	$alert = "<div class='alert alert-success' role='alert'>Created Personal Server</div>";
}

function uploadCosmetic()
{
	//upload directories
	$uploadDirectory = $GLOBALS['assetCDNPath']; //directory where the assets are stored
	$textureUploadDirectory = $GLOBALS['thumbnailCDNPath']; //directory where the textures are stored
	$xmlTemplatesDirectory = "../../default_xmls/"; //path to default xml directory
	
	//allowed image types 
	$types = array('image/png');
	
	//generate new hashes
	$xmlhash = genAssetHash(16);
	$texturehash = genAssetHash(16);
		
	//post variables
	$price = (int)$_POST['asset_price'];
	$image = $_FILES['asset_file']['tmp_name'];
	$name = $_POST['asset_name'];
	$description = $_POST['asset_desc'];

	//variables used for checks
	$assettype = -1;
	$minimumprice = 0;
	$maxwidth = 0;
	$maxheight = 0;
	$xmlfile = "";
	$isapproved = false;
	$onsale = false;
	
	//time for a lot of checks
	
	//onsale
	if (isset($_POST['onsale_checkbox']))
	{
		$onsale = true;
	}

	//chosen asset type
	if (isset($_POST['tshirt_checkbox']))
	{
		$xmlfile = $xmlTemplatesDirectory . "tshirts";
		$minimumprice = 2;
		$assettype = 2;
		$maxwidth = 2048;
		$maxheight = 2048;
	}
	elseif (isset($_POST['shirt_checkbox']))
	{
		$xmlfile = $xmlTemplatesDirectory . "shirts";
		$minimumprice = 5;
		$assettype = 11;
		$maxwidth = 585;
		$maxheight = 559;
	}
	elseif (isset($_POST['pants_checkbox']))
	{
		$xmlfile = $xmlTemplatesDirectory . "pants";
		$minimumprice = 5;
		$assettype = 12;
		$maxwidth = 585;
		$maxheight = 559;
	}
	else
	{
		return "Please choose an asset type";
	}
	
	//price
	if ($onsale)
	{
		if (!is_int($price)) //price isnt integer
		{
			return "Price must be an integer";
		}

		if ($price < $minimumprice)
		{
			return "Price too low, must be atleast " . $minimumprice . " Alphabux";
		}
		
		if ($price < 1)
		{
			return "Price cannot be 0";
		}
	}
	else
	{
		$price = 0;
	}
		
	//check if image is posted
	if (!file_exists($image) || !is_uploaded_file($image))
	{
		return "Please provide an image";
	}
		
	//verify that its a valid .png or .jpeg via mimetype, if shirt or pants verify the template is valid
	$type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $image);
	if (in_array($type, $types)) 
	{	
		//check dimensions
		$imagedetails = getimagesize($image);
		$width = $imagedetails[0];
		$height = $imagedetails[1];
		
		//verify dimensions of shirts or pants and if png
		if ($assettype == 11 || $assettype == 12) //11 is shirt, 12 is pants
		{
			if ($width != $maxwidth || $height != $maxheight || !in_array($type, array('image/png'))) //invalid template
			{
				return "Invalid template provided";
			}
		}
		else
		{
			//check if image is too large
			if ($width > $maxwidth || $height > $maxheight) //too big
			{
				return "Image is too big";
			}
		}
	}
	else
	{
		return "Invalid image, must be png";
	}
	
	//name checks
	if (strlen($name) > 50)
	{
		return "Provided name is too long";
	}
		
	if (strlen($name) < 3)
	{
		return "Provided name is too short";
	}
	
	//description check
	if (strlen($description) > 1000)
	{
		return "Provided description too long";
	}
	
	//remove currency
	if (!removeCurrency($minimumprice, "Creation of cosmetic name ".$name))
	{
		return "You don't have enough currency";
	}
	
	//POINT OF NO RETURN, ALL CHECKS PASSED
										
	//setup the new asset in the DB, lock it!
	$GLOBALS['pdo']->exec("LOCK TABLES assets WRITE"); //lock since this stuff is sensitive

	$b = $GLOBALS['pdo']->prepare("SELECT * FROM assets");
	$b->execute();
										
	//grab auto increment values
	$autoincrement = $b->rowCount() + 1; //initial auto increment value
	$autoincrement2 = $autoincrement+1; //initial auto increment value + 1
													
	//add XML (selected type) to assets
	$m = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`, `Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,:atid,:aname,:adesc,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),:oid,:aid2,:price,0,0,:onsale,1,0,0,:ia,0,0,0,0,0,8,0,0,:hash)");
	$m->bindParam(":aid", $autoincrement, PDO::PARAM_INT);
	$m->bindParam(":atid", $assettype, PDO::PARAM_INT);
	$m->bindParam(":aname", $name, PDO::PARAM_STR);
	$m->bindParam(":adesc", $description, PDO::PARAM_STR);
	$m->bindParam(":oid", $GLOBALS['user']->id, PDO::PARAM_STR);
	$m->bindParam(":aid2", $autoincrement, PDO::PARAM_INT);
	$m->bindParam(":ia", $isapproved, PDO::PARAM_INT);
	$m->bindParam(":price", $price, PDO::PARAM_INT);
	$m->bindParam(":onsale", $onsale, PDO::PARAM_INT);
	$m->bindParam(":hash", $xmlhash, PDO::PARAM_STR);
	$m->execute();
										
	//add texture to assets
	$name = $name . " Texture";
	$x = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`, `Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,1,:aname,'Shirt Image',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),:oid,:aid2,0,0,0,0,1,0,0,:ia,0,0,0,0,0,8,0,0,:hash)");
	$x->bindParam(":aid", $autoincrement2, PDO::PARAM_INT);
	$x->bindParam(":aname", $name, PDO::PARAM_STR);
	$x->bindParam(":oid", $GLOBALS['user']->id, PDO::PARAM_INT);
	$x->bindParam(":aid2", $autoincrement2, PDO::PARAM_INT);
	$x->bindParam(":ia", $isapproved, PDO::PARAM_INT);
	$x->bindParam(":hash", $texturehash, PDO::PARAM_STR);
	$x->execute();
		
	//unlock since we are done with sensitive asset stuff				
	$GLOBALS['pdo']->exec("UNLOCK TABLES"); 
								
	//give the creator the asset
	giveItem($GLOBALS['user']->id, $autoincrement);
											
	//upload texture and edit xml template, copy to assets
	move_uploaded_file($image, $textureUploadDirectory . $texturehash);
	$str = file_get_contents($xmlfile);
	$str=str_replace("TEXTUREURLPLACEHOLDER", $GLOBALS['url'] . "/asset/?id=" . $autoincrement2, $str);
	file_put_contents($uploadDirectory . $xmlhash, $str);
	
	return true;
}

function newPlace()
{
	//upload parameters
	$uploadDirectory = $GLOBALS['assetCDNPath']; //cdn where the game/asset is stored
	
	//post variables
	$name = $_POST['place_name'];
	$description = $_POST['place_desc'];
	
	//how many games the user has
	if (getAllGames($GLOBALS['user']->id)->rowCount() >= 6 && !$GLOBALS['user']->isAdmin())
	{
		return "Games limit reached";
	}
	
	//name checks
	if (strlen($name) > 50)
	{
		return "Provided name is too long";
	}
		
	if (strlen($name) < 3)
	{
		return "Provided name is too short";
	}
	
	//description check
	if (strlen($description) > 1000)
	{
		return "Provided description too long";
	}
	
	//POINT OF NO RETURN 
	
	$newplace = createPlace($GLOBALS['user']->id, $name, $description, 12);
	redirect("/games/config?id=".$newplace);
}

function newPBSPlace($placetype)
{
	$selectedPlacePath = "../../default_pbs_places/" . $placetype . ".rbxlx";

	//post variables
	$name = $_POST['place_name'];
	$description = $_POST['place_desc'];

	//how many games the user has
	if (getAllGames($GLOBALS['user']->id)->rowCount() > 6 && !$GLOBALS['user']->isAdmin())
	{
		return "Games limit reached";
	}
	
	//name checks
	if (strlen($name) > 50)
	{
		return "Provided name is too long";
	}
		
	if (strlen($name) < 3)
	{
		return "Provided name is too short";
	}
	
	//description check
	if (strlen($description) > 1000)
	{
		return "Provided description too long";
	}
	
	//POINT OF NO RETURN 
	$newpbs = createPBSPlace($GLOBALS['user']->id, $name, $description, 12, $selectedPlacePath);

	redirect("/games/pbs/config?id=".$newpbs);
}

if (isset($_POST['SubmitPBSSuperflat']))
{	
	$upload = newPBSPlace("Superflat");
	if ($upload !== true)
	{
		$alert = "<div class='alert alert-danger' role='alert'>" . $upload . "</div>";
	}
	else
	{
		redirect('/create?pbsSuccess=true');
	}
}

if (isset($_POST['SubmitPBSRugged']))
{	
	$upload = newPBSPlace("Rugged");
	if ($upload !== true)
	{
		$alert = "<div class='alert alert-danger' role='alert'>" . $upload . "</div>";
	}
	else
	{
		redirect('/create?pbsSuccess=true');
	}
}

if (isset($_POST['SubmitPBSHappyHome']))
{	
	$upload = newPBSPlace("HappyHome");
	if ($upload !== true)
	{
		$alert = "<div class='alert alert-danger' role='alert'>" . $upload . "</div>";
	}
	else
	{
		redirect('/create?pbsSuccess=true');
	}
}

if (isset($_POST['SubmitPBSBaseplate']))
{	
	$upload = newPBSPlace("Baseplate");
	if ($upload !== true)
	{
		$alert = "<div class='alert alert-danger' role='alert'>" . $upload . "</div>";
	}
	else
	{
		redirect('/create?pbsSuccess=true');
	}
}

if (isset($_POST['PBSNoSelection']))
{
	$alert = "<div class='alert alert-danger' role='alert'>Please choose a template</div>";
}

if (isset($_POST['SubmitAsset']))
{	
	$upload = uploadCosmetic();
	if ($upload !== true)
	{
		$alert = "<div class='alert alert-danger' role='alert'>" . $upload . "</div>";
	}
	else
	{
		redirect('/create?cosSuccess=true');
	}
}

if (isset($_POST['SubmitPlace']))
{
	
	$place = newPlace();
	if ($place !== true)
	{
		$alert = "<div class='alert alert-danger' role='alert'>" . $place . "</div>";
	}
	else
	{
		redirect('/create?placeSuccess=true');
	}
}
	
	$body = <<<EOT
	<div class="container">
	{$alert}
	<form action="" method="post" enctype="multipart/form-data">
		<h5>Create</h5>
		<div class="row">
			<div class="col-sm-3 mb-4">
				<div class="card">
					<div class="card-body text-center">
						<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
							<a class="nav-link active red-a-nounder" id="v-pills-asset-tab" data-toggle="pill" href="#v-pills-asset" role="tab" aria-controls="v-pills-asset" aria-selected="true">Cosmetic</a>
							<a class="nav-link red-a-nounder" id="v-pills-place-tab" data-toggle="pill" href="#v-pills-place" role="tab" aria-controls="v-pills-place">Place</a>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm">
				<div class="card">
					<div class="card-body">
						<div class="tab-content" id="v-pills-tabContent">
							<div class="tab-content" id="v-pills-tabContent">
								<div class="tab-pane fade show active" id="v-pills-asset" role="tabpanel" aria-labelledby="v-pills-asset-tab">
									<h5>Create Cosmetic</h5>
									<h>Shirts/Pants costs 5 Alphabux to create, T-Shirts costs 2 Alphabux to create</h>
									<div class="input-group mb-3">
										<div class="custom-file">
											<input type="file" name="asset_file" class="custom-file-input" id="inputAssetFile">
											<label class="custom-file-label" for="inputAssetFile">Image</label>
										</div>
									</div>
									<div class="text-center">
										<p>
											<button class="btn btn-danger w-100" type="button" data-toggle="collapse" data-target="#assettemp" aria-expanded="false" aria-controls="assettemp">Shirt/Pants Template</button>
										</p>
										<div class="collapse" id="assettemp">
											<img class="img-fluid" src="/alphaland/cdn/imgs/asset-template.png">
										</div>
									</div>
									<hr>
									<input class="form-control mb-3" name="asset_name" type="text" placeholder="Cosmetic Name">
									<textarea class="form-control" name="asset_desc" style="min-height:8rem;max-height:8rem;" placeholder="Cosmetic Description"></textarea>
									<hr>
									<div class="input-group mb-3">
										<div class="input-group-prepend">
											<span class="input-group-text"><img style="width:1rem;" src="/alphaland/cdn/imgs/alphabux-grey-1024.png"></span>
										</div>
										<input type="text" name="asset_price" class="form-control" placeholder="Price">
									</div>
									<div class="container text-center marg-bot-15">
										<h6>Cosmetic Type</h6>
										<div class="custom-control custom-checkbox custom-control-inline">
											<input type="checkbox" name="tshirt_checkbox" class="custom-control-input sev_check" id="asset1">
											<label class="custom-control-label" for="asset1">T-Shirt</label>
										</div>
										<div class="custom-control custom-checkbox custom-control-inline">
											<input type="checkbox" name="shirt_checkbox" class="custom-control-input sev_check" id="asset2">
											<label class="custom-control-label" for="asset2">Shirt</label>
										</div>
										<div class="custom-control custom-checkbox custom-control-inline">
											<input type="checkbox" name="pants_checkbox" class="custom-control-input sev_check" id="asset3">
											<label class="custom-control-label" for="asset3">Pants</label>
										</div>
										<div class="container text-center marg-bot-15">
											<div class="custom-control custom-checkbox custom-control-inline">
												<input type="checkbox" name="onsale_checkbox" class="custom-control-input" id="onsale">
												<label class="custom-control-label" for="onsale">On-Sale</label>
											</div>
										</div>
									</div>
									<script type="text/javascript">
										$('.sev_check').click(function() {
										  $('.sev_check').not(this).prop('checked', false);
										});
									</script>
									<input type="submit" name="SubmitAsset" value="Create Asset" class="btn btn-lg btn-danger w-100">
								</div>
								<div class="tab-pane fade" id="v-pills-place" role="tabpanel" aria-labelledby="v-pills-place-tab">
									<div class="row mb-2">
										<div class="col-sm">
											<div id="RegPlaceInfo">
												<h5>Create Place</h5>
												<h>This will create a default place, limit of 6 places</h>
											</div>
											<div id="PBSPlaceInfo">
												<h5>Create Personal Build Place</h5>
												<h>This will create a Personal Build Game with chosen template, limit of 6 places.</h>
											</div>
										</div>
										<div class="col-sm-3" id="enable_regulargame_checkbox">
											<button type="button" onclick="setPlaceRegular()" class="btn btn-danger w-100 float-right">Default Game</button>
										</div>
										<div class="col-sm-3" id="enable_pbs_checkbox">
											<button type="button" onclick="setPlacePersonalBuild()" class="btn btn-danger w-100 float-right">Personal Build Game</button>
										</div>
									</div>
									<div>
										<h>Place will be configurable after creation</h>
									</div>
									<hr>
									<input class="form-control mb-3" name="place_name" type="text" placeholder="Place Name">
									<textarea class="form-control" name="place_desc" style="min-height:8rem;max-height:8rem;" placeholder="Place Description"></textarea>
									<div id="PBSTemplates" class="mt-2">
										<div class="text-center">
											<h6>Template:</h6>
										</div>
										<div class="pbstempcontainer">
											<ul>
												<li>
													<div id="PBSIDSuperflat" class="pbstempcard" style="cursor: pointer;" onclick="setPBSType('Superflat')">
														<a class="text-center">
															<img class="img-fluid" src="/alphaland/cdn/imgs/Previews/Superflat.png">
															<span><p>Superflat</p></span>
														</a>
													</div>
												</li>
												<li>
													<div id="PBSIDRugged" class="pbstempcard" style="cursor: pointer;" onclick="setPBSType('Rugged')">
														<a class="text-center">
															<img class="img-fluid" src="/alphaland/cdn/imgs/Previews/Rugged.png">
															<span><p>Rugged</p></span>
														</a>
													</div>
												</li>
												<li>
													<div id="PBSIDHappyHome" class="pbstempcard" style="cursor: pointer;" onclick="setPBSType('HappyHome')">
														<a class="text-center">
															<img class="img-fluid" src="/alphaland/cdn/imgs/Previews/Happy Home.png">
															<span><p>Happy Home</p></span>
														</a>
													</div>
												</li>
												<li>
													<div id="PBSIDBaseplate" class="pbstempcard" style="cursor: pointer;" onclick="setPBSType('Baseplate')">
														<a class="text-center">
															<img class="img-fluid" src="/alphaland/cdn/imgs/Previews/Baseplate.png">
															<span><p>Baseplate</p></span>
														</a>
													</div>
												</li>
											</ul>
										</div>
									</div>
									<div id="RegPlaceCreateButton">
									<hr>
										<button type="submit" name="SubmitPlace" class="btn btn-danger w-100">Create</button>
									</div>
									<div id="PBSPlaceCreateButton">
										<button id="PBSSubmitButton" name="PBSNoSelection" class="btn btn-danger w-100 mt-2">Create</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<script>
function setPlaceRegular()
{
	$('#PBSPlaceInfo').hide();
	$('#PBSTemplates').hide();
	$('#PBSPlaceCreateButton').hide();
	$('#enable_regulargame_checkbox').hide();

	$('#RegPlaceInfo').show();
	$('#RegPlaceCreateButton').show();
	$('#enable_pbs_checkbox').show();
}

function setPlacePersonalBuild()
{
	$('#RegPlaceInfo').hide();
	$('#RegPlaceCreateButton').hide();
	$('#enable_pbs_checkbox').hide();
	
	$('#PBSPlaceInfo').show();
	$('#PBSTemplates').show();
	$('#PBSPlaceCreateButton').show();
	$('#enable_regulargame_checkbox').show();
}


var currentTypeId = "";
function setPBSType(type) {
	if (currentTypeId) {
		$(currentTypeId).removeClass("selected-my-group");
	}
	currentTypeId = "#PBSID"+type;
	$(currentTypeId).addClass("selected-my-group");
	$("#PBSSubmitButton").prop('name','SubmitPBS'+type);
}

setPlaceRegular();
</script>
EOT;
	
pageHandler();
$ph->pageTitle("Create");
$ph->body = $body;
$ph->output();