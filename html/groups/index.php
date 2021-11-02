<?php

$groupid = (int)$_GET['id'];

//stuff is wrong !!

if ($groupid)
{
	if (!is_int($groupid) || !groupExists($groupid))
	{
		http_response_code(404);
	}
}

$body = <<<EOT
<div class="container">
	<div id = "success_alert" class="alert alert-success" role="alert" style="display:none;"></div>
	<div id = "error_alert" class="alert alert-danger" role="alert" style="display:none;"></div>
	<div class="input-group mb-3">
		<input type="text" class="form-control" placeholder="Search Groups" id="search_query" autocomplete="off">
		<div class="input-group-append">
			<button class="btn btn-danger" type="button" onclick="window.location='/groups/search?keyword='+$('#search_query').val()">Search</button>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-3">
			<div class="card mb-3">
				<div class="card-body">
					<h5>My Groups</h5>
					<hr>
					<div id = "my_groups">
					
					</div>
					<button class="btn btn-sm btn-danger w-100" onclick="window.location='/groups/create'"><i class="fas fa-plus"></i> Create Group</button>
				</div>
			</div>
		</div>
		<div class="col-sm" id="main_groups_display"  style="display:none">
			<div class="row">
				<div class="col-sm">
					<div class="card">
						<div class="card-body">
							<div class="row" id = "group_info">
									
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row mt-3">
				<div class="col-sm">
					<div class="card">
						<div class="card-body">
							<div class="row mb-2">
								<div class="col-sm">
									<h5>Members</h5>
								</div>
								<div class="col-sm">
									<select onchange="updateUsers(this.value); this.selectedindex=-1" class="form-control" id = "roles_list">
									
									</select>
								</div>
							</div>
							<div class="row">
								<div class="col-sm" id = "members">

								</div>
							</div>
							<div class="text-center">
								<div class="btn-group mt-2" id = "member_buttons">

								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row mt-3" id="main_group_wall" style="display:none">
				<div class="col-sm">
					<div class="card">
						<div class="card-body">
							<div class="row mb-2">
								<div class="col-sm">
									<h5>Group Wall</h5>
									<hr>
									<div id="group_wall_post" style="display:none">
										<textarea class="form-control" style="min-height:6rem;max-height:6rem;" id="posts_input" autocomplete="off" placeholder="Post on Group Wall"></textarea>
										<button onclick="submitPost($('#posts_input').val())" class="btn btn-danger float-right mt-2">Post</button>	
									</div>
								</div>
							</div>
							<hr id = "group_wall_post_divide" style="display:none">
							<div id = "group_wall">
								<div class="row mb-2">
									<div class="col-sm" id = "group_posts">
											
									</div>
								</div>
								<div class="text-center">
									<div class="btn-group mt-2" id = "posts_buttons">
											
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
<script>

//global vars n shit
var id = -1
var currentrank = -1;
var wallDeletePermission = false;
var getparam = new URLSearchParams(window.location.search).get('id');
if (getparam)
{
	id = getparam;
}
	
//user groups
function myGroups()
{
	var html = '<div id="groupid{id}" class="card mb-2" style="min-height:3rem;padding-top:.2rem;padding-bottom:.2rem;cursor: pointer;" onclick="openGroup({id})">';
	html += '<div class="row m-auto w-100">';
	html += '<div class="col-sm-3 float-left mygroup-pane-mob">';
	html += '<img class="img-fluid" style="max-width:2.3rem;" src="{emblem}">';
	html += '</div>';
	html += '<div class="col-sm align-self-center no-overflow mygroup-pane-mob">';
	html += '<a class="black-a-nounder">{name}</a>';
	html += '</div>';
	html += '</div>';
	html += '</div>';
		
	staticPageHelper("https://api.alphaland.cc/users/groups", "https://api.alphaland.cc/logo", "#my_groups", html, "", 100, "", "No groups");
}
	
