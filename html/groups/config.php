<?php

use Finobe\Groups\Group;

$groupid = (int)$_GET['id'];

if ($groupid)
{
	if (!is_int($groupid) || !Group::Exists($groupid) || !Group::ConfigPermission($user->id, $groupid))
	{
		http_response_code(404);
	}
}
else
{
	http_response_code(404);
}

$body = <<<EOT
<div class="container">
	<div id = "error_alert" class="alert alert-danger" role="alert" style="display:none;"></div>
	<div class="row">
		<div class="col-sm-3 mb-4">
			<div class="card">
				<div class="card-body text-center">
					<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
						<a class="nav-link active red-a-nounder" id="v-pills-general-tab" data-toggle="pill" href="#v-pills-general" role="tab" aria-controls="v-pills-general" aria-selected="true">General</a>
						<a class="nav-link red-a-nounder" id="v-pills-members-tab" data-toggle="pill" href="#v-pills-members" role="tab" aria-controls="v-pills-members" aria-selected="false">Members</a>
						<a class="nav-link red-a-nounder" id="v-pills-roles-tab" data-toggle="pill" href="#v-pills-roles" role="tab" aria-controls="v-pills-roles" aria-selected="false">Roles</a>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm">
			<div class="card">
				<div class="card-body">
					<div class="tab-content" id="v-pills-tabContent">
						<div class="tab-content" id="v-pills-tabContent">
							<div class="tab-pane fade show active" id="v-pills-general" role="tabpanel" aria-labelledby="v-pills-general-tab">
								<div class="container text-center">
									<h5>General</h5>
									<hr>
									<img class="img-fluid mb-2" style="width:14rem;" id="group_emblem" src="https://t7.rbxcdn.com/842728e0a8e241d132cafbee29f10a57">							
									<div class="input-group mb-2">
										<div class="custom-file">
											<input type="file" name="icon_file" class="custom-file-input" id="inputIconFile">
											<label class="custom-file-label" for="inputIconFile">Group Icon File</label>
										</div>
									</div>
									<textarea class="form-control mb-2" style="height:12rem;max-height:12rem;min-height:12rem;" name="gdesc" id = "group_description" autocomplete="off"></textarea>		
									<div class="custom-control custom-checkbox custom-control-inline mb-2">
										<input type="checkbox" name="manual_checkbox" class="custom-control-input" id="manual_join_requests">
										<label class="custom-control-label" for="manual_join_requests">Manual Join Requests</label>
									</div>
									<h5>Join Requests</h5>
									<hr>
									<div class="card w-100 mb-2">
										<div class="card-body">
											<div class="row">
												<div class="col-sm">
													<div class="group-approval-container">
														<ul id="join_requests">
	
														</ul>
													</div>
												</div>
											</div>
										</div>
									</div>
									<div class="text-center">
										<div class="btn-group mb-3" id="join_requests_buttons">

										</div>
									</div>
									<button class="btn btn-danger w-100" onclick="configGroup()">Update</button>
								</div>
							</div>
							<div class="tab-pane fade" id="v-pills-members" role="tabpanel" aria-labelledby="v-pills-members-tab">
								<div class="container text-center">
									<h5>Members</h5>
									<hr>
									<div class="row mb-2">
										<div class="col-sm" id="members_manage_list">
											
										</div>	
									</div>
									<div class="text-center">
										<div class="btn-group mt-2 mb-2" role="group" aria-label="First group" id="members_manage_buttons">

										</div>
									</div>
								</div>
							</div>
							<div class="tab-pane fade" id="v-pills-roles" role="tabpanel" aria-labelledby="v-pills-roles-tab">
								<div class="container">
									<div class="row">
										<div class="col-sm-4">
											<div class="card">
												<div class="card-body">
													<div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
														<h6 class="text-center">Roles</h6>
														<div id = "current_group_roles">
														
														</div>
														<button type="button" class="btn btn-sm btn-danger mt-3" data-toggle="modal" data-target="#createnewrole">Create New</button>
														<div class="modal fade" id="createnewrole" tabindex="-1" role="dialog" aria-labelledby="createnewroleLabel" aria-hidden="true">
															<div class="modal-dialog" role="document">
																<div class="modal-content">
																	<div class="modal-header">
																		<h5 class="modal-title" id="exampleModalLabel">Create New Role</h5>
																		<button type="button" class="close" data-dismiss="modal" aria-label="Close">
																			<span aria-hidden="true">&times;</span>
																		</button>
																	</div>
																	<div class="modal-body">
																	<label>Role Name</label>
																		<input class="form-control mb-3" type="text" id="new_role_name" autocomplete="off" placeholder="Role Name">
																		<label>Rank</label>
																		<input class="form-control" type="text" id="new_role_rank" autocomplete="off" placeholder="0 - 254">
																		<strong class="mt-1">This will cost you 15 Alphabux</strong>
																	</div>
																	<div class="modal-footer">
																		<button type="button" class="btn btn-danger" onclick="createRole()">Create Role</button>
																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-sm">
											<div id="reg_role_configuration">
												<h5 class="text-center" id="role_name"></h5>
												<label>Role Name</label>
												<input class="form-control mb-3" type="text" name="rolename" id="rolename_input" placeholder="Role Name">
												<div id="reg_config_elements">
													<label>Rank</label>
													<input class="form-control" type="text" name="rolerank" id="rolerank_input" placeholder="0 - 255">
													<hr>
													<h5 class="text-center">Permissions</h5>
													<div class="row mb-2 border-top border-bottom">
														<div class="col-sm">
															<strong>View group wall</strong>
														</div>
														<div class="col-sm-3">
															<div class="form-check float-right">
																<input class="form-check-input position-static" type="checkbox" id="groupwallaccess" value="viewgroupwall" aria-label="...">
															</div>
														</div>
													</div>
													<div class="row mb-2 border-top border-bottom">
														<div class="col-sm">
															<strong>Post on group wall</strong>
														</div>
														<div class="col-sm-3">
															<div class="form-check float-right">
																<input class="form-check-input position-static" type="checkbox" id="grouppostaccess" value="postgroupwall" aria-label="...">
															</div>
														</div>
													</div>
													<div class="row mb-2 border-top border-bottom">
														<div class="col-sm">
															<strong>Delete group wall posts</strong>
														</div>
														<div class="col-sm-3">
															<div class="form-check float-right">
																<input class="form-check-input position-static" type="checkbox" id="groupdeleteaccess" value="deletegroupwall" aria-label="...">
															</div>
														</div>
													</div>
													<div class="row mb-2 border-top border-bottom">
														<div class="col-sm">
															<strong>Post group shout</strong>
														</div>
														<div class="col-sm-3">
															<div class="form-check float-right">
																<input class="form-check-input position-static" type="checkbox" id="groupshoutaccess" value="postgroupshout" aria-label="...">
															</div>
														</div>
													</div>
													<div class="row mb-2 border-top border-bottom">
														<div class="col-sm">
															<strong>Manage lower ranked members ranks</strong>
														</div>
														<div class="col-sm-3">
															<div class="form-check float-right">
																<input class="form-check-input position-static" type="checkbox" id="groupmanagelowerranks" value="managelowermemranks" aria-label="...">
															</div>
														</div>
													</div>
													<div class="row mb-2 border-top border-bottom">
														<div class="col-sm">
															<strong>Kick lower ranked members</strong>
														</div>
														<div class="col-sm-3">
															<div class="form-check float-right">
																<input class="form-check-input position-static" type="checkbox" id="groupkicklowerranks" value="kicklowermemranks" aria-label="...">
															</div>
														</div>
													</div>
													<div class="row mb-2 border-top border-bottom">
														<div class="col-sm">
															<strong>Accept join requests</strong>
														</div>
														<div class="col-sm-3">
															<div class="form-check float-right">
																<input class="form-check-input position-static" type="checkbox" id="groupacceptjoinrequests" value="accjoinrequests" aria-label="...">
															</div>
														</div>
													</div>
													<div class="row mb-2 border-top border-bottom">
														<div class="col-sm">
															<strong>View Audit Log</strong>
														</div>
														<div class="col-sm-3">
															<div class="form-check float-right">
																<input class="form-check-input position-static" type="checkbox" id="groupauditaccess" value="viewauditlog" aria-label="...">
															</div>
														</div>
													</div>
												</div>
												<button class="btn btn-sm btn-danger w-100" onclick="updateRole()">Save</button>
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
<script>
//global vars
var currentRolesRank = -1;
var groupid = new URLSearchParams(window.location.search).get('id');
var image = "";

