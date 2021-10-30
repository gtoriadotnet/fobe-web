<?php

$body = <<<EOT
<div class="container">
	<div id = "error_alert" class="alert alert-danger" role="alert" style="display:none";></div>
	<div>
		<h5>Create New Group</h5>
		<h6>This will cost 20 Alphabux</h6>
	</div>
	<div class="card">
		<div class="card-body">
		<div class="form-group">
			<label>Group Name (required)</label>
			<input type="text" class="form-control" id="group_name" autocomplete="off" placeholder="Enter Group Name">
			<small class="form-text text-muted">Group Names are limited to 50 characters.</small>
		</div>
		<div class="form-group">
			<label>Group Description (required)</label>
			<textarea class="form-control" style="min-height:8rem;max-height:8rem;"id="group_description" autocomplete="off" placeholder="Enter Group Description"></textarea>
			<small class="form-text text-muted">Group Descriptions are limited to 1024 characters.</small>
		</div>
		<div class="form-group">
			<label>Group Icon (required)</label>
			<div class="text-center mb-3">
				<img id="emblemPreview" src="https://api.alphaland.cc/logo" width="150" height="150" class="img-fluid">
			</div>
			<div class="custom-file">
				<input type="file" class="custom-file-input" name="emblemPhoto" value="" id="emblemPhoto">
				<label class="custom-file-label" for="emblemPhoto">Group Icon</label>
				<small class="form-text text-muted">Group Icons will be resized to 150x150</small>
			</div>
		</div>
		<div class="form-group">
			<label>Settings</label>
			<div class="form-check">
				<input class="form-check-input settings" type="radio" name="radios" id="noapproval" value="option1" checked>
				<label class="form-check-label" for="noapproval">Anyone Can Join</label>
			</div>
			<div class="form-check">
				<input class="form-check-input settings" type="radio" name="radios" id="manualapproval" value="option2">
				<label class="form-check-label" for="manualapproval">Manual Approval</label>
			</div>
		</div>
		<div>
			<button class="btn btn-danger w-100" onclick="createGroup()">Create</button>
			</div>
		</div>
	</div>
</div>
<script>
var image = "";
var groupname = "";
var groupdescription = "";
var approvals = false;

function readEmblem(input) 
{
  if (input.files && input.files[0])
  {
    var reader = new FileReader();
    reader.onload = function(e) {
    	image = e.target.result;
     	$('#emblemPreview').attr('src', image);
    }
    reader.readAsDataURL(input.files[0]);
  }
}

$("#emblemPhoto").change(function() {
	readEmblem(this);
});

$('input.example').on('change', function() {
    $('input.example').not(this).prop('checked', false);  
});

function createGroup()
{
	groupname = $("#group_name").val();
	groupdescription = $("#group_description").val();
	
	if ($('#manualapproval').is(":checked"))
	{
		approvals = true;
	}
	else
	{
		approvals = false;
	}
	
	postJSONCDS("https://api.alphaland.cc/group/create", JSON.stringify({"name": groupname, "description": groupdescription, "approvals": approvals, "emblem":image}))
	.done(function(object) {
		var alert = object.alert;
		var messageid = "#error_alert";
		if (alert == "Group Created") 
		{
			window.location='/groups';
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
</script>
EOT;

pageHandler();
$ph->pageTitle("Create Group");
$ph->body = $body;
$ph->output();