//posts
function postsPage(page)
{
	getPosts(id, page, 10);
}
function getPosts(groupid, page, limit)
{
	var html = '<div class="card">';
	html += '<div class="row m-2">';
	html += '<div class="col-sm-2">';
	html += '<a href="/profile/view?id={userid}">';
	html += '<div class="mobile-center">';
	html += '<img class="img-fluid" style="width:6rem;" src="{thumbnail}">';
	html += '</div>';
	html += '</a>';
	html += '</div>';
	html += '<div class="col-sm p-2">';
	html += '<div class="row">';
	html += '<div class="col-sm mobile-center">';
	html += '<a href="/profile/view?id={userid}" class="red-a">{username}</a>';
	html += '</div>';
	html += '<div class="col-sm">';
	html += '<p class="group-post-text" style="color:grey;">Posted: {postdate}</p>';
	html += '</div>';
	html += '</div>';
	html += '<div class="mobile-center">';
	html += '<p>"{post}"</p>';
	html += '</div>';
	if (wallDeletePermission) {
		html += '<button class="btn btn-sm btn-danger float-right delete_post" onclick="deletePost({groupid},{postid})">Delete</button>';
	}
	html += '</div>';
	html += '</div>';
	html += '</div>';	
	
	multiPageHelper("postsPage", [], "https://api.alphaland.cc/group/posts", "https://api.alphaland.cc/logo", "#group_posts", "#posts_buttons", html, page, limit, "", "No posts", "&id="+groupid);
}
	
//users based on role
function roleUsers(page)
{
	getRoleUsers(id, page, 8, currentrank);
}
function updateUsers(rank, override=false)
{
	currentrank = rank;
	roleUsers(1);
}
function getRoleUsers(groupid, page, limit, rank)
{
	var html = '<div class="card float-left text-center p-1 m-1 group-user-card" style="">';
	html += '<a href="/profile/view?id={userid}"><img class="img-fluid" src="{thumbnail}" style="width:8rem;"></a>';
	html += '<div class="text-center">';
	html += '<a href="/profile/view?id={userid}" class="red-a no-overflow">{username}</a>';
	html += '</div>';
	html += '</div>';
				
	multiPageHelper("roleUsers", [], "https://api.alphaland.cc/group/members", "https://api.alphaland.cc/logo", "#members", "#member_buttons", html, page, limit, "", "No users", "&id="+groupid+"&rank="+rank);
}
function groupRoles(groupid)
{
	var html = '<option value = "{rank}">{name} ({members})</option>';
		
	staticPageHelper("https://api.alphaland.cc/group/roles?id="+groupid, "https://api.alphaland.cc/logo", "#roles_list", html, "", 100, "", "Error occurred");
}
	
//general group info
function groupInfo(groupid)
{
	var html = '<div class="col-sm-3 text-center">'
	html += '<img class="img-fluid" src="{emblem}">';
	html += '</div>';
	html += '<div class="col-sm">';
	html += '<div class="row">';
	html += '<div class="col-sm">';
	html += '<h5>{name}</h5>';
	html += '</div>';
	html += '<div class="col-sm">';
	html += '<a class="float-right ml-1"><button class="btn btn-success" id="group_join_button" onclick="joinGroup({id})" style="display:none;">Join Group</button></a>';
	html += '<a class="float-right ml-1"><button class="btn btn-danger" id="group_leave_button" onclick="leaveGroup({id})" style="display:none;">Leave Group</button></a>';
	html += '<a class="float-right ml-1"><button class="btn btn-secondary" id="group_configure_button" onclick="window.location=\'/groups/config?id={id}\'" style="display:none;">Configure</button></a>';
	html += '</div>';
	html += '</div>';
	html += 'Owner: <a class="red-a" href = "/profile/view?id={creatorid}">{creatorname}</a>';
	html += '<p>Members: {members}</p>';
	html += '<h6>Description:</h6>';
	html += '<p>"{description}"</p>';
	html += '</div>';
		
	staticPageHelper("https://api.alphaland.cc/group/info?id="+groupid, "https://api.alphaland.cc/logo", "#group_info", html, "", 1, "", "Error occurred");
}

