<?php

use Alphaland\Games\Game;
use Alphaland\Web\WebContextManager;

$gearsportion = false;

$body = '';

function resize($newWidth, $newHeight, $targetFile, $originalFile) {

    $info = getimagesize($originalFile);
    $mime = $info['mime'];

    switch ($mime) {
            case 'image/jpeg':
                    $image_create_func = 'imagecreatefromjpeg';
                    $image_save_func = 'imagejpeg';;
                    break;

            case 'image/png':
                    $image_create_func = 'imagecreatefrompng';
                    $image_save_func = 'imagepng';
                    break;

            case 'image/gif':
                    $image_create_func = 'imagecreatefromgif';
                    $image_save_func = 'imagegif';
                    break;

            default: 
                    throw new Exception('Unknown image type.');
    }

    $img = $image_create_func($originalFile);
    list($width, $height) = getimagesize($originalFile);

    $tmp = imagecreatetruecolor($newWidth, $newHeight);
	imagealphablending($tmp , false);
	imagesavealpha($tmp , true);
    imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    if (file_exists($targetFile)) {
            unlink($targetFile);
    }
    $image_save_func($tmp, "$targetFile");
}

function convertToPBSPlace($placetype, $placeid)
{
	$selectedPlacePath = $_SERVER['DOCUMENT_ROOT'] . "/../default_pbs_places/" . $placetype . ".rbxlx";
	$assetcdn = $GLOBALS['assetCDNPath'];
						
	//grab a new hash for the game asset
	$gamehash = genAssetHash(16);
		
	//copy template, set the game type to PBS, update the hash, delete persistence data, close all servers, start place render and redirect
	if (copy($selectedPlacePath, $assetcdn . $gamehash))
	{
		if (gameCloseAllJobs($placeid))
		{
			if (setPBSGame($placeid))
			{
				$set = $GLOBALS['pdo']->prepare("UPDATE assets SET Hash = :hash WHERE id = :i");
				$set->bindParam(":hash", $gamehash, PDO::PARAM_INT);
				$set->bindParam(":i", $placeid, PDO::PARAM_INT);
				$set->execute();
				if ($set->rowCount() > 0)
				{
					//trust mysql!!
					$deletepersistence = $GLOBALS['pdo']->prepare("DELETE FROM persistence WHERE placeid = :i");
					$deletepersistence->bindParam(":i", $placeid, PDO::PARAM_INT);
					$deletepersistence->execute();
	
					handleRenderPlace($placeid);
					WebContextManager::Redirect("/games/pbs/config?id=".$placeid);
				}
			}
		}
		setRegularGame($placeid);
	}
	return "Error converting to PBS";
}