//picture upload handling
function readEmblem(input) 
{
  if (input.files && input.files[0])
  {
    var reader = new FileReader();
    reader.onload = function(e) {
    	image = e.target.result;
     	$('#group_emblem').attr('src', image);
    }
    reader.readAsDataURL(input.files[0]);
  }
}

$("#inputIconFile").change(function() {
	readEmblem(this);
});

//roles tab 

function createRole()
{
	postJSONCDS("https://api.idk16.xyz/group/update?id="+groupid+"&newrole=true", JSON.stringify({"name":$('#new_role_name').val(),"rank":parseInt($('#new_role_rank').val())}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Group Updated") {
			$('#new_role_name').val('');
			$('#new_role_rank').val('');
			currentRoles();
		}
		else
		{
			$(messageid).text(alert);
			$(messageid).show();
			
			setTimeout(function() 
			{
				$(messageid).hide();
			}, 3000);
		}
		$("#createnewrole").modal('hide');
	});
}

function updateRole()
{
	postJSONCDS("https://api.idk16.xyz/group/update?id="+groupid+"&updaterole=true", JSON.stringify({
	"rank":currentRolesRank,
	"NewRank":parseInt($('#rolerank_input').val()),
	"Name":$("#rolename_input").val(),
	"AccessGroupWall":$('#groupwallaccess').prop('checked'),
	"PostGroupWall":$('#grouppostaccess').prop('checked'),
	"DeleteGroupWallPosts":$('#groupdeleteaccess').prop('checked'),
	"PostGroupShout":$('#groupshoutaccess').prop('checked'),
	"ManageLowerRanks":$('#groupmanagelowerranks').prop('checked'),
	"KickLowerRanks":$('#groupkicklowerranks').prop('checked'),
	"AcceptJoinRequests":$('#groupacceptjoinrequests').prop('checked'),
	"ViewAuditLog":$('#groupauditaccess').prop('checked')
	}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Group Updated") {
			currentRolesRank = $('#rolerank_input').val();
		}
		else
		{
			$(messageid).text(alert);
			$(messageid).show();
			
			setTimeout(function() 
			{
				$(messageid).hide();
			}, 3000);
		}
		currentRoles();
		openRole(currentRolesRank);
		window.scrollTo({top: 0, behavior: "smooth"});
	});
}

