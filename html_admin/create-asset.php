<?php

forceHttpsCloudflare();

if(!($user->isAdmin())) {
	die('bababooey');
}

adminPanelStats();

$alert = '';

	if (isset($_POST['submitrobloxasset']))
	{
		$requestedassetid = $_POST['requestedrobloxassetid'];
		$prodinfo = getRobloxProductInfo($requestedassetid);

		$assetname = "";
		$assetdescription = "";
		$assetprice = (int)$_POST['itemprice'];
	
		if (isset($_POST['manualinput_checkbox_assetuploader'])) {
			$assetname = ($_POST['item']);
			$assetdescription = cleanInput($_POST['itemdesc']);
		} else {
			$assetname = cleanInput($prodinfo->Name);
			$assetdescription = cleanInput($prodinfo->Description);
		}
		
		$onsale = 0;
		if (isset($_POST['onsale_checkbox_assetuploader'])) {
			$onsale = 1;
		}
									
		$price = 0;
		if (!empty($assetprice)) {
			$price = $assetprice;
		}

		$worker = submitRobloxAssetWorker($requestedassetid, $prodinfo->AssetTypeId, $assetname, $assetdescription, $price, $onsale);

		if ($worker !== TRUE)
		{
			$alert = "<div class='alert alert-danger' role='alert'>".$worker."</div>";
		}
		else
		{
			$alert = "<div class='alert alert-success' role='alert'>Uploaded item</div>";
		}
	}
	elseif (isset($_POST['submithat']))
	{
		$b = $GLOBALS['pdo']->prepare("SELECT * FROM assets");
		$b->execute();
		if ($b->rowCount() > 0) //check if the assets auto increment query succeeds
		{
			if (file_exists($_FILES['xml_file']['tmp_name']) || file_exists($_FILES['mesh_file']['tmp_name']) || file_exists($_FILES['texture_file']['tmp_name'])|| is_uploaded_file($_FILES['xml_file']['tmp_name']) || is_uploaded_file($_FILES['mesh_file']['tmp_name']) || is_uploaded_file($_FILES['texture_file']['tmp_name'])) //check if all files exist
			{
				//setup the new asset in the DB, lock it!
				$GLOBALS['pdo']->exec("LOCK TABLES assets WRITE"); //lock since this stuff is sensitive
		
				//grab auto increment values
				$autoincrement = $b->rowCount() + 1; //initial auto increment value
				$autoincrement2 = $autoincrement+1; //initial auto increment value + 1
				$autoincrement3 = $autoincrement2+1; //initial auto increment value + 2
				// ...
				
				//generate new hashes for the assets
				$xmlhash = genAssetHash(16);
				$meshhash = genAssetHash(16);
				$texturehash = genAssetHash(16);
				// ...
					
				$onsale = 0;
				if (isset($_POST['onsale_checkbox']))
				{
					$onsale = 1;
				}
				else
				{
					$onsale = 0;
				}
					
				$price = 0;
				if (!empty(isset($_POST['itemprice'])))
				{
					$price = (int)$_POST['itemprice'];
				}
					
				$stock = 0;
				$maxstock = 0;

				//add XML (hat) to assets
				$m = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`, `Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,8,:aname,:adesc,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),1,:aid2,:price,0,0,:onsale,1,0,0,1,0,0,0,0,0,8,0,0,:hash)");
				$m->bindParam(":aid", $autoincrement, PDO::PARAM_INT);
				$m->bindParam(":aname", $_POST['item'], PDO::PARAM_STR);
				$m->bindParam(":adesc", $_POST['itemdesc'], PDO::PARAM_STR);
				$m->bindParam(":aid2", $autoincrement, PDO::PARAM_INT);
				$m->bindParam(":price", $price, PDO::PARAM_INT);
				$m->bindParam(":onsale", $onsale, PDO::PARAM_INT);
				$m->bindParam(":hash", $xmlhash, PDO::PARAM_STR);
				$m->execute();
				// ...
				
				//add mesh to assets
				$name = $_POST['item'] . " Mesh";
				$t = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`,`Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,4,:aname,'',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),1,:aid2,0,0,0,0,1,0,0,1,0,0,0,0,0,8,0,0,:hash)");
				$t->bindParam(":aid", $autoincrement2, PDO::PARAM_INT);
				$t->bindParam(":aname", $name, PDO::PARAM_STR);
				$t->bindParam(":aid2", $autoincrement2, PDO::PARAM_INT);
				$t->bindParam(":hash", $meshhash, PDO::PARAM_STR);
				$t->execute();
				// ...
				
				//add texture to assets
				$name = $_POST['item'] . " Texture";
				$x = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`, `Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,1,:aname,'',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),1,:aid2,0,0,0,0,1,0,0,1,0,0,0,0,0,8,0,0,:hash)");
				$x->bindParam(":aid", $autoincrement3, PDO::PARAM_INT);
				$x->bindParam(":aname", $name, PDO::PARAM_STR);
				$x->bindParam(":aid2", $autoincrement3, PDO::PARAM_INT);
				$x->bindParam(":hash", $texturehash, PDO::PARAM_STR);
				$x->execute();
				// ...
				
				$GLOBALS['pdo']->exec("UNLOCK TABLES"); //unlock since we are done with sensitive asset stuff
					
				//give the hat to the user Alphaland
				$c = $GLOBALS['pdo']->prepare("INSERT into owned_assets (uid, aid, stock, when_sold, givenby) VALUES(1, :a, 0, UNIX_TIMESTAMP(), 1)"); //give asset 8
				$c->bindParam(":a", $autoincrement, PDO::PARAM_INT); //catalog asset id
				$c->execute();
				// ...

					
				//upload parameters
				$uploadDirectory = $GLOBALS['assetCDNPath']; //directory where the xml and mesh assets are stored
				$textureUploadDirectory = $GLOBALS['thumbnailCDNPath']; //directory where the textures are stored
				// ...
					
				//temp file locations
				$xmlfileTmpName  = $_FILES['xml_file']['tmp_name']; //location of the uploaded xml file (temp directory)
				$meshfileTmpName  = $_FILES['mesh_file']['tmp_name']; //location of the uploaded mesh file (temp directory)
				$texturefileTmpName  = $_FILES['texture_file']['tmp_name']; //location of the uploaded texture file (temp directory)
				// ...
					
				//upload and edit files
				
				$str = file_get_contents($xmlfileTmpName);
				$str=str_replace("MESHURLPLACEHOLDER", $url . "/asset/?id=" . $autoincrement2, $str);
				$str=str_replace("TEXTUREURLPLACEHOLDER", $url . "/asset/?id=" . $autoincrement3, $str);
				file_put_contents($xmlfileTmpName, $str);
				
				move_uploaded_file($xmlfileTmpName, $uploadDirectory . $xmlhash);
				move_uploaded_file($meshfileTmpName, $uploadDirectory . $meshhash);
				move_uploaded_file($texturefileTmpName, $textureUploadDirectory . $texturehash);
				// ...
					
				//render

				if (!RenderHat($autoincrement))
				{
					$alert = "<div class='alert alert-danger' role='alert'>Error Rendering Hat, it's been uploaded but not Rendered</div>";
				}
				else
					{
				$alert = "<div class='alert alert-success' role='alert'>Uploaded item</div>";

				if ($onsale) {
					httpGetPing("localhost:4098/?type=itemrelease&assetid=".$autoincrement."&name=".urlencode($_POST['item'])."&description=".urlencode($_POST['itemdesc'])."&price=".$price."&image=".$GLOBALS['renderCDN']."/".getAssetInfo($autoincrement)->ThumbHash, 8000);
				}
				}
				// ...
			}
			else
			{
				$alert = "<div class='alert alert-danger' role='alert'>Missing files, please check Instructions</div>";
			}
		}
	}
	elseif (isset($_POST['submitface']))
	{
		$b = $GLOBALS['pdo']->prepare("SELECT * FROM assets");
		$b->execute();
		if ($b->rowCount() > 0) //check if the assets auto increment query succeeds
		{
			if (file_exists($_FILES['texture_file']['tmp_name']) || is_uploaded_file($_FILES['texture_file']['tmp_name'])) //check if all files exist
			{
				//setup the new asset in the DB, lock it!
				$GLOBALS['pdo']->exec("LOCK TABLES assets WRITE"); //lock since this stuff is sensitive
		
				//grab auto increment values
				$autoincrement = $b->rowCount() + 1; //initial auto increment value
				$autoincrement2 = $autoincrement+1; //initial auto increment value + 1
				// ...
				
				//generate new hashes for the assets
				$xmlhash = genAssetHash(16);
				$texturehash = genAssetHash(16);
				// ...
					
				$onsale = 0;
				if (isset($_POST['onsale_face_checkbox']))
				{
					$onsale = 1;
				}
				else
				{
					$onsale = 0;
				}
					
				$price = 0;
				if (!empty(isset($_POST['itemprice'])))
				{
					$price = (int)$_POST['itemprice'];
				}
					
				$stock = 0;
				$maxstock = 0;
					
				//add XML (face) to assets
				$m = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`, `Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,18,:aname,:adesc,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),1,:aid2,:price,0,0,:onsale,1,0,0,1,0,0,0,0,0,8,0,0,:hash)");
				$m->bindParam(":aid", $autoincrement, PDO::PARAM_INT);
				$m->bindParam(":aname", $_POST['item'], PDO::PARAM_STR);
				$m->bindParam(":adesc", $_POST['itemdesc'], PDO::PARAM_STR);
				$m->bindParam(":aid2", $autoincrement, PDO::PARAM_INT);
				$m->bindParam(":price", $price, PDO::PARAM_INT);
				$m->bindParam(":onsale", $onsale, PDO::PARAM_INT);
				$m->bindParam(":hash", $xmlhash, PDO::PARAM_STR);
				$m->execute();
				// ...
					
				//add texture to assets
				$name = $_POST['item'] . " Texture";
				$x = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`, `Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,1,:aname,'',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),1,:aid2,0,0,0,0,1,0,0,1,0,0,0,0,0,8,0,0,:hash)");
				$x->bindParam(":aid", $autoincrement2, PDO::PARAM_INT);
				$x->bindParam(":aname", $name, PDO::PARAM_STR);
				$x->bindParam(":aid2", $autoincrement2, PDO::PARAM_INT);
				$x->bindParam(":hash", $texturehash, PDO::PARAM_STR);
				$x->execute();
				// ...
				
				$GLOBALS['pdo']->exec("UNLOCK TABLES"); //unlock since we are done with sensitive asset stuff
				// ...
					
				//give the face to the user Alphaland
				$c = $GLOBALS['pdo']->prepare("INSERT into owned_assets (uid, aid, stock, when_sold, givenby) VALUES(1, :a, 0, UNIX_TIMESTAMP(), 1)"); //give asset 8
				$c->bindParam(":a", $autoincrement, PDO::PARAM_INT); //catalog asset id
				$c->execute();
				// ...
					
				//upload parameters
				$uploadDirectory = $GLOBALS['assetCDNPath']; //directory where the assets are stored
				$textureUploadDirectory = $GLOBALS['thumbnailCDNPath']; //directory where the textures are stored
				$faceTemplate = "../default_xmls/faces"; //path to default face
				// ...
					
				//temp file locations
				$xmlfileTmpName  = $uploadDirectory . "xml_templates/faces"; //location of the uploaded xml file (temp directory)
				$texturefileTmpName  = $_FILES['texture_file']['tmp_name']; //location of the uploaded texture file (temp directory)
				// ...
					
				//copy and stored edited xml
				$strr = file_get_contents($faceTemplate);
				$strr=str_replace("TEXTUREURLPLACEHOLDER", $url . "/asset/?id=" . $autoincrement2, $strr);
				// ...
					
				//move files
				file_put_contents($uploadDirectory . $xmlhash, $strr); //copy xml hash to assets cdn
				move_uploaded_file($texturefileTmpName, $textureUploadDirectory . $texturehash); //copy the uploaded texture to thumb cdn
				// ...
					
				//render
				
				if (!RenderFace($autoincrement))
				{
					$alert = "<div class='alert alert-danger' role='alert'>Error Rendering face, it's been uploaded but not Rendered</div>";
				}
				else
				{
					$alert = "<div class='alert alert-success' role='alert'>Uploaded face</div>";

					if ($onsale) {
						httpGetPing("localhost:4098/?type=itemrelease&assetid=".$autoincrement."&name=".urlencode($_POST['item'])."&description=".urlencode($_POST['itemdesc'])."&price=".$price."&image=".$GLOBALS['renderCDN']."/".getAssetInfo($autoincrement)->ThumbHash, 8000);
					}
				}
				// ...
			}
			else
			{
				$alert = "<div class='alert alert-danger' role='alert'>Missing files, please check Instructions</div>";
			}
		}
	}
	elseif(isset($_POST['submithead']))
	{
		$b = $GLOBALS['pdo']->prepare("SELECT * FROM assets");
		$b->execute();
		if ($b->rowCount() > 0) //check if the assets auto increment query succeeds
		{
			$xmlexists = false;
			$meshexists = false;
			$textureexists = false;
			if (file_exists($_FILES['xml_file']['tmp_name']) || is_uploaded_file($_FILES['xml_file']['tmp_name']))
			{
				$xmlexists = true;
			}
			if (file_exists($_FILES['mesh_file']['tmp_name']) || is_uploaded_file($_FILES['mesh_file']['tmp_name']))
			{
				$meshexists = true;
			}
			if (file_exists($_FILES['texture_file']['tmp_name']) && is_uploaded_file($_FILES['texture_file']['tmp_name']))
			{
				$textureexists = true;
			}

			if ($xmlexists && !(!$meshexists && $textureexists)) //XML required
			{
				//setup the new asset in the DB, lock it!
				$GLOBALS['pdo']->exec("LOCK TABLES assets WRITE"); //lock since this stuff is sensitive
		
				//grab auto increment values
				$autoincrement = $b->rowCount() + 1; //initial auto increment value (XML)
				$autoincrement2 = $autoincrement+1; //MESH
				$autoincrement3 = $autoincrement2+1; //TEXTURE
				// ...
				
				//generate new hashes for the assets
				$xmlhash = genAssetHash(16);
				$meshhash = genAssetHash(16);
				$texturehash = genAssetHash(16);
				// ...
					
				$onsale = 0;
				if (isset($_POST['onsale_checkbox']))
				{
					$onsale = 1;
				}
				else
				{
					$onsale = 0;
				}
					
				$price = 0;
				if (!empty(isset($_POST['itemprice'])))
				{
					$price = (int)$_POST['itemprice'];
				}
					
				$stock = 0;
				$maxstock = 0;
					
				//add XML (head) to assets
				$m = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`, `Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,17,:aname,:adesc,UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),1,:aid2,:price,0,0,:onsale,1,0,0,1,0,0,0,0,0,8,0,0,:hash)");
				$m->bindParam(":aid", $autoincrement, PDO::PARAM_INT);
				$m->bindParam(":aname", $_POST['item'], PDO::PARAM_STR);
				$m->bindParam(":adesc", $_POST['itemdesc'], PDO::PARAM_STR);
				$m->bindParam(":aid2", $autoincrement, PDO::PARAM_INT);
				$m->bindParam(":price", $price, PDO::PARAM_INT);
				$m->bindParam(":onsale", $onsale, PDO::PARAM_INT);
				$m->bindParam(":hash", $xmlhash, PDO::PARAM_STR);
				$m->execute();
				// ...
				
				if ($meshexists)
				{
					//add mesh to assets
					$name = $_POST['item'] . " Mesh";
					$t = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`,`Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,4,:aname,'',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),1,:aid2,0,0,0,0,1,0,0,1,0,0,0,0,0,8,0,0,:hash)");
					$t->bindParam(":aid", $autoincrement2, PDO::PARAM_INT);
					$t->bindParam(":aname", $name, PDO::PARAM_STR);
					$t->bindParam(":aid2", $autoincrement2, PDO::PARAM_INT);
					$t->bindParam(":hash", $meshhash, PDO::PARAM_STR);
					$t->execute();
					// ...	
				}
				if ($textureexists)
				{
					//add texture to assets
					$name = $_POST['item'] . " Texture";
					$x = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`, `Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,1,:aname,'',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),1,:aid2,0,0,0,0,1,0,0,1,0,0,0,0,0,8,0,0,:hash)");
					$x->bindParam(":aid", $autoincrement3, PDO::PARAM_INT);
					$x->bindParam(":aname", $name, PDO::PARAM_STR);
					$x->bindParam(":aid2", $autoincrement3, PDO::PARAM_INT);
					$x->bindParam(":hash", $texturehash, PDO::PARAM_STR);
					$x->execute();
					// ...
				}
				
				$GLOBALS['pdo']->exec("UNLOCK TABLES"); //unlock since we are done with sensitive asset stuff
				// ...
					
				//give the Head to the user Alphaland
				$c = $GLOBALS['pdo']->prepare("INSERT into owned_assets (uid, aid, stock, when_sold, givenby) VALUES(1, :a, 0, UNIX_TIMESTAMP(), 1)"); //give asset
				$c->bindParam(":a", $autoincrement, PDO::PARAM_INT); //catalog asset id
				$c->execute();
				// ...
					
				//upload parameters
				$uploadDirectory = $GLOBALS['assetCDNPath']; //directory where the xml and mesh assets are stored
				$textureUploadDirectory = $GLOBALS['thumbnailCDNPath']; //directory where the textures are stored
				// ...
					
				//temp file locations
				$xmlfileTmpName  = $_FILES['xml_file']['tmp_name']; //location of the uploaded xml file (temp directory)
				$meshfileTmpName  = $_FILES['mesh_file']['tmp_name']; //location of the uploaded mesh file (temp directory)
				$texturefileTmpName  = $_FILES['texture_file']['tmp_name']; //location of the uploaded texture file (temp directory)
				// ...
					
				//upload and edit files
				if ($meshexists || $textureexists)
				{
					$str = file_get_contents($xmlfileTmpName);
					if ($meshexists)
					{
						$str=str_replace("MESHURLPLACEHOLDER", $url . "/asset/?id=" . $autoincrement2, $str);
					}
					if ($textureexists)
					{
						$str=str_replace("TEXTUREURLPLACEHOLDER", $url . "/asset/?id=" . $autoincrement3 ,$str);
					}
					file_put_contents($xmlfileTmpName, $str);
				}
				
				move_uploaded_file($xmlfileTmpName, $uploadDirectory . $xmlhash);
				if ($meshexists)
				{
					move_uploaded_file($meshfileTmpName, $uploadDirectory . $meshhash);
				}
				if ($textureexists)
				{
					move_uploaded_file($texturefileTmpName, $textureUploadDirectory . $texturehash);
				}
				// ...

				if (!RenderHead($autoincrement))
				{
					$alert = "<div class='alert alert-danger' role='alert'>Error Rendering Head, it's been uploaded but not Rendered</div>";
				}
				else
				{
					$alert = "<div class='alert alert-success' role='alert'>Uploaded item</div>";
				}
				// ...
			}
			else
			{
				$alert = "<div class='alert alert-danger' role='alert'>Missing files, please check Instructions</div>";
			}
		}
	}
	
	$body = <<<EOT
	<div class="container">
	{$alert}
	<form action="" method="post" enctype="multipart/form-data">
		<h5>Create Asset</h5>
		<div class="row">
			<div class="col-sm-3 mb-4">
				<div class="card">
					<div class="card-body text-center">
						<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
							<a class="nav-link active red-a-nounder" id="v-pills-asset-by-id-tab" data-toggle="pill" href="#v-pills-asset-by-id" role="tab" aria-controls="v-pills-asset-by-id" aria-selected="true">Auto Uploader</a>
							<a class="nav-link red-a-nounder" id="v-pills-hats-tab" data-toggle="pill" href="#v-pills-hats" role="tab" aria-controls="v-pills-hats" aria-selected="false">Hats</a>
							<a class="nav-link red-a-nounder" id="v-pills-faces-tab" data-toggle="pill" href="#v-pills-faces" role="tab" aria-controls="v-pills-faces" aria-selected="false">Faces</a>
							<a class="nav-link red-a-nounder" id="v-pills-heads-tab" data-toggle="pill" href="#v-pills-heads" role="tab" aria-controls="v-pills-heads" aria-selected="false">Heads</a>
						</div>
					</div>
				</div>
			</div>
			<div class="col-sm">
				<div class="card">
					<div class="card-body">
						<div class="tab-content" id="v-pills-tabContent">
							<div class="tab-content" id="v-pills-tabContent">
								<div class="tab-pane fade show active" id="v-pills-asset-by-id" role="tabpanel" aria-labelledby="v-pills-asset-by-id-tab">
									<div class="card-body">
										<div class="text-center">
										<p><b>ROBLOX Automatic Asset Uploader</b></p>
										<p>Supports Hats, Faces and Gears in XML format with mesh version 2.00 and below</p>
										</div>
											<div class="row">
												<div class="col-sm">
													<div class="form-group">
														<form action="" method="post" enctype="multipart/form-data">
														 <input style="width:100%!important;" type="text" name="requestedrobloxassetid" placeholder="ROBLOX Asset ID" class="form-control mb-3">
														 <div id="robloxassetuploader_name_desc" style="display:none;"> 
														 	<input style="width:100%!important;" type="text" name="item" placeholder="Asset Name" class="form-control mb-3">
														 	<textarea type="text" name="itemdesc" style="width:100%!important;min-height:150px;max-height:150px;" class="form-control mb-3" placeholder="Item Description"></textarea>
														 </div>
														 <div class="input-group mb-3">
																<div class="input-group-prepend">
																	<span class="input-group-text"><img style="width:1rem;" src="https://www.alphaland.cc/alphaland/cdn/imgs/alphabux-grey-1024.png"></span>
																</div>
																<input type="text" name="itemprice" class="form-control" placeholder="Price">
															</div>
															<div class="container text-center mb-3">
																<div class="custom-control custom-checkbox custom-control-inline">
																	<input type="checkbox" name="onsale_checkbox_assetuploader" class="custom-control-input" id="onsale_assetuploader">
																	<label class="custom-control-label" for="onsale_assetuploader">On-Sale</label>
																</div>
																<div class="custom-control custom-checkbox custom-control-inline">
																	<input type="checkbox" name="manualinput_checkbox_assetuploader" class="custom-control-input" id="manualinput_assetuploader">
																	<label class="custom-control-label" for="manualinput_assetuploader">Advanced</label>
																</div>
															</div>
															<input type="submit" name="submitrobloxasset" value="Upload" class="btn btn-danger">
														</form>
													</div>
												</div>
											</div>
									</div>
								</div>

								<div class="tab-pane fade" id="v-pills-hats" role="tabpanel" aria-labelledby="v-pills-hats-tab">
									<div class="card-body">
										<div class="text-center">
											<p>
												<button class="btn btn-danger w-100" type="button" data-toggle="collapse" data-target="#instructions" aria-expanded="false" aria-controls="instructions">Instructions</button>
											</p>
											<div class="collapse" id="instructions">
												<p><b>1. Download the hat's XML file with the URL: "https://assetdelivery.roblox.com/v1/asset/?id=ASSETID"</b></p>
												<p><b>2. Open the file with notepad or file editor of choice, and download the asset data, found easily with "MeshId" and "TextureId"</b></p>
												<p><b>3. Edit the URL's in the opened XML file, replace the MeshID URL, found with "MeshId" with "MESHURLPLACEHOLDER" (NO SPACES BEFORE OR AFTER)</b></p>
												<p><b>4. Edit the URL's in the opened XML file, replace the TextureID URL, found with "TextureId" with "TEXTUREURLPLACEHOLDER" (NO SPACES BEFORE OR AFTER)</b></p>
												<p><b>5. Select the Mesh, Texture and the edited XML file under the correct file selectors</b></p>
												<p><b>6. Fill out the rest of the required data, and create</b></p>
											</div>
										</div>
											<div class="row">
												<div class="col-sm">
													<div class="form-group">
														<form action="" method="post" enctype="multipart/form-data">
															<div class="row text-left">
																<div class="col-sm">
																	<div class="input-group mb-3">
																		<div class="custom-file">
																			<input type="file" name="xml_file" id="inputxmlfile" class="custom-file-input">
																			<label class="custom-file-label" for="inputxmlfile">XML File</label>
																		</div>
																	</div>
																</div>
																<div class="col-sm">
																	<div class="input-group mb-3">
																		<div class="custom-file">
																			<input type="file" name="mesh_file" class="custom-file-input" id="inputMeshFile">
																			<label class="custom-file-label" for="inputMeshFile">Mesh File</label>
																		</div>
																	</div>
																</div>
																<div class="col-sm">
																	<div class="input-group mb-3">
																		<div class="custom-file">
																			<input type="file" name="texture_file" class="custom-file-input" id="inputTextureFile">
																			<label class="custom-file-label" for="inputTextureFile">Texture File</label>
																		</div>
																	</div>
																</div>
															</div>
															<input style="width:100%!important;" type="text" name="item" placeholder="Asset Name" class="form-control mb-3">
															<textarea type="text" name="itemdesc" style="width:100%!important;min-height:150px;max-height:150px;" class="form-control mb-3" placeholder="Item Description"></textarea>
															<div class="input-group mb-3">
																<div class="input-group-prepend">
																	<span class="input-group-text"><img style="width:1rem;" src="https://www.alphaland.cc/alphaland/cdn/imgs/alphabux-grey-1024.png"></span>
																</div>
																<input type="text" name="itemprice" class="form-control" placeholder="Price">
															</div>
															<div class="container text-center mb-3">
																<div class="custom-control custom-checkbox custom-control-inline">
																	<input type="checkbox" name="onsale_checkbox" class="custom-control-input" id="onsale">
																	<label class="custom-control-label" for="onsale">On-Sale</label>
																</div>
															</div>
															<input type="submit" name="submithat" value="Create" class="btn btn-danger">
														</form>
													</div>
												</div>
											</div>
									</div>
								</div>
								<div class="tab-pane fade" id="v-pills-faces" role="tabpanel" aria-labelledby="v-pills-faces-tab">
									<div class="card-body">
											<div class="text-center">
												<p>
													<button class="btn btn-danger w-100" type="button" data-toggle="collapse" data-target="#instructions" aria-expanded="false" aria-controls="instructions">Instructions</button>
												</p>
												<div class="collapse" id="instructions">
													<p><b>1. Download the faces's XML file with the URL: "https://assetdelivery.roblox.com/v1/asset/?id=ASSETID"</b></p>
													<p><b>2. Open the file with notepad or file editor of choice, and download the Texture, found easily with "Texture"</b></p>
													<p><b>3. Select the Texture under the correct file selector</b></p>
													<p><b>4. Fill out the rest of the required data, and create</b></p>
												</div>
											</div>
												<div class="row">
													<div class="col-sm">
														<div class="form-group">
															<form action="" method="post" enctype="multipart/form-data">
																<div class="row text-left">
																	<div class="col-sm">
																		<div class="input-group mb-3">
																			<div class="custom-file">
																				<input type="file" name="texture_file" class="custom-file-input" id="inputTextureFile">
																				<label class="custom-file-label" for="inputTextureFile">Texture File</label>
																			</div>
																		</div>
																	</div>
																</div>
																<input style="width:100%!important;" type="text" name="item" placeholder="Asset Name" class="form-control mb-3">
																<textarea type="text" name="itemdesc" style="width:100%!important;min-height:150px;max-height:150px;" class="form-control mb-3" placeholder="Item Description"></textarea>
																<div class="input-group mb-3">
																	<div class="input-group-prepend">
																		<span class="input-group-text"><img style="width:1rem;" src="https://www.alphaland.cc/alphaland/cdn/imgs/alphabux-grey-1024.png"></span>
																	</div>
																	<input type="text" name="itemprice" class="form-control" placeholder="Price">
																</div>
																<div class="container text-center mb-3">
																	<div class="custom-control custom-checkbox custom-control-inline">
																		<input type="checkbox" name="onsale_face_checkbox" class="custom-control-input" id="onsale_face">
																		<label class="custom-control-label" for="onsale_face">On-Sale</label>
																	</div>
																</div>
																<input type="submit" name="submitface" value="Create" class="btn btn-danger">
															</form>
														</div>
													</div>
												</div>
										</div>
									</div>
									<div class="tab-pane fade" id="v-pills-heads" role="tabpanel" aria-labelledby="v-pills-heads-tab">
										<div class="card-body">
										<div class="text-center">
											<p>
												<button class="btn btn-danger w-100" type="button" data-toggle="collapse" data-target="#instructions" aria-expanded="false" aria-controls="instructions">Instructions</button>
											</p>
											<div class="collapse" id="instructions">
												<p><b>1. Download the Head's XML file with the URL: "https://assetdelivery.roblox.com/v1/asset/?id=ASSETID" (if the item isn't in XML Format, open it in studio and Export as XML)</b></p>
												<p><b>2. Open the file with notepad or file editor of choice, and download the asset data, found easily with "MeshId" and "TextureId"</b></p>
												<p><b>3. Edit the URL's in the opened XML file, replace the MeshID URL, found with "MeshId" with "MESHURLPLACEHOLDER" (NO SPACES BEFORE OR AFTER)</b></p>
												<p><b>4. Edit the URL's in the opened XML file, replace the TextureID URL, found with "TextureId" with "TEXTUREURLPLACEHOLDER" (NO SPACES BEFORE OR AFTER)</b></p>
												<p><b>5. Select the Mesh, Texture and the edited XML file under the correct file selectors</b></p>
												<p><b>6. Fill out the rest of the required data, and create</b></p>
											</div>
										</div>
											<div class="row">
												<div class="col-sm">
													<div class="form-group">
														<form action="" method="post" enctype="multipart/form-data">
															<div class="row text-left">
																<div class="col-sm">
																	<div class="input-group mb-3">
																		<div class="custom-file">
																			<input type="file" name="xml_file" id="inputxmlfile" class="custom-file-input">
																			<label class="custom-file-label" for="inputxmlfile">XML File</label>
																		</div>
																	</div>
																</div>
																<div class="col-sm">
																	<div class="input-group mb-3">
																		<div class="custom-file">
																			<input type="file" name="mesh_file" class="custom-file-input" id="inputMeshFile">
																			<label class="custom-file-label" for="inputMeshFile">Mesh File</label>
																		</div>
																	</div>
																</div>
																<div class="col-sm">
																	<div class="input-group mb-3">
																		<div class="custom-file">
																			<input type="file" name="texture_file" class="custom-file-input" id="inputTextureFile">
																			<label class="custom-file-label" for="inputTextureFile">Texture File</label>
																		</div>
																	</div>
																</div>
															</div>
															<input style="width:100%!important;" type="text" name="item" placeholder="Asset Name" class="form-control mb-3">
															<textarea type="text" name="itemdesc" style="width:100%!important;min-height:150px;max-height:150px;" class="form-control mb-3" placeholder="Item Description"></textarea>
															<div class="input-group mb-3">
																<div class="input-group-prepend">
																	<span class="input-group-text"><img style="width:1rem;" src="https://www.alphaland.cc/alphaland/cdn/imgs/alphabux-grey-1024.png"></span>
																</div>
																<input type="text" name="itemprice" class="form-control" placeholder="Price">
															</div>
															<div class="container text-center mb-3">
																<div class="custom-control custom-checkbox custom-control-inline">
																	<input type="checkbox" name="onsale_checkbox" class="custom-control-input" id="onsale">
																	<label class="custom-control-label" for="onsale">On-Sale</label>
																</div>
															</div>
															<input type="submit" name="submithead" value="Create" class="btn btn-danger">
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
			</div>
		</div>
	</form>
</div>
<script>
$("#manualinput_assetuploader").change(function() {
    if(this.checked) {
		$("#robloxassetuploader_name_desc").show();
    } else {
		$("#robloxassetuploader_name_desc").hide();
	}
});
</script>
EOT;

pageHandler();
$ph->pagetitle = ""; 
$ph->navbar = "";
$ph->body = $body;
$ph->footer = "";
$ph->output();