$alert = "";
if(isset($_GET['id'])) 
{
	$id = (int)$_GET['id'];

	if(getAssetInfo($id)->isPersonalServer)
	{
		WebContextManager::Redirect("/games/pbs/config?id=".$id);
	}
	
	//Query
	$q = $pdo->prepare("SELECT * FROM assets WHERE id = :i");
	$q->bindParam(":i", $id, PDO::PARAM_INT);
	$q->execute();
	
	if($q->rowCount() > 0) 
	{
		if (isOwner($id) or $user->isAdmin()) //if the user is the owner of the game, or staff
		{
			//item parameters
			$gameinfo = getAssetInfo($id);
			$gamename = cleanOutput($gameinfo->Name);
			$gamedescription = cleanOutput($gameinfo->Description, false); //pass false to not replace linebreaks with html
			$gamecreator = $gameinfo->CreatorId;
			$gamemaxplayers = $gameinfo->MaxPlayers;
			$gamerender = handleGameThumb($id);
			
			$commentsstatus = '';
			if ($gameinfo->IsCommentsEnabled == true)
			{
				$commentsstatus = 'checked';
			}

			$chatclassic = "";
			$chatbubble = "";
			$chatclassicbubble = "";
			switch (Game::GetChatStyle($id))
			{
				case 0:
					$chatclassic = "checked";
					break;
				case 1:
					$chatbubble = "checked";
					break;
				case 2:
					$chatclassicbubble = "checked";
					break;
				default:
					$chatclassicbubble = "checked";
					break;
			}

			$thumbnailstatus = '';
			if (isPlaceUsingRender($id))
			{
				$thumbnailstatus = 'checked';
			}
			//...
			
			if (isset($_POST['Submit']))
			{
				//some important parameters
					
				//file parameters
				$thumbnailfileExtensionsAllowed = ['png']; // These will be the only file extensions allowed 
					
				//upload parameters
				$thumbnailuploadDirectory = $GLOBALS['thumbnailCDNPath']; //directory where the textures are stored
				$thumbnailHash = genAssetHash(16);
				//$thumbnailuploadDirectory = "../thumbnails/places/"; //directory where the games thumbnails are stored
				// ...
					
				//temp file locations
				$thumbnailfileName = $_FILES['thumbnail_file']['name'];
				$thumbnailfileTmpName  = $_FILES['thumbnail_file']['tmp_name']; //location of the uploaded png file (temp directory)
				$thumbnailfileExtension = strtolower(end(explode('.',$thumbnailfileName)));
				// ...
						
				$usedefaultthumb = false;
				if(!file_exists($_FILES['thumbnail_file']['tmp_name']) || !is_uploaded_file($_FILES['thumbnail_file']['tmp_name'])) 
				{
					$usedefaultthumb = true;
				}
									
				//check dimensions
				$filecheckfail = false;
				$dimensionsfail = false;
							
				//check the image if it exists
				if (!$usedefaultthumb)
				{
					if (in_array($thumbnailfileExtension,$thumbnailfileExtensionsAllowed)) //make sure .png file extension
					{
						$isimage = @imagecreatefrompng($_FILES['thumbnail_file']['tmp_name']); //check if the file is actually a PNG image
						
						if ($isimage)
						{
							$imagedetails = getimagesize($_FILES['thumbnail_file']['tmp_name']);
							$width = $imagedetails[0];
							$height = $imagedetails[1];
																		
							if ($width > 1920) //over 1920 width, too big
							{
								$dimensionsfail = true;
							}
															
							if ($height > 1080) //over 1080 height, too big
							{
								$dimensionsfail = true;
							}
						}
						else
						{
							$filecheckfail = true;
						}
					}
					else
					{
						$filecheckfail = true;
					}
				}
							
				if ($filecheckfail)
				{
					$alert = "<div class='alert alert-danger' role='alert'>Invalid thumbnail file, must be .PNG</div>";
				}
				elseif (strlen($_POST['placename']) < 3)
				{
					$alert = "<div class='alert alert-danger' role='alert'>Place name too short, must be over 3 characters</div>";
				}
				elseif (strlen($_POST['placename']) > 50)
				{
					$alert = "<div class='alert alert-danger' role='alert'>Place name too long, must be under 50 characters</div>";
				}
				elseif(strlen($_POST['description']) > 1000)
				{
					$alert = "<div class='alert alert-danger' role='alert'>Place description too long, must be under 1k characters</div>";
				}
				elseif ($_POST['gdskill'][1] < 1) //cant have max players under 1
				{
					$alert = "<div class='alert alert-danger' role='alert'>An error occurred</div>";
				}
				elseif ($_POST['gdskill'][1] > 12) //cant have max players over 12
				{
					$alert = "<div class='alert alert-danger' role='alert'>An error occurred</div>";
				}
				elseif ($dimensionsfail)
				{
					$alert = "<div class='alert alert-danger' role='alert'>Thumbnail resolution cannot be over 1920x1080</div>";
				}
				else //all checks passed, do the do
				{
					//$
					//update place name
					$c = $pdo->prepare("UPDATE assets SET Name = :n WHERE id = :i");
					$c->bindParam(":n", cleanInput($_POST['placename']), PDO::PARAM_STR); //item name
					$c->bindParam(":i", $id, PDO::PARAM_INT); //catalog id
					$c->execute();
					// ...
					
					//update place description
					$c = $pdo->prepare("UPDATE assets SET Description = :n WHERE id = :i");
					$c->bindParam(":n", cleanInput($_POST['description']), PDO::PARAM_STR); //item description
					$c->bindParam(":i", $id, PDO::PARAM_INT); //catalog id
					$c->execute();
					// ...
					
					//update place max players
					$c = $pdo->prepare("UPDATE assets SET MaxPlayers = :n WHERE id = :i");
					$c->bindParam(":n", $_POST['gdskill'][1], PDO::PARAM_INT); //item price
					$c->bindParam(":i", $id, PDO::PARAM_INT); //catalog id
					$c->execute();
					// ...

					//update place chat style
					if (isset($_POST['chatstyle_classic_checkbox'])) {
						Game::SetChatStyle($id, 0);
					} else if (isset($_POST['chatstyle_bubble_checkbox'])) {
						Game::SetChatStyle($id, 1);
					} else if (isset($_POST['chatstyle_classicbubble_checkbox'])) {
						Game::SetChatStyle($id, 2);
					}
					
					if (isset($_POST['comments_checkbox']))
					{
						//update IsCommentsEnabled to enabled
						$comments = 1;
						$c = $pdo->prepare("UPDATE assets SET IsCommentsEnabled = :n, Updated = UNIX_TIMESTAMP() WHERE id = :i");
						$c->bindParam(":n", $comments, PDO::PARAM_INT); //item name
						$c->bindParam(":i", $id, PDO::PARAM_INT); //catalog id
						$c->execute();
						// ...
					}
					else
					{
						//update IsCommentsEnabled to disabled
						$comments = 0;
						$c = $pdo->prepare("UPDATE assets SET IsCommentsEnabled = :n, Updated = UNIX_TIMESTAMP() WHERE id = :i");
						$c->bindParam(":n", $comments, PDO::PARAM_INT); //item name
						$c->bindParam(":i", $id, PDO::PARAM_INT); //catalog id
						$c->execute();
						// ...
					}

					if (isset($_POST['thumbnail_checkbox']))
					{
						if (!isPlaceUsingRender($id))
						{
							$placepost = handleRenderPlace($id);
							if ($placepost !== true) {
								$alert = "<div class='alert alert-danger' role='alert'>".$placepost."</div>";
							}
						}
					}
					else
					{
						//grab place image hash
						
						//files in proper places
						if (!$usedefaultthumb) //if custom thumb uploaded
						{
							$GLOBALS['pdo']->exec("LOCK TABLES assets WRITE"); //lock since this stuff is sensitive
				
							$b = $GLOBALS['pdo']->prepare("SELECT * FROM assets");
							$b->execute();
																	
							//grab auto increment values
							$autoincrement = $b->rowCount() + 1; //initial auto increment value
								
							//add texture to assets
							$assetname = $gamename . " Thumbnail";
							$x = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`, `Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,1,:aname,'Place Thumbnail',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),:oid,:aid2,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,:hash)");
							$x->bindParam(":aid", $autoincrement, PDO::PARAM_INT);
							$x->bindParam(":aname", $assetname, PDO::PARAM_STR);
							$x->bindParam(":oid", $gamecreator, PDO::PARAM_INT);
							$x->bindParam(":aid2", $autoincrement, PDO::PARAM_INT);
							$x->bindParam(":hash", $thumbnailHash, PDO::PARAM_STR);
							$x->execute();

							//update place thumbhash
							$c = $pdo->prepare("UPDATE assets SET IconImageAssetId = :n WHERE id = :i");
							$c->bindParam(":n", $autoincrement, PDO::PARAM_INT); //item price
							$c->bindParam(":i", $id, PDO::PARAM_INT); //catalog id
							$c->execute();
								
							$GLOBALS['pdo']->exec("UNLOCK TABLES"); 

							resize(768, 432, $thumbnailuploadDirectory . $thumbnailHash, $_FILES['thumbnail_file']['tmp_name']);

							setPlaceUsingCustomThumbnail($id); //set not using rendered thumb
						}
						else
						{
							if (isPlaceUsingRender($id))
							{
								$thumb = rand(4, 6); 

								//update place icon
								$c = $pdo->prepare("UPDATE assets SET IconImageAssetId = :iiad WHERE id = :i");
								$c->bindParam(":iiad", $thumb, PDO::PARAM_INT); //item name
								$c->bindParam(":i", $id, PDO::PARAM_INT); //catalog id
								$c->execute();
								// ...

								setPlaceUsingCustomThumbnail($id); //set not using rendered thumb
							}
						}
						// ...
					}
					WebContextManager::Redirect("config?id={$id}");
				}
			}

			if (isset($_POST['SubmitPBSSuperflat']))
			{	
				$upload = convertToPBSPlace("Superflat", $id);
				if ($upload !== true)
				{
					$alert = "<div class='alert alert-danger' role='alert'>" . $upload . "</div>";
				}
				else
				{
					$alert = "<div class='alert alert-success' role='alert'>Created Personal Server</div>";
				}
			}

			if (isset($_POST['SubmitPBSRugged']))
			{	
				$upload = convertToPBSPlace("Rugged", $id);
				if ($upload !== true)
				{
					$alert = "<div class='alert alert-danger' role='alert'>" . $upload . "</div>";
				}
				else
				{
					$alert = "<div class='alert alert-success' role='alert'>Created Personal Server</div>";
				}
			}

			if (isset($_POST['SubmitPBSHappyHome']))
			{	
				$upload = convertToPBSPlace("HappyHome", $id);
				if ($upload !== true)
				{
					$alert = "<div class='alert alert-danger' role='alert'>" . $upload . "</div>";
				}
				else
				{
					$alert = "<div class='alert alert-success' role='alert'>Created Personal Server</div>";
				}
			}

			if (isset($_POST['SubmitPBSBaseplate']))
			{	
				$upload = convertToPBSPlace("Baseplate", $id);
				if ($upload !== true)
				{
					$alert = "<div class='alert alert-danger' role='alert'>" . $upload . "</div>";
				}
				else
				{
					$alert = "<div class='alert alert-success' role='alert'>Created Personal Server</div>";
				}
			}

			if (isset($_POST['PBSNoSelection']))
			{
				$alert = "<div class='alert alert-danger' role='alert'>Please choose a template</div>";
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
					$alert = "<div class='alert alert-success' role='alert'>Created place</div>";
				}
			}
		}
		else
		{
			WebContextManager::Redirect("/"); //not owner or not admin
		}
	}
	else
	{
		WebContextManager::Redirect("/"); //place doesnt exist
	}
}
else
{
	WebContextManager::Redirect("/"); //no url parameters
}