function deletePost(groupid, postid)
{
	postJSONCDS("https://api.alphaland.cc/group/update?id="+groupid+"&deletepost=true", JSON.stringify({"postid": postid}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Group Updated") {
			postsPage(1);
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

//group join/leave
function leaveGroup(groupid)
{
	getJSONCDS("https://api.alphaland.cc/group/leave?id="+groupid)
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Left Group") {
			initialize();
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

function joinGroup(groupid)
{
	getJSONCDS("https://api.alphaland.cc/group/join?id="+groupid)
	.done(function(object) 
	{
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Joined Group") 
		{
			myGroups();
			var newGroupElementExists = setInterval(function() 
			{
				if ($("#groupid"+groupid).length) 
				{
					openGroup(groupid);
					clearInterval(newGroupElementExists);
				}
			}, 100); //100 ms
			window.scrollTo({top: $("#main_group_wall").offset().top, behavior: "smooth"});
		} 
		else 
		{
			$("#error_alert").text(alert);
			$("#error_alert").show();
			window.scrollTo({top: 0, behavior: "smooth"});
			setTimeout(function() 
			{
				$("#error_alert").hide();
			}, 3000);
		}
	});
}
	
//open group page
function openGroup(groupid)
{
	$('#main_groups_display').hide();
	$('#main_group_wall').hide();
	$('#group_join_button').hide();
	$('#group_configure_button').hide();
	$('#group_leave_button').hide();
	$('#group_wall_post').hide();
	$('#group_wall_post_divide').hide();
	$('#error_alert').hide();
	
	wallDeletePermission = false;
	
	//initialize groupinfo, roles and update users portion
	if (groupid > 0)
	{
		$("#groupid"+id).removeClass("selected-my-group");
		id = groupid;
		window.history.replaceState(null, null, "?id="+id);
	}

	getJSONCDS("https://api.alphaland.cc/group/info?id="+id)
	.done(function(jsonData) 
	{
		var data = jsonData[0];
		if (data) //group exists
		{
			groupInfo(id);
			groupRoles(id);
			updateUsers(255); //owner first
			
			//set attribute for mygroup entries and show main group display
			$("#groupid"+id).addClass("selected-my-group");
			$('#main_groups_display').show();
			
			//we do some checks and retry in MS until the html is populated 
			if (!data.groupMember)
			{
				var joinButtonExists = setInterval(function() 
				{
					if ($('#group_join_button').length) 
					{
						if (data.pendingJoin)
						{
							$('#group_join_button').html('Join pending...');
							$('#group_join_button').prop("disabled", true);
						}
						$('#group_join_button').show();
						clearInterval(joinButtonExists);
					}
				}, 100); //100 ms
			}
			
			if (data.configPermission)
			{
				var configButtonExists = setInterval(function() 
				{
					if ($('#group_configure_button').length) 
					{
						$('#group_configure_button').show();
						clearInterval(configButtonExists);
					}
				}, 100); //100 ms
			}
			if (!data.groupOwner && data.groupMember && !data.pendingJoin)
			{
				var leaveButtonExists = setInterval(function() 
				{
					if ($('#group_leave_button').length) 
					{
						$('#group_leave_button').show();
						clearInterval(leaveButtonExists);
					}
				}, 100); //100 ms
			}
			if (data.wallViewPermission) 
			{
				if (data.wallDeletePermission)
				{
					wallDeletePermission = true;
				}
				if (data.wallPostPermission) 
				{
					$('#group_wall_post').show();
					$('#group_wall_post_divide').show();
				}
				$('#main_group_wall').show();
				postsPage(1);
			}
		}
	});
}
	
//submit post
function submitPost(post) 
{
	postJSONCDS("https://api.alphaland.cc/group/submitpost?groupid="+id, JSON.stringify({"post": post}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Post Placed") 
		{
			messageid = "#success_alert";
			$("#posts_input").val('');
			postsPage(1);
			window.scrollTo({top: $("#main_group_wall").offset().top, behavior: "smooth"});
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
	
//initialize (populate players groups list then open first group available if exists)
function initialize()
{
	myGroups(); //show player groups
	
	getJSONCDS("https://api.alphaland.cc/users/groups")
	.done(function(jsonData) 
	{
		var data = jsonData[0];
		if (data) //has groups
		{
			if (!getparam || !$.isNumeric(id) || id == 0)
			{
				if (data) 
				{
					id = data.id;
				}
			}
		}
		openGroup(id);
	});
}
initialize();
</script>
EOT;
pageHandler();
$ph->pageTitle("My Groups");
$ph->body = $body;
$ph->output();