function openRole(rank="")
{
	getJSONCDS("https://api.idk16.xyz/group/roles?id="+groupid+"&rank="+rank)
	.done(function(jsonData) {
		var data = jsonData[0];
		if (data)
		{
			if (!rank)
			{
				currentRolesRank = data.rank;
				rank = data.rank;
			}
			
			if (rank == 255) {
				$("#reg_config_elements").hide();
			} else {
				$("#reg_config_elements").show();
			}
			
			var roleTabExists = setInterval(function() 
			{
				if ($("#"+currentRolesRank+"-tab").length) 
				{
					$("#"+currentRolesRank+"-tab").removeClass("active")
					currentRolesRank = data.rank;
					console.log("#"+currentRolesRank+"-tab");
					$("#role_name").html(data.name+" Role");
					$("#rolename_input").val(data.name);
					$("#rolerank_input").val(data.rank);
					$("#groupwallaccess").prop('checked', data.wallViewPermission);
					$("#grouppostaccess").prop('checked', data.wallPostPermission);
					$("#groupdeleteaccess").prop('checked', data.wallDeletePermission);
					$("#groupshoutaccess").prop('checked', data.postShoutPermission);
					$("#groupmanagelowerranks").prop('checked', data.manageLowerRankPermission);
					$("#groupkicklowerranks").prop('checked', data.kickLowerRankPermission);
					$("#groupacceptjoinrequests").prop('checked', data.acceptJoinRequestPermission);
					$("#groupauditaccess").prop('checked', data.viewAuditLogPermission);
					$("#"+currentRolesRank+"-tab").addClass("active");
					clearInterval(roleTabExists);
				}
			}, 100); //100 ms
		}
	});
}

function currentRoles()
{
	var html = '<a class="nav-link red-a-nounder mouse-hover" id="{rank}-tab" onclick="openRole({rank})">{name}</a>';
		
	staticPageHelper("https://api.idk16.xyz/group/roles", "https://api.idk16.xyz/logo", "#current_group_roles", html, "", 100, "", "Error occurred", "&id="+groupid);
	
	openRole();
}

//group config

function configGroup()
{
	var approvals = false;
	if ($('#manual_join_requests').is(":checked"))
	{
		approvals = true;
	}
	
	postJSONCDS("https://api.idk16.xyz/group/update?id="+groupid+"&updateinfo=true", JSON.stringify({"description": $("#group_description").val(), "approvals": approvals, "emblem":image}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Group Updated") {
			initialize();
		}
		else
		{
			$(messageid).text(alert);
			$(messageid).show();
			
			setTimeout(function() 
			{
				$(messageid).hide();
			}, 3000);
		}
		window.scrollTo({top: 0, behavior: "smooth"});
	});
}

