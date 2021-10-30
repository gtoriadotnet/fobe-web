<?php


$owned = $pdo->prepare("SELECT * FROM assets WHERE AssetTypeId = 10 AND CreatorId = :cid ORDER BY created DESC");
$owned->bindParam(":cid", $user->id, PDO::PARAM_INT);
$owned->execute();
$owned = $owned->fetchAll(PDO::FETCH_ASSOC);

$assets = "";
foreach($owned as $asset) 
{
	$assets .= '
	<li>
		<div class="studio-upload-card text-center" style="cursor: pointer;width: 152px;height: 185px" onclick="updateAsset('.$asset['id'].')">
			<a>
				<div class="studio-upload-card-img">
					<img class="img-fluid" style="width: 150px;height: 150px" src="'.getAssetRender($asset['id']).'">
				</div>
				<p class="no-overflow">'.getAssetInfo($asset['id'])->Name.'</p>
			</a>
		</div>
	</li>
	';
}

$body = <<<EOT
<div class="container mt-2"  id="update_model_dialog">
	<h5>Upload Model to Alphaland:</h5>
	<hr>
	<div class="catalog-container">
		<ul>
			<li>
				<div class="studio-upload-card text-center" style="cursor: pointer;" onclick="showNewModelDialog()">
					<a>
						<div class="studio-upload-card-img">
							<img class="img-fluid" src="/alphaland/cdn/imgs/addmodelicon.png">
						</div>
						<p class="no-overflow">Create New</p>
					</a>
				</div>
			</li>
			{$assets}
		</ul>
	</div>
</div>

<div id="new_model_dialog" style="width:60%;margin:auto;display:none;">
	<div id = "error_alert" class="alert alert-danger" role="alert" style="display:none;"></div>
    <h5 class="mt-2">Create Model:</h5>
    <hr>
    <label>Model Name</label>
    <input class="form-control mb-3" id="model_name" placeholder="Model Name"></input>
    <label>Model Description</label>
    <textarea class="form-control mb-2" id="model_description" placeholder="Model Description" style="height:7rem;max-height:7rem;"></textarea>
        <div class="form-check">
            <input type="checkbox" class="form-check-input" id="copylocked">
            <label class="form-check-label" for="copylocked">Copylocked</label>
        </div>
    <button class="btn btn-danger float-right" onclick="newAsset()">Upload</button>
</div>

<script>

function checkParameters(name, description)
{
	var message = "";
	if (name.length > 50)
	{
		message = "Name cannot be more than 50 characters";
	}
	else if (name.length < 3)
	{
		message = "Name must be more than 3 characters";
	}
	else if (description.length > 1024)
	{
		message = "Description cannot be more than 1024 characters"
	}

	if (message)
	{
		$("#error_alert").text(message);
		$("#error_alert").show();
		setTimeout(function() 
		{
			$("#error_alert").hide();
		}, 3000);
		return false; //issue
	}
	return true; //no issues
}

function showNewModelDialog()
{
	$("#update_model_dialog").hide();
	$("#new_model_dialog").show();
}

function updateAsset(assetid)
{
	window.external.WriteSelection().Upload('https://www.alphaland.cc/Studio/Data/UploadData?id=' + assetid + '\'');
	alert("Updated Model");
	window.close();
}

function newAsset()
{
	var modelname = $("#model_name").val();
	var modeldescription = $("#model_description").val();
	var ispublic = "true";
	if ($('#copylocked').is(":checked"))
	{
		ispublic = "false";
	}

	if (checkParameters(modelname, modeldescription))
	{
		window.external.WriteSelection().Upload("https://www.alphaland.cc/Studio/IDE/Publish/uploadnewasset?assetTypeName=Model&name="+modelname+"&description="+modeldescription+"&isPublic="+ispublic+"&allowComments=false");
		alert("Created Model");
		window.close();
	}
}

</script>
EOT;

pageHandler();
$ph->navbar = "";
$ph->footer = "";
$ph->studio = true; //force default theme (light)
$ph->body = $body;
$ph->pageTitle("Upload");
$ph->output();