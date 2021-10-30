<?php

$body = <<<EOT
<div class="container">
    <h5>Users</h5>
    <div class="input-group mb-2">
        <input type="text" id="keyword_input" class="form-control" placeholder="Search Users">
        <div class="input-group-append">
             <button class="btn btn-secondary" type="button" id="usersearchdrop" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" disabled>Filter</button>
             <div class="dropdown-menu" aria-labelledby="usersearchdrop">
                 <a class="dropdown-item" href="#">Action</a>
             </div>
            <button onclick="userPage(1, $('#keyword_input').val())" class="btn btn-danger" type="button">Search</button>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-sm">
                    <div id="users-container">

                    </div>
                    <div class="container text-center">
                        <div class="btn-group mt-1" id="page-buttons">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function returnBorder(object)
{
    var borderColor = "";
    if (object.siteStatus == "Online") { //online
        borderColor = 'border-primary';  
    } else if (object.siteStatus == "Offline") { //offline
        //do nothing here
    } else { //ingame
        borderColor = 'border-success';
    }
    return borderColor;
}

function userPage(num, keyword = "")
{
	var html = `
    <div class="row mb-1">
        <div class="col-sm">
            <a href="/profile/view?id={id}" class="black-a-nounder">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-2 text-center">
                                <img class="img-fluid rounded-circle [returnBorder]" title="{siteStatus}" style="border: 1px solid rgba(0,0,0,.125);" width="92" src="{thumbnail}">
                            </div>
                            <div class="col-sm">
                                <div class="row">
                                    <div class="col-sm no-overflow">
                                        <b>{username}</b>
                                    </div>
                                </div>
                                <p class="no-overflow">{blurb}</p>
                            </div>
                            <div class="col-sm-2">
                                <div class="row">
                                    <div class="col-sm text-center">
                                        <p><b>Last Seen:</b> {lastseen}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    `;

	multiPageHelper("userPage", "https://api.alphaland.cc/users/siteusers", "https://api.alphaland.cc/logo", "#users-container", "#page-buttons", html, num, 8, keyword, "No results");
}

$('#keyword_input').keypress(function(event) {
    if (event.keyCode == 13 || event.which == 13) {
		userPage(1, $('#keyword_input').val());
    }
});

userPage(1);
</script>
EOT;

pageHandler();
$ph->body = $body;
$ph->pageTitle("Users");
$ph->output();