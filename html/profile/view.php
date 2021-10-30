<?php

$alert = "";

if(isset($_GET['id'])) 
{
	$id = (int)$_GET['id'];
	$info = userInfo($id); // add true as a second param if u wanna use usernames instead
	if($info !== false) 
	{		
		if(isset($_POST['sendfriend'])) 
		{
			sendFriendRequest($info->id);
		}
			
		if(isset($_POST['removefriend'])) 
		{
			removeFriend($info->id);
		}
		
		if(isset($_POST['acceptfriend'])) 
		{
			acceptFriendRequest($info->id);
		}
			
		if (banned($id))
		{
			redirect("/404");
		}

		$body = <<<EOT
		<div class="container">
			{$alert}
			<div class="row">
				<div class="col-sm">
					<div class="card mb-2">
						<div id = "upper_profile" class="card-body text-center">
							
						</div>
					</div>
					<div class="card mb-2">
						<div class="card-body text-center">
							<div class="text-center">
								<h5>Alphaland Badges</h5>
							</div>
							<div id = "badges_container" class="ul-container text-center">
								
							</div>
						</div>
					</div>
				</div>
				<div class="col-sm">
					<div class="card mb-2">
						<div class="card-body">
					      <div id="game_slides_container" class="slideshow-container">
							
						  </div>
						  <br>
						  <div id="game_slides_dots" style="text-align:center;overflow:hidden;white-space:nowrap;width:100%;">
							
						  </div>
						</div>
					</div>
					<div class="card mb-2">
						<div class="card-body">
							<div id = "friends_container" class="text-center">
								
							</div>
							<div class="ul-container text-center">
								<ul id = "inner_friends_container">

								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="container mb-2" id="profile_badges_main">
			<div class="card">
				<div class="card-body">
					<div class="text-center">
						<h5>Badges</h5>
					</div>
					<div class="profile-badges text-center">
						<ul id="usermade_badges_container">

						</ul>
					</div>
				</div>
				<div class="mb-2 text-center">
					<div id="usermade_badges_page_buttons" class="btn-group">

					</div>
				</div>
			</div>
		</div>
		<div class="container mb-2" id="profile_groups_main">
			<div class="card">
				<div class="card-body">
					<div class="text-center">
						<h5>Groups</h5>
					</div>
					<div class="profile-groups text-center">
						<ul id="groups_container">

						</ul>
					</div>
				</div>
				<div class="mb-2 text-center">
					<div id="groups_page-buttons" class="btn-group">

					</div>
				</div>
			</div>
		</div>
		<div class="container-fluid" id="user_inventory">
			<div class="container">
				<div class="row">
					<div class="col-sm">
						<div class="card">
							<div class="card-body text-center">
								<h5>Inventory</h5>
								<ul class="nav nav-tabs" id="myTab" role="tablist">
									<li class="nav-item">
										<a onclick="inventoryPage(1, 8)" class="nav-link active red-a" id="hats-tab" data-toggle="tab" href="#hats" role="tab" aria-controls="hats" aria-selected="true">Hats</a>
									</li>
									<li class="nav-item">
										<a onclick="inventoryPage(1, 2)" class="nav-link red-a" id="tshirts-tab" data-toggle="tab" href="#tshirts" role="tab" aria-controls="tshirts" aria-selected="false">T-Shirts</a>
									</li>
									<li class="nav-item">
										<a onclick="inventoryPage(1, 11)" class="nav-link red-a" id="shirts-tab" data-toggle="tab" href="#shirts" role="tab" aria-controls="shirts" aria-selected="false">Shirts</a>
									</li>
									<li class="nav-item">
										<a onclick="inventoryPage(1, 12)" class="nav-link red-a" id="pants-tab" data-toggle="tab" href="#pants" role="tab" aria-controls="pants" aria-selected="false">Pants</a>
									</li>
									<li class="nav-item">
										<a onclick="inventoryPage(1, 18)" class="nav-link red-a" id="faces-tab" data-toggle="tab" href="#faces" role="tab" aria-controls="faces" aria-selected="false">Faces</a>
									</li>
									<li class="nav-item">
										<a onclick="inventoryPage(1, 19)" class="nav-link red-a" id="gears-tab" data-toggle="tab" href="#gears" role="tab" aria-controls="gears" aria-selected="false">Gears</a>
									</li>
									<li class="nav-item">
										<a onclick="inventoryPage(1, 17)" class="nav-link red-a" id="heads-tab" data-toggle="tab" href="#heads" role="tab" aria-controls="heads" aria-selected="false">Heads</a>
									</li>
									<li class="nav-item">
										<a onclick="inventoryPage(1, 32)" class="nav-link red-a" id="packages-tab" data-toggle="tab" href="#packages" role="tab" aria-controls="packages" aria-selected="false">Packages</a>
									</li>
									<li class="nav-item">
										<a onclick="inventoryPage(1, 3)" class="nav-link red-a" id="audios-tab" data-toggle="tab" href="#audios" role="tab" aria-controls="audios" aria-selected="false">Audios</a>
									</li>
									<li class="nav-item">
										<a onclick="inventoryPage(1, 10)" class="nav-link red-a" id="models-tab" data-toggle="tab" href="#models" role="tab" aria-controls="models" aria-selected="false">Models</a>
									</li>
								</ul>
								<div class="tab-content" id="myTabContent">
									<div class="tab-pane fade show active" id="hats" role="tabpanel" aria-labelledby="hats-tab">
										<div class="row">
											<div class="col-sm inventory-container">
												<ul id="invitems"></ul>
											</div>
										</div>
									</div>
									<div class="btn-group mt-1" role="group" aria-label="First group" id="invpages">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="curwearingdisplay" tabindex="-1" role="dialog" aria-hidden="true" style="display:none;">
			<div class="modal-dialog modal-dialog-centered" style="max-width:750px!important;" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="curWearingUsername"></h5>
					</div>
					<div class="card">
						<div class="card-body">
							<div class="cwearingmodal-ul-container">
								<ul id="curWearing">		
								
								</ul>
							</div>
						</div>
					</div>
					<div class="modal-footer" id="closediv">
						<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		<script>
		var user = new URLSearchParams(window.location.search).get('id');
		var assetTypeId = 8;
		var loadingHtml = '<div class="text-center"><img src="https://api.alphaland.cc/logo" class="loading-rotate" width="250" height="250" /></div>';

		function getProfileInfo(userid)
		{
			$("#upper_profile").html(loadingHtml);

			getJSONCDS('https://api.alphaland.cc/users/profile/info?userId=' + userid)
			.done(function(jsonData) 
			{
				var onlineStatusHtml = "";
				if (jsonData[0].siteStatus == "Online") {
					onlineStatusHtml = '<p><a class="blue-a-nounder">Online</a></p>';
				} else if (jsonData[0].siteStatus == "Offline") {
					onlineStatusHtml = '<p class="offline-profile">Offline</p>';
				} else if (jsonData[0].siteStatus == "In-Game") {
					onlineStatusHtml = '<p><a class="green-a-nounder">In-Game</a></p>';
				} else {
					onlineStatusHtml = '<p><a class="green-a-nounder" href="/games/view?id=' + jsonData[0].gameAssetId + '">' + 'Playing: ' + jsonData[0].siteStatus + '</a></p>';
				}
				
				var friendStatusHtml = "";
				if (jsonData[0].friendsStatus == "Friends") {
					friendStatusHtml = '<button type="submit" name="removefriend" class="btn btn-danger">Remove Friend</button>';
				} else if (jsonData[0].friendsStatus == "Pending") {
					friendStatusHtml = '<button class="btn btn-danger" disabled>Friend Pending</button>';
				} else if (jsonData[0].friendsStatus == "Incoming") {
					friendStatusHtml = '<button type="submit" name="acceptfriend" class="btn btn-success">Accept Friend Request</button>';
				} else if (jsonData[0].friendsStatus == "User") {
					friendStatusHtml = '<button type="submit" name="sendfriend" class="btn btn-success">Send Friend Request</button>';
				}

				if (jsonData[0].privateInventory) {
					$('#user_inventory').hide();
				}

				var profileHtml = onlineStatusHtml;
				profileHtml += '<h5>{username}</h5>';
				profileHtml += '<div class="text-center" style="overflow:hidden;">';
				profileHtml += '<span>"{shout}"</span>';
				profileHtml += '</div>';
				profileHtml += '<div class="row justify-content-center">';
				profileHtml += '<img class="img-fluid" style="max-width:298px;" src="{thumbnail}">';
				profileHtml += '</div>';
				profileHtml += '<button class="btn btn-sm btn-danger mb-1" onclick="currentlyWearing()">Currently Wearing</button>';
				profileHtml += '<h5>Blurb:</h5>';
				profileHtml += '<p style="overflow:auto;max-height:10rem;">{blurb}</p>';
				profileHtml += '<div class="card-body">';
				profileHtml += '<form action="" method="post">' + friendStatusHtml + '</form>';
				profileHtml += '</div>';
				profileHtml += '<h6>Join Date: {joindate}</h6>';
				profileHtml += '<h6>Place Visits: {placevisits}</h6>';
				profileHtml += '<div class="row">';
				profileHtml += '<div class="col-sm">';
				profileHtml += '<a class="red-a" style="float: right;" href="#">Report Abuse</a>';
				profileHtml += '</div>';
				profileHtml += '</div>';

				$("#upper_profile").html(parseHtml(profileHtml, 1, jsonData, "Error occurred"));
			});
		}

		function getOfficialBadges(userid)
		{
			//TODO: merge with regular badges?
			var html = '<ul>';
			html += '<li>';
			html += '<a href="/badges/" title="{badgeName}">';
			html += '<img class="profile-icon" src="{badgeImage}">';
			html += '</a>';
			html += '</li>';
			html += '</ul>';
				
			staticPageHelper("https://api.alphaland.cc/users/profile/badges", "https://api.alphaland.cc/logo", "#badges_container", html, 1, 9, "", "User has no official Badges", "&userId="+userid+"&official=true")
		}

		function userBadgesPage(page)
		{
			getUserBadges(user, page, 6);
		}
		function getUserBadges(userid, page, limit)
		{	
			var html = '<li>';
			html += '<a class="red-a-nounder" href="/badges/view?id={badgeId}">';
			html += '<div class="card">';
			html += '<div class="card-body">';
			html += '<img class="img-fluid" src="{badgeImage}">';
			html += '<p class="">{badgeName}</p>';
			html += '</div>';
			html += '</div>';
			html += '</a>';
			html += '</li>';

			multiPageHelper("userBadgesPage", "https://api.alphaland.cc/users/profile/badges", "https://api.alphaland.cc/logo", "#usermade_badges_container", "#usermade_badges_page_buttons", html, page, limit, "", "User has no Badges", "&userId="+userid);
		}

		function getUserFriends(userid)
		{
			$("#friends_container").html(loadingHtml);

			getJSONCDS('https://api.alphaland.cc/users/profile/friends?userId=' + userid + '&page=1&limit=6')
			.done(function(jsonData) {
				$("#friends_container").html('<a class = "red-a" href = "/friends/view?id=' + userid + '">Friends (' + jsonData.friendsCount + ')</a>');
				
				var friendsHtml = '<li>';
				friendsHtml += '<a href = "view?id={userid}" title="{username}" class="red-a">';
				friendsHtml += '<img class="profile-icon" src="{thumbnail}">';
				friendsHtml += '<p class="no-overflow">{username}</p>';
				friendsHtml += '</a>';
				friendsHtml += '</li>';
				
				$("#inner_friends_container").html(parseHtml(friendsHtml, jsonData.pageResults, jsonData, "User has no Friends"));
			});
		}

		function getUserCurWearing(userid)
		{
			getJSONCDS('https://api.alphaland.cc/users/profile/wearing?userId=' + userid)
			.done(function(jsonData) {
				$("#curWearingUsername").html(jsonData.username + '\'s Currently Wearing');
				
				var wearingHtml = '<li>';
				wearingHtml += '<div class="card">';
				wearingHtml += '<div class="card-body text-center">';
				wearingHtml += '<a href="/catalog/view?id={id}"><img class="img-fluid" src="{thumbnail}"></a>';
				wearingHtml += '<a class="red-a no-overflow" href="/catalog/view?id={id}">{name}</a>';
				wearingHtml += '</div>';
				wearingHtml += '</div>';
				wearingHtml += '</li>';
				
				$("#curWearing").html(parseHtml(wearingHtml, jsonData.itemCount, jsonData, "User isn't wearing any items"));
			});
		}

		function getUserGames(userid)
		{
			$("#game_slides_container").html(loadingHtml);

			getJSONCDS('https://api.alphaland.cc/users/profile/games?userId=' + userid)
			.done(function(jsonData) {
				var gamesCount = jsonData.gamesCount;
			
				var games_html = '<div class="mySlides gamepanel-fade">';
				games_html += '<div class="container">';
				games_html += '<a class="black-a-nounder" href="/games/view?id={id}"><h6 style="text-overflow:ellipsis;white-space:nowrap;overflow:hidden;">{name}</h6></a>';
				games_html += '</div>';
				games_html += '<div class="text-center">';
				games_html += '<a class="black-a-nounder" href="/games/view?id={id}"><img src="{thumbnail}" class="img-fluid" style="max-height: 300px;"></a>';
				games_html += '</div>';
				games_html += '<div class="game-text">{description}</div>';
				games_html += '<div class="text-center">';
				games_html += '<a href="/games/view?id={id}" class="btn btn-lg btn-danger">Play <i class="fas fa-caret-right"></i></a>';
				games_html += '<button class="btn btn-lg btn-success" disabled>Build <i class="fas fa-hammer"></i></button>';
				games_html += '</div>';
				games_html += '</div>';
				games_html += '<a class="prev" onclick="plusSlides(-1)">&#10094;</a>';
				games_html += '<a class="next" onclick="plusSlides(1)">&#10095;</a>';
				
				
				if (gamesCount > 0) {
					var dots_html = '';
					for (var i = 0; i < gamesCount; i++) {
						dots_html += '<span class="dot"></span>';
					}
					$("#game_slides_container").html(parseHtml(games_html, gamesCount, jsonData, "Error occurred"));
					$("#game_slides_dots").html(dots_html);
					showSlides(1);
				} else {
					$("#game_slides_container").html("User has no Games");
				}	
			});
		}
		
		function inventoryPage(page, assettype)
		{
			if (assettype)
			{
				assetTypeId = assettype;
			}
			getInventory(user, assetTypeId, page, 15);
		}
		
		function getInventory(userid, assettype, page, limit)
		{
			var html = '<li>';
			html += '<a class="a-nostyle" href="/catalog/view?id={id}" title="{name}"><img style="width: 155.5px;height: 155.2px" class="profile-icon" src="{thumbnail}">';
			html += '</a>';
			html += '<a class="red-a" href="/catalog/view?id={id}">';
			html += '<p class="inventory-item-name">{name}</p>';
			html += '</a>';
			html += '<p class="inventory-item-name">By: <a class="red-a" href="view?id={creatorId}">{creator}</a></p>';
			html += '</li>';
			
			multiPageHelper("inventoryPage", "https://api.alphaland.cc/users/profile/inventory", "https://api.alphaland.cc/logo", "#invitems", "#invpages", html, page, limit, "", "User has no items of this type", "&userId="+userid+"&assetTypeId="+assettype);
		}

		function currentlyWearing() {
			$('#curwearingdisplay').modal('show');
		}
		
		function groupsPage(page)
		{
			getGroups(user, page, 6);
		}
		
		function getGroups(userid, page, limit)
		{
			var html = '<li>';
			html +=	'<a class="red-a-nounder" href="/groups?id={id}">';
			html +=	'<div class="card">';
			html +=	'<div class="card-body text-center p-2">';
			html +=	'<img class="img-fluid" src="{emblem}">';
			html +=	'<p class="">{name}</p>';
			html +=	'<p class="grey">Members:</p>';
			html +=	'<p class="grey">{members}</p>';
			html +=	'<p class="grey">Rank:</p>';
			html +=	'<p class="grey">{rank}</p>';
			html +=	'</div>';
			html +=	'</div>';
			html +=	'</a>';
			html +=	'</li>';
			
			multiPageHelper("groupsPage", "https://api.alphaland.cc/users/groups", "https://api.alphaland.cc/logo", "#groups_container", "#groups_page-buttons", html, page, limit, "", "User has no Groups", "&userId="+userid);
		}
		
		//slides stuff
		var slideIndex = 1;
		
		function showSlides(n) {
			var i;
			var slides = document.getElementsByClassName("mySlides");
			var dots = document.getElementsByClassName("dot");
			if (n > slides.length) {slideIndex = 1}
			if (n < 1) {slideIndex = slides.length}
			for (i = 0; i < slides.length; i++) {
				slides[i].style.display = "none";
			}
			for (i = 0; i < dots.length; i++) {
				dots[i].className = dots[i].className.replace(" active-game", "");
			}
			slides[slideIndex-1].style.display = "block";
			dots[slideIndex-1].className += " active-game";
		}
		
		function plusSlides(n) {
			showSlides(slideIndex += n);
		}

		getProfileInfo(user);
		getOfficialBadges(user); //official badges
		userBadgesPage(1);
		getUserFriends(user);
		getUserCurWearing(user);
		getUserGames(user);
		inventoryPage(1, assetTypeId);
		groupsPage(1);

		</script>
EOT;
	}
	else 
	{
		redirect('/404');
	}
} 
else 
{
	//viewing your own profile
	header('Location: view?id=' . $GLOBALS['user']->id);
}
pageHandler();
$ph->pageTitle("Profile");
$ph->body = $body;
$ph->output();