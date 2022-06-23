<?php
//avatar page

/*
Alphalnd 2021
*/

$bc = $pdo->prepare("SELECT * FROM body_colours WHERE uid = :u");
$bc->bindParam(":u", $user->id, PDO::PARAM_INT);
$bc->execute();
if($bc->rowCount() > 0) 
{
	$bc = $bc->fetch(PDO::FETCH_OBJ);
} 
else 
{
	die('Something went wrong, please contact an Administrator on the Discord Server');
}

$body = '
<div class="container">
	<div id = "error_alert" class="alert alert-danger" role="alert" style="display:none";>Please wait for the current Render</div>
	<div class="row">
		<div class="col-sm-4">
			<div class="card mb-2">
				<div class="card-body text-center">
					<img id="character" class="card-img-top" src="https://api.idk16.xyz/users/thumbnail">
					<button onclick="updateCharacter()" class="btn btn-danger" style="float:right;">Refresh</button>
					<button onclick="getAvatarSettings()" type="button" data-toggle="modal" data-target="#avatarsettingspopup" class="btn btn-danger" style="float:left;">Settings</button>
				</div>
			</div>
			<div class="card mb-2">
				<div class="card-body text-center">
					<h5>Body Colors</h5>
					<hr>
					<div class="bodyc-container" id="bodycolor-dummy">
						<div onclick="pickbc(0)" id="bc0" class="bodyc-part bodyc-head" style="background-color: '.getBC($bc->h).'"></div> 
						<div onclick="pickbc(2)" id="bc2" class="bodyc-part bodyc-rarm" style="background-color: '.getBC($bc->la).'"></div> 
						<div onclick="pickbc(1)" id="bc1" class="bodyc-part bodyc-torso" style="background-color: '.getBC($bc->t).'"></div> 
						<div onclick="pickbc(3)" id="bc3" class="bodyc-part bodyc-larm" style="background-color: '.getBC($bc->ra).'"></div> 
						<div onclick="pickbc(4)" id="bc4" class="bodyc-part bodyc-rleg" style="background-color: '.getBC($bc->ll).'"></div> 
						<div onclick="pickbc(5)" id="bc5" class="bodyc-part bodyc-lleg" style="background-color: '.getBC($bc->rl).'"></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm">
			<div class="card mb-2">
				<div class="card-body">
					<div class="row">
						<div class="col-sm">
							<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
								<li class="nav-item">
									<a style="cursor:pointer;" class="red-a-nounder nav-link active" id="itemtabt8" onclick="inventoryPage(1,8)" data-toggle="pill" role="tab" aria-controls="pills-hats">Hats</a>
								</li>
								<li class="nav-item">
									<a style="cursor:pointer;" class="red-a-nounder nav-link" id="itemtabt2" onclick="inventoryPage(1,2)" data-toggle="pill" role="tab" aria-controls="pills-hats">T-Shirts</a>
								</li>
								<li class="nav-item">
									<a style="cursor:pointer;" class="red-a-nounder nav-link" id="itemtabt11" onclick="inventoryPage(1,11)" data-toggle="pill" role="tab" aria-controls="pills-hats">Shirts</a>
								</li>
								<li class="nav-item">
									<a style="cursor:pointer;" class="red-a-nounder nav-link" id="itemtabt12" onclick="inventoryPage(1,12)" data-toggle="pill" role="tab" aria-controls="pills-hats">Pants</a>
								</li>
								<li class="nav-item">
									<a style="cursor:pointer;" class="red-a-nounder nav-link" id="itemtabt18" onclick="inventoryPage(1,18)" data-toggle="pill" role="tab" aria-controls="pills-hats">Faces</a>
								</li>
								<li class="nav-item">
									<a style="cursor:pointer;" class="red-a-nounder nav-link" id="itemtabt19" onclick="inventoryPage(1,19)" data-toggle="pill" role="tab" aria-controls="pills-hats">Gears</a>
								</li>
								<li class="nav-item">
									<a style="cursor:pointer;" class="red-a-nounder nav-link" id="itemtabt17" onclick="inventoryPage(1,17)" data-toggle="pill" role="tab" aria-controls="pills-hats">Heads</a>
								</li>
								<li class="nav-item">
									<a style="cursor:pointer;" class="red-a-nounder nav-link" id="itemtabt32" onclick="inventoryPage(1,32)" data-toggle="pill" role="tab" aria-controls="pills-hats">Packages</a>
								</li>
								<li class="nav-item">
									<a style="cursor:pointer;" class="red-a-nounder nav-link" id="outfitsbutton" onclick="outfitPage(1)" data-toggle="pill" role="tab" aria-controls="pills-hats">Outfits</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="row">
						<div class="col-sm mb-3" id="search_bar">
							<input autocomplete="off" type="text" name="keyword" id="keyword_input" class="form-control" placeholder="Search">
						</div>
					</div>
					<div class="tab-content" id="pills-tabContent">
						<div class="tab-pane fade show active" id="pills-hats" role="tabpanel" aria-labelledby="pills-hats-tab">
							<div class="card">
								<div class="card-body text-center">
									<div class="avatar-items-container">
										<div class="row" id="create-outfit-button" style="display:none";>
											<div class="col-sm mb-3">
												<button type="button" data-toggle="modal" data-target="#createoutfitpopup" style="float:right;" class="btn btn-sm btn-danger">Create Outfit</button>
											</div>
										</div>
										<ul id="itemsDiv"></ul>
									</div>
								</div>
							</div>
						</div>
						<div class="container-fluid" style="margin-top:10px;">
							<div class="row text-center">
								<div class="col-sm">
									<div id="page-buttons" class="btn-group" role="group" aria-label="First group">

									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="card mb-2">
				<div class="card-body text-center">
				<h5>Currently Wearing</h5>
					<div class="card">
						<div class="card-body text-center">
							<div class="avatar-items-container">
								<ul id="curWear">		
												
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="updateoutfitpopup" tabindex="-1" role="dialog" aria-labelledby="updateoutfitpopupLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Update Outfit</h5>
			</div>
			<div class="modal-body">
				<label>Name</label>
				<input class="form-control mb-3" type="text" id="update_outfit_name" autocomplete="off" placeholder="Outfit Name">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" style="float:left;" onclick="deleteOutfit()">Delete</button>
				<button type="button" class="btn btn-success" style="float:right;" onclick="updateOutfit($(\'#update_outfit_name\').val())">Update</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="createoutfitpopup" tabindex="-1" role="dialog" aria-labelledby="createoutfitpopupLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Create Outfit</h5>
			</div>
			<div class="modal-body">
				<label>Name</label>
				<input class="form-control mb-3" type="text" id="new_outfit_name" autocomplete="off" placeholder="Outfit Name">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" onclick="newOutfit($(\'#new_outfit_name\').val())">Create</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="avatarsettingspopup" tabindex="-1" role="dialog" aria-labelledby="avatarsettingspopupLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Avatar Settings</h5>
			</div>
			<div class="modal-body">
				<label>Headshot Style</label>
				<div>
					<div class="custom-control custom-checkbox custom-control-inline">
						<input type="checkbox" name="headshot_center_checkbox" class="custom-control-input headshot_checkbox_check" id="headshot_center" autocomplete="off">
						<label class="custom-control-label" for="headshot_center">Center</label>
					</div>
					<div class="custom-control custom-checkbox custom-control-inline">
						<input type="checkbox" name="headshot_left_checkbox" class="custom-control-input headshot_checkbox_check" id="headshot_left" autocomplete="off">
						<label class="custom-control-label" for="headshot_left">Angle Left</label>
					</div>
					<div class="custom-control custom-checkbox custom-control-inline">
						<input type="checkbox" name="headshot_right_checkbox" class="custom-control-input headshot_checkbox_check" id="headshot_right" autocomplete="off">
						<label class="custom-control-label" for="headshot_right">Angle Right</label>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<span id="avatar_settings_updating_notify" style="display:none;">Updating Settings...</span>
				<button type="button" class="btn btn-success" onclick="updateAvatarSettings()">Save</button>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="pickbc" tabindex="-1" role="dialog" aria-hidden="true" style="display:none;">
	<div class="modal-dialog" role="document" style="max-width:392px;">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Pick Body Color</h5>
				<span id="bctype"></span>
			</div>
			<div class="modal-body">
				<div class="color-palette">
					<ul>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1" title="White" style="background: rgb(242, 243, 243);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="5" title="Brick yellow" style="background: rgb(215, 197, 154);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="9" title="Light reddish violet" style="background: rgb(232, 186, 200);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="11" title="Pastel Blue" style="background: rgb(128, 187, 219);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="18" title="Nougat" style="background: rgb(204, 142, 105);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="21" title="Bright red" style="background: rgb(196, 40, 28);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="23" title="Bright blue" style="background: rgb(13, 105, 172);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="24" title="Bright yellow" style="background: rgb(245, 208, 48);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="26" title="Black" style="background: rgb(27, 42, 53);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="28" title="Dark green" style="background: rgb(40, 127, 71);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="29" title="Medium green" style="background: rgb(161, 196, 140);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="37" title="Bright green" style="background: rgb(75, 151, 75);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="38" title="Dark orange" style="background: rgb(160, 95, 53);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="45" title="Light blue" style="background: rgb(180, 210, 228);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="101" title="Medium red" style="background: rgb(218, 134, 122);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="102" title="Medium blue" style="background: rgb(110, 153, 202);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="104" title="Bright violet" style="background: rgb(107, 50, 124);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="105" title="Br. yellowish orange" style="background: rgb(226, 155, 64);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="106" title="Bright orange" style="background: rgb(218, 133, 65);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="107" title="Bright bluish green" style="background: rgb(0, 143, 156);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="119" title="Br. yellowish green" style="background: rgb(164, 189, 71);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="125" title="Light orange" style="background: rgb(234, 184, 146);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="135" title="Sand blue" style="background: rgb(116, 134, 157);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="141" title="Earth green" style="background: rgb(39, 70, 45);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="151" title="Sand green" style="background: rgb(120, 144, 130);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="153" title="Sand red" style="background: rgb(149, 121, 119);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="192" title="Reddish brown" style="background: rgb(105, 64, 40);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="194" title="Medium stone grey" style="background: rgb(163, 162, 165);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="199" title="Dark stone grey" style="background: rgb(99, 95, 98);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="208" title="Light stone grey" style="background: rgb(229, 228, 223);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="217" title="Brown" style="background: rgb(124, 92, 70);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="226" title="Cool yellow" style="background: rgb(253, 234, 141);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="360" title="Copper" style="background: rgb(150, 103, 102);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1001" title="Institutional white" style="background: rgb(248, 248, 248);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1002" title="Mid Grey" style="background: rgb(205, 205, 205);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1003" title="Really Black" style="background: rgb(17, 17, 17);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1004" title="Really Red" style="background: rgb(255, 0, 0);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1005" title="Deep Orange" style="background: rgb(255, 176, 0);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1006" title="Alder" style="background: rgb(180, 128, 255);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1007" title="Dusty Rose" style="background: rgb(163, 75, 75);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1008" title="Olive" style="background: rgb(193, 190, 66);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1009" title="New Yeller" style="background: rgb(255, 255, 0);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1010" title="Really Blue" style="background: rgb(0, 0, 255);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1011" title="Navy Blue" style="background: rgb(0, 32, 96);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1012" title="Deep Blue" style="background: rgb(33, 84, 185);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1013" title="Cyan" style="background: rgb(4, 175, 236);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1014" title="CGA Brown" style="background: rgb(170, 85, 0);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1015" title="Magenta" style="background: rgb(170, 0, 170);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1016" title="Pink" style="background: rgb(255, 102, 204);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1018" title="Teal" style="background: rgb(18, 238, 212);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1019" title="Toothpaste" style="background: rgb(0, 255, 255);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1020" title="Lime Green" style="background: rgb(0, 255, 0);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1021" title="Camo" style="background: rgb(58, 125, 21);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1022" title="Grime" style="background: rgb(127, 142, 100);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1023" title="Lavender" style="background: rgb(140, 91, 159);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1024" title="Pastel Light Blue" style="background: rgb(175, 221, 255);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1025" title="Pastel Orange" style="background: rgb(255, 201, 201);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1026" title="Pastel Violet" style="background: rgb(177, 167, 255);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1027" title="Pastel Blue-Green" style="background: rgb(159, 243, 233);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1028" title="Pastel Green" style="background: rgb(204, 255, 204);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1029" title="Pastel Yellow" style="background: rgb(255, 255, 204);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1030" title="Pastel Brown" style="background: rgb(255, 204, 153);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1031" title="Royal Purple" style="background: rgb(98, 37, 209);"></div></li>
						<li><div onclick="updbc($(this).attr(\'data-brickcolor\'));" data-toggle="tooltip" data-placement="top" data-brickcolor="1032" title="Hot Pink" style="background: rgb(255, 0, 191);"></div></li>
					</ul>
				</div>
			</div>
			<div class="modal-footer" id="closediv">
				<span id="bcinfo"></span>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<script>
	var assetTypeId = 8;
	var tempAlertDisplayed = false;
	var tempAlert = "";
	// old shit
	curbc = 0;
	clrs = {"1" : "rgb(242, 243, 243)", "5" : "rgb(215, 197, 154)", "9" : "rgb(232, 186, 200)", "11" : "rgb(128, 187, 219)", "18" : "rgb(204, 142, 105)", "21" : "rgb(196, 40, 28)", "23" : "rgb(13, 105, 172)", "24" : "rgb(245, 208, 48)", "26" : "rgb(27, 42, 53)", "28" : "rgb(40, 127, 71)", "29" : "rgb(161, 196, 140)", "37" : "rgb(75, 151, 75)", "38" : "rgb(160, 95, 53)", "45" : "rgb(180, 210, 228)", "101" : "rgb(218, 134, 122)", "102" : "rgb(110, 153, 202)", "104" : "rgb(107, 50, 124)", "105" : "rgb(226, 155, 64)", "106" : "rgb(218, 133, 65)", "107" : "rgb(0, 143, 156)", "119" : "rgb(164, 189, 71)", "125" : "rgb(234, 184, 146)", "135" : "rgb(116, 134, 157)", "141" : "rgb(39, 70, 45)", "151" : "rgb(120, 144, 130)", "153" : "rgb(149, 121, 119)", "192" : "rgb(105, 64, 40)", "194" : "rgb(163, 162, 165)", "199" : "rgb(99, 95, 98)", "208" : "rgb(229, 228, 223)", "217" : "rgb(124, 92, 70)", "226" : "rgb(253, 234, 141)", "360" : "rgb(150, 103, 102)", "1001" : "rgb(248, 248, 248)", "1002" : "rgb(205, 205, 205)", "1003" : "rgb(17, 17, 17)", "1004" : "rgb(255, 0, 0)", "1005" : "rgb(255, 176, 0)", "1006" : "rgb(180, 128, 255)", "1007" : "rgb(163, 75, 75)", "1008" : "rgb(193, 190, 66)", "1009" : "rgb(255, 255, 0)", "1010" : "rgb(0, 0, 255)", "1011" : "rgb(0, 32, 96)", "1012" : "rgb(33, 84, 185)", "1013" : "rgb(4, 175, 236)", "1014" : "rgb(170, 85, 0)", "1015" : "rgb(170, 0, 170)", "1016" : "rgb(255, 102, 204)", "1018" : "rgb(18, 238, 212)", "1019" : "rgb(0, 255, 255)", "1020" : "rgb(0, 255, 0)", "1021" : "rgb(58, 125, 21)", "1022" : "rgb(127, 142, 100)", "1023" : "rgb(140, 91, 159)", "1024" : "rgb(175, 221, 255)", "1025" : "rgb(255, 201, 201)", "1026" : "rgb(177, 167, 255)", "1027" : "rgb(159, 243, 233)", "1028" : "rgb(204, 255, 204)", "1029" : "rgb(255, 255, 204)", "1030" : "rgb(255, 204, 153)", "1031" : "rgb(98, 37, 209)", "1032" : "rgb(255, 0, 191)"}
	function tempErrorAlert(alert)
	{
		if (!tempAlertDisplayed) {
			$("#error_alert").text(alert)
			$("#error_alert").show();
			tempAlertDisplayed = true;
			setTimeout(function() {
				tempAlertDisplayed = false;
				$("#error_alert").hide();
			}, 2000);	
		}
	}
	
	function updateCharacter() {
		$("#character").removeClass("loading-rotate");
		$("#character").attr("src", "https://api.idk16.xyz/users/thumbnail?timestamp="+new Date().getTime());
	}
	function handleRender() {
		$.get("/avatar/renderstatus", function(data) {
			if(data == "pending") {
				setTimeout(function() {
					handleRender();
				}, 500);	
			} else {
				updateCharacter();
			}
		});
	}
	function characterloading()
	{
		document.getElementById("character").src = "";
		document.getElementById("character").src = "https://api.idk16.xyz/logo";
		document.getElementById("character").className = "loading-rotate card-img-top";
		//handleRender();
	}
	function pickbc(type) {
		ah = {"0": "Head", "1": "Torso", "2": "Left Arm", "3": "Right Arm", "4": "Left Leg", "5": "Right Leg"};
		$("#bctype").html(ah[type]);
		$("#pickbc").modal("show");
		curbc = type;
	}
	function updbc(clr) {
		$("#bcinfo").html("Updating color...");
		characterloading();
		$.post("/avatar/changebc", {bct:curbc,clr:clr}, function(data) {
			if(data == "s") {
				$("#bcinfo").html("");
				//$("#bc" + curbc).css("background-color", clrs[clr]);
				updateBodyColors();
				updateCharacter();
			} else {
				$("#bcinfo").html(data);
			}
		});
	}
	// ...
	function avatarChange(url)
	{
		characterloading();
		$.ajax(url, {
			xhrFields: {
				withCredentials: true
			},
			crossDomain: true,
			success: function () {
				//$("#error_alert").hide();
				getWearingItems();
				updateCharacter();
			},
			error: function (object) {
				tempErrorAlert(object.responseJSON.error)
				updateCharacter();
			}
		});
	}
	function updateBodyColors()
	{
		getJSONCDS("https://api.idk16.xyz/user/avatar/bodycolors")
		.done(function(jsonData) 
		{
			var data = jsonData;

			var html = \'<div onclick="pickbc(0)" id="bc0" class="bodyc-part bodyc-head" style="background-color: \'+data.Head+\'"></div>\';
			html += \'<div onclick="pickbc(2)" id="bc2" class="bodyc-part bodyc-rarm" style="background-color: \'+data.LeftArm+\'"></div>\';
			html += \'<div onclick="pickbc(1)" id="bc1" class="bodyc-part bodyc-torso" style="background-color: \'+data.Torso+\'"></div>\';
			html += \'<div onclick="pickbc(3)" id="bc3" class="bodyc-part bodyc-larm" style="background-color: \'+data.RightArm+\'"></div>\';
			html += \'<div onclick="pickbc(4)" id="bc4" class="bodyc-part bodyc-rleg" style="background-color: \'+data.LeftLeg+\'"></div>\';
			html += \'<div onclick="pickbc(5)" id="bc5" class="bodyc-part bodyc-lleg" style="background-color: \'+data.RightLeg+\'"></div>\';

			$("#bodycolor-dummy").html(html);
		});
	}

	function equipItem(assetId)
	{
		avatarChange("https://api.idk16.xyz/user/avatar/assets/wear?assetId=" + assetId);
	}
	function deequipItem(assetId)
	{
		avatarChange("https://api.idk16.xyz/user/avatar/assets/remove?assetId=" + assetId)
	}

	function inventoryPage(page, assettype, keyword)
	{
		if (assettype)
		{
			$("#itemtabt" + assetTypeId).removeClass("active");
			assetTypeId = assettype;
			$("#itemtabt" + assetTypeId).addClass("active");
		}
		$("#search_bar").show();
		$("#create-outfit-button").hide();
		$("#outfitsbutton").removeClass("active");
		getInventoryPage(assetTypeId, page, 8, keyword);
	}
	function getInventoryPage(assettype, page, limit, keyword="")
	{
		var html = "<li>";
		html += "<a style=\"cursor:pointer;\" onclick=\"equipItem({id})\">";
		html += "<img class=\"img-fluid avatar-item-img\" src=\"{thumbnail}\">";
		html += "<p>{name}</p>";
		html += "</a>";
		html += "</li>";
				
		multiPageHelper("inventoryPage", [assettype,keyword], "https://api.idk16.xyz/users/profile/inventory", "https://api.idk16.xyz/logo", "#itemsDiv", "#page-buttons", html, page, limit, keyword, "You don\'t own any items of this type", "&assetTypeId="+assettype);
	}

	$(".headshot_checkbox_check").click(function() {
		$(".headshot_checkbox_check").not(this).prop("checked", false);
	});

	function getAvatarSettings()
	{
		getJSONCDS("https://api.idk16.xyz/user/avatar/settings")
		.done(function(jsonData) 
		{
			var data = jsonData;

			$("#headshot_center").prop("checked", data.angleCenter);
			$("#headshot_right").prop("checked", data.angleRight);
			$("#headshot_left").prop("checked", data.angleLeft);
		});
	}

	function updateAvatarSettings()
	{
		if ($("#avatar_settings_updating_notify").is(":hidden")) //hack
		{
			var headshotStyleGetParam = ""
			if ($("#headshot_right").is(":checked")) {
				headshotStyleGetParam = "?angleRight=true";
			} else if ($("#headshot_left").is(":checked")) {
				headshotStyleGetParam = "?angleLeft=true";
			}

			$("#avatar_settings_updating_notify").show();

			getJSONCDS("https://api.idk16.xyz/user/avatar/updatesettings"+headshotStyleGetParam)
			.done(function(jsonData) 
			{
				$("#avatarsettingspopup").modal("toggle");
				$("#avatar_settings_updating_notify").hide();

				if (typeof jsonData.result != "boolean") { //we have a message
					$("#error_alert").text(jsonData.result);
					$("#error_alert").show();
					window.scrollTo({top: 0, behavior: "smooth"});
					setTimeout(function() 
					{
						$("#error_alert").hide();
					}, 3000);
				}
			});
		}
	}

	var editingOutfitId = 0;
	function newOutfit(name)
	{
		postJSONCDS("https://api.idk16.xyz/user/avatar/outfits/create", JSON.stringify({"name": name}))
		.done(function(object) {
			var alert = object.alert;
			var messageid = "#error_alert";
			if (alert == "Outfit Created") 
			{
				outfitPage(1);
			} 
			else 
			{
				$(messageid).text(alert);
				$(messageid).show();
				window.scrollTo({top: 0, behavior: "smooth"});
				setTimeout(function() 
				{
					$(messageid).hide();
				}, 3000);
			}
			$("#createoutfitpopup").modal("toggle");
		});
	}
	function applyOutfit(outfitid)
	{
		postJSONCDS("https://api.idk16.xyz/user/avatar/outfits/apply", JSON.stringify({"id": outfitid}))
		.done(function(object) {
			var alert = object.alert;
			var messageid = "#error_alert";
			if (alert == "Outfit Applied") 
			{
				getWearingItems();
				updateBodyColors();
				updateCharacter();
			} 
			else 
			{
				$(messageid).text(alert);
				$(messageid).show();
				window.scrollTo({top: 0, behavior: "smooth"});
				setTimeout(function() 
				{
					$(messageid).hide();
				}, 3000);
			}
		});
	}
	function updateOutfit(name)
	{
		postJSONCDS("https://api.idk16.xyz/user/avatar/outfits/update?update=true", JSON.stringify({"id": editingOutfitId, "name": name}))
		.done(function(object) {
			var alert = object.alert;
			var messageid = "#error_alert";
			if (alert == "Outfit Updated") 
			{
				outfitPage(1);
			} 
			else 
			{
				$(messageid).text(alert);
				$(messageid).show();
				window.scrollTo({top: 0, behavior: "smooth"});
				setTimeout(function() 
				{
					$(messageid).hide();
				}, 3000);
			}
			$("#updateoutfitpopup").modal("toggle");
		});
	}
	function deleteOutfit()
	{
		postJSONCDS("https://api.idk16.xyz/user/avatar/outfits/update?delete=true", JSON.stringify({"id": editingOutfitId}))
		.done(function(object) {
			var alert = object.alert;
			var messageid = "#error_alert";
			if (alert == "Outfit Deleted") 
			{
				outfitPage(1);
			} 
			else 
			{
				$(messageid).text(alert);
				$(messageid).show();
				window.scrollTo({top: 0, behavior: "smooth"});
				setTimeout(function() 
				{
					$(messageid).hide();
				}, 3000);
			}
			$("#updateoutfitpopup").modal("toggle");
		});
	}
	function setOutfitData(outfitid, name)
	{
		editingOutfitId = outfitid;
		$("#update_outfit_name").val(name);
	}
	function outfitPage(page)
	{
		$("#search_bar").hide();
		$("#create-outfit-button").show();
		$("#itemtabt" + assetTypeId).removeClass("active");
		$("#outfitsbutton").addClass("active");
		getOutfitPage(page, 8)
	}
	function getOutfitPage(page, limit)
	{
		var html = "<li>";
		html += "<a class=\"red-a-nounder\" style=\"cursor:pointer;\" onclick=\"applyOutfit({id})\">";
		html += "<img class=\"img-fluid\" src=\"{thumbnail}\">";
		html += "<p>{name}</p>";
		html += "</a>";
        html += "<div class=\"row mb-1\">";
		html += "<div class=\"col-sm\"><button type=\"button\" class=\"btn btn-sm btn-danger w-100\" data-toggle=\"modal\" data-target=\"#updateoutfitpopup\" onclick=\"setOutfitData({id},\'{name}\')\">Update</button></div>";
		html += "</div>";
		html += "</li>";
				
		multiPageHelper("outfitPage", [], "https://api.idk16.xyz/users/profile/outfits", "https://api.idk16.xyz/logo", "#itemsDiv", "#page-buttons", html, page, limit, "", "You don\'t have any outfits", "&assetTypeId=");
	}


	function getWearingItems()
	{
		var html = "<li>";
		html += "<a style=\"cursor:pointer;\" onclick=\"deequipItem({id})\">";
		html += "<img class=\"img-fluid avatar-item-img\" src=\"{thumbnail}\">";
		html += "<p>{name}</p>";
		html += "</a>";
		html += "</li>";
		
		staticPageHelper("https://api.idk16.xyz/users/profile/wearing", "https://api.idk16.xyz/logo", "#curWear", html, "", 12, "", "Not wearing any Items");
	}


	$("#keyword_input").keypress(function(event) {
		if (event.keyCode == 13 || event.which == 13) {
			inventoryPage(1, assetTypeId, $("#keyword_input").val())
		}
	});
	getWearingItems()
	inventoryPage(1,assetTypeId)

</script>
';

pageHandler();
$ph->body = $body;
$ph->pageTitle("Avatar");
$ph->output();