$gearshtml = "";
	if ($gearsportion)
	{
		$gearshtml = <<<EOT
		<div class="container text-center marg-bot-15">
			<h6>Allowed Gear Genres</h6>
			<div class="custom-control custom-checkbox custom-control-inline">
				<input type="checkbox" class="custom-control-input" id="defaultInline1">
				<label class="custom-control-label" for="defaultInline1">Melee Weapon</label>
			</div>
			<div class="custom-control custom-checkbox custom-control-inline">
				<input type="checkbox" class="custom-control-input" id="defaultInline2">
				<label class="custom-control-label" for="defaultInline2">Ranged Weapons</label>
			</div>
			<div class="custom-control custom-checkbox custom-control-inline">
				<input type="checkbox" class="custom-control-input" id="defaultInline3">
				<label class="custom-control-label" for="defaultInline3">Explosive</label>
			</div>
			<div class="custom-control custom-checkbox custom-control-inline">
				<input type="checkbox" class="custom-control-input" id="defaultInline4">
				<label class="custom-control-label" for="defaultInline4">Power Up</label>
			</div>
			<div class="custom-control custom-checkbox custom-control-inline">
				<input type="checkbox" class="custom-control-input" id="defaultInline5">
				<label class="custom-control-label" for="defaultInline5">Navigation Enhancers</label>
			</div>
			<div class="custom-control custom-checkbox custom-control-inline">
				<input type="checkbox" class="custom-control-input" id="defaultInline6">
				<label class="custom-control-label" for="defaultInline6">Musical Instruments</label>
			</div>
			<div class="custom-control custom-checkbox custom-control-inline">
				<input type="checkbox" class="custom-control-input" id="defaultInline7">
				<label class="custom-control-label" for="defaultInline7">Social Items</label>
			</div>
			<div class="custom-control custom-checkbox custom-control-inline">
				<input type="checkbox" class="custom-control-input" id="defaultInline8">
				<label class="custom-control-label" for="defaultInline8">Building Tools</label>
			</div>
			<div class="custom-control custom-checkbox custom-control-inline">
				<input type="checkbox" class="custom-control-input" id="defaultInline9">
				<label class="custom-control-label" for="defaultInline9">Personal Transport</label>
			</div>
		</div>
EOT;
	}
	
	$body = <<<EOT
	<div class="container">
	{$alert}
	<form action="" method="post" enctype="multipart/form-data">
		<div class="row">
			<div class="col-sm">
				<div class="card">
					<div class="card-body">
						<div class="row">
							<div class="col-sm">
								<div class="row mb-2">
									<div class="col-sm">
										<h5>Configure Place</h5>
									</div>
									<div class="col-sm-2">
										<btn class="btn btn-danger w-100 float-right" data-toggle="modal" data-target="#convertpbs">Convert to PBS</btn>
									</div>
								</div>
								<div class="text-center marg-bot-15">
									<label style="float:left;">Place Name</label>
									<input class="form-control" type="text" name="placename" value="{$gamename}">
								</div>
								<div class="text-center">
									<img class="img-fluid" style="width:40rem;height:20rem;" src="{$gamerender}">
								</div>
								<div class="text-center marg-bot-15">
									<label style="float:left;text-align:top;">Description</label>
									<textarea style="min-height:10rem;max-height:10rem;" class="form-control" type="text" name="description" placeholder="Description">{$gamedescription}</textarea>
								</div>
								<hr>
								<div class="container text-center">
									<hr>
									<h5>Chat Style</h5>
									<div class="custom-control custom-checkbox custom-control-inline">
										<input type="checkbox" name="chatstyle_classic_checkbox" {$chatclassic} class="custom-control-input sev_check" autocomplete="off" id="chatstyle_classic">
										<label class="custom-control-label" for="chatstyle_classic">Classic</label>
									</div>
									<div class="custom-control custom-checkbox custom-control-inline">
										<input type="checkbox" name="chatstyle_bubble_checkbox" {$chatbubble} class="custom-control-input sev_check" autocomplete="off" id="chatstyle_bubble">
										<label class="custom-control-label" for="chatstyle_bubble">Bubble</label>
									</div>
									<div class="custom-control custom-checkbox custom-control-inline">
										<input type="checkbox" name="chatstyle_classicbubble_checkbox" {$chatclassicbubble} class="custom-control-input sev_check" autocomplete="off" id="chatstyle_classicbubble">
										<label class="custom-control-label" for="chatstyle_classicbubble">Classic And Bubble</label>
									</div>
								</div>
								<hr>
								<div class="container text-center marg-bot-15">
									<h5>Max Players</h5>
									<input class="form-control-range custom-range" min="1" max="12" name="gdskill[1]" id="gdskill1" value="{$gamemaxplayers}" step="1" type="range" name="placemaxplayers" oninput="Output1.value = gdskill1.value">
									<output id="Output1" class="output" style="font-size:18px;">{$gamemaxplayers}</output>
									<datalist id="ticks">
										<option>1</option>
										<option>2</option>
										<option>3</option>
										<option>4</option>
										<option>5</option>
										<option>6</option>
										<option>7</option>
										<option>8</option>
										<option>9</option>
										<option>10</option>
										<option>11</option>
										<option>12</option>
									</datalist>
								</div>
								<hr>
								<div class="text-center mb-3">
									<h5>Miscellaneous</h5>
								</div>
								<div class="text-center mb-3">
									<div class="custom-control custom-checkbox custom-control-inline">
										<input type="checkbox" name="comments_checkbox" {$commentsstatus} class="custom-control-input" autocomplete="off" id="comments">
										<label class="custom-control-label" for="comments">Comments Enabled</label>
									</div>
								</div>
								<div class="text-center">
									<h6>If you'd like to use the last Studio position as the Thumbnail, check it below</h6>
									<h6>When you update this place through Studio with this ticked, the Thumbnail will update with the current position</h6>
								</div>
								<div class="container text-center">
									<div class="custom-control custom-checkbox custom-control-inline">
										<input type="checkbox" name="thumbnail_checkbox" {$thumbnailstatus} class="custom-control-input" onclick="checkTick()" autocomplete="off" id="thumbnail_tick">
										<label class="custom-control-label" for="thumbnail_tick">Use last Studio position</label>
									</div>
								</div>
								<hr>
								<div id="custom_thumb_container">
									<div class="text-center">
										<h6>Custom Game Thumbnails cannot be above 1920x1080</h6>
										<h6>If no custom Thumbnail is provided, a default will be used</h6>
									</div>
									<div class="input-group mb-3">
										<div class="custom-file">
											<input type="file" name="thumbnail_file" class="custom-file-input" id="inputGthumbFile">
											<label class="custom-file-label" for="inputGthumbFile">Custom Game Thumbnail</label>
										</div>
									</div>
								</div>
								<hr>
								{$gearshtml}
								<input type="Submit" name="Submit" value="Update Place" class="btn btn-danger w-100">
							</div>
						</div>		
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="convertpbs" tabindex="-1" role="dialog" aria-labelledby="convertpbsLabel" aria-hidden="true">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Convert to PBS</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<h5>WARNING:</h5>
					<p>Selecting a template then pressing <b>Confirm</b> will convert your game to a Personal Build Server (PBS), and all game data will be lost including Data Stores. Please take any backups before proceeding. All running Servers will also shutdown in the process.</p>
					<div class="converpbsopcontainer">
						<ul>
							<li>
								<div id="PBSIDSuperflat" class="converpbsopcard" onclick="setPBSType('Superflat')">
									<a class="text-center">
										<img class="img-fluid" src="/alphaland/cdn/imgs/Previews/Superflat.png">
										<span><p>Superflat</p></span>
									</a>
								</div>
							</li>
							<li>
								<div id="PBSIDRugged" class="converpbsopcard" onclick="setPBSType('Rugged')">
									<a class="text-center">
										<img class="img-fluid" src="/alphaland/cdn/imgs/Previews/Rugged.png">
										<span><p>Rugged</p></span>
									</a>
								</div>
							</li>
							<li>
								<div id="PBSIDHappyHome" class="converpbsopcard" onclick="setPBSType('HappyHome')">
									<a class="text-center">
										<img class="img-fluid" src="/alphaland/cdn/imgs/Previews/Happy%20Home.png">
										<span><p>Happy Home</p></span>
									</a>
								</div>
							</li>
							<li>
								<div id="PBSIDBaseplate" class="converpbsopcard" onclick="setPBSType('Baseplate')">
									<a class="text-center">
										<img class="img-fluid" src="/alphaland/cdn/imgs/Previews/Baseplate.png">
										<span><p>Baseplate</p></span>
									</a>
								</div>
							</li>
						</ul>
					</div>
				</div>
					<div class="modal-footer">
						<form action="" method="post">
							<button id="PBSSubmitButton" name="PBSNoSelection" class="btn btn-danger"><b>Confirm</b></button>
						</form>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<script>

var currentTypeId = "";
function setPBSType(type) {
	if (currentTypeId) {
		$(currentTypeId).removeClass("selected-my-group");
	}
	currentTypeId = "#PBSID"+type;
	$(currentTypeId).addClass("selected-my-group");
	$("#PBSSubmitButton").prop('name','SubmitPBS'+type);
}

function checkTick()
{
	if ($('#thumbnail_tick').is(':checked'))
	  $("#custom_thumb_container").hide();
	else
	  $("#custom_thumb_container").show();
}
checkTick()

$('.sev_check').click(function() {
  $('.sev_check').not(this).prop('checked', false);
});

</script>
EOT;

pageHandler();
$ph->body = $body;
$ph->pageTitle("Config");
$ph->output();