//member management

function changeRank(userid, rank)
{
	postJSONCDS("https://api.idk16.xyz/group/update?id="+groupid+"&userrank=true", JSON.stringify({"userid": userid, "rank": rank}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Group Updated") {
			membersPage(1);
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

function exileUser(userid)
{
	postJSONCDS("https://api.idk16.xyz/group/update?id="+groupid+"&exileuser=true", JSON.stringify({"userid": userid}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Group Updated") {
			membersPage(1);
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

function approveRequest(userid)
{
	postJSONCDS("https://api.idk16.xyz/group/update?id="+groupid+"&approverequest=true", JSON.stringify({"userid": userid}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Group Updated") {
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
function denyRequest(userid)
{
	postJSONCDS("https://api.idk16.xyz/group/update?id="+groupid+"&denyrequest=true", JSON.stringify({"userid": userid}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Group Updated") {
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

//join requests page
function requestsPage(page)
{
	getRequestsPage(page, 4);
}
function getRequestsPage(page, limit)
{
	var html = '<li>';
	html += '<div class="card mb-1">';
	html += '<div class="card-body">';
	html += '<a class="red-a-nounder">';
	html += '<img class="img-fluid" src="{thumbnail}">';
	html += '<p>{username}</p>';
	html += '</a>';
	html += '<div class="row mb-1">';
	html += '<div class="col-sm">';
	html += '<button class="btn btn-success w-100" onclick="approveRequest({userid})">Approve</button>';
	html += '</div>';
	html += '</div>';
	html += '<div class="row">';
	html += '<div class="col-sm">';
	html += '<button class="btn btn-danger w-100" onclick="denyRequest({userid})">Deny</button>';
	html += '</div>';
	html += '</div>';
	html += '</div>';
	html += '</div>';
	html += '</li>';
	
	multiPageHelper("requestsPage", [], "https://api.idk16.xyz/group/joinrequests", "https://api.idk16.xyz/logo", "#join_requests", "#join_requests_buttons", html, page, limit, "", "No join requests", "&id="+groupid);
}

//members page
function membersAvailableRoles(userid)
{
	var html = '<li class="dropdown-custom bootstrap-toggle-dropdown" onclick="changeRank('+userid+',{rank})">{name}</li>';
		
	staticPageHelper("https://api.idk16.xyz/group/roles?id="+groupid, "", "#member"+userid+"dropdown", html, "", 100, "", "Error occurred", "&excluderank=255");
}
function membersPage(page)
{
	getMembersPage(page, 6)
}
function getMembersPage(page, limit)
{
	var html = '<div class="card gaytempfix float-left text-center p-2 m-1 group-user-card" style="width:15.15rem;">';
	html += '<a href="/profile/view?id={userid}"><img class="img-fluid" src="{thumbnail}" style="width:8rem;"></a>';
	html += '<div class="text-center">';
	html += '<a class="red-a-nounder no-overflow">{username}</a>';
	html += '</div>';
	html += '<div class="mb-2 mt-2 dropdown">';
	html += '<button class="dropdown-custom dropdown" type="button" data-toggle="dropdown" onclick="membersAvailableRoles({userid})">{rankname}</button>';
	html += '<ul class="bootstrap-dropdown-parent dropdown-menu" id="member{userid}dropdown">';
	html += '</ul>';
	html += '</div>';
	html += '<button class="btn btn-sm btn-danger" onclick="exileUser({userid})">Exile</button>';
	html += '</div>';
	
	multiPageHelper("membersPage", [], "https://api.idk16.xyz/group/members", "https://api.idk16.xyz/logo", "#members_manage_list", "#members_manage_buttons", html, page, limit, "", "No members", "&id="+groupid+"&excluderank=255");
}

//initialize
function initialize()
{
	getJSONCDS("https://api.idk16.xyz/group/info?id=" + groupid)
	.done(function(jsonData) {
		var data = jsonData[0];
		$("#group_name").val(data.name);
		$("#group_emblem").attr("src", data.emblem);
		$("#group_description").val(data.description);
		$("#manual_join_requests").prop('checked', data.manualJoinRequests);
	});
	requestsPage(1);
	membersPage(1);
	currentRoles();
}
initialize();
</script>

EOT;
pageHandler();
$ph->pageTitle("Group Config");
$ph->body = $body;
$ph->output();