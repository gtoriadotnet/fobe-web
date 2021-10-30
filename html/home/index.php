<?php

$body = <<<EOT
<div class="container">
    <div id = "success_alert" class="alert alert-success" role="alert" style="display:none;"></div>
    <div id = "error_alert" class="alert alert-danger" role="alert" style="display:none;"></div>
    <div class="row">
        <div class="col-sm-3 mb-3">
            <h5 id="home_username"></h5>
            <div class="card">
                <div class="card-body">
                    <img class="img-fluid rounded" src="https://api.alphaland.cc/users/thumbnail?headshot=true">
                </div>
            </div>
        </div>
        <div class="col-sm mb-3">
        <h5>Recently Played:</h5>
            <div class="card">
                <div class="card-body">
                    <div class="game-container-index">
                        <ul id="recents-container">
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-3" style="display:none;"> <-- TODO --/>
            <div class="card">
                <div class="card-body">
                    <h6>Online Friends (0)</h6>
                    <div class="row">
                        <div class="col-sm text-center">
                            <a href="#" class="red-a-nounder">
                                <img class="img-fluid rounded-circle border" width="86" src="https://api.alphaland.cc/users/thumbnail?headshot=true">
                                <p class="no-overflow">UsernameHerePls</p>
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm text-center">
                            <a href="#" class="red-a-nounder">
                                <img class="img-fluid rounded-circle border" width="86" src="https://api.alphaland.cc/users/thumbnail?headshot=true">
                                <p class="no-overflow">UsernameHerePls</p>
                            </a>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm">
                            <a href="#" class="red-a float-right">More...</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-3 marg-bot-15">
			<div class="card marg-auto" style="height:18rem;">
				<div class="card-body">
					<h5>News</h5>
					<div class="container w-100">
						<a href="#" class="red-a">News coming soon!</a>
					</div>
				</div>
			</div>
		</div>
        <div class="col-sm">
            <div class="card">
                <div class="card-body">
                    <h6>Feed:</h6>
                    <div class="row mb-3">
                        <div class="col-sm">
                            <div class="input-group">
                                <input class="form-control" placeholder="New Shout" id="new_shout_input" autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-danger" onclick="postShout()">Submit</button>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm" id="shouts-container">
                            
                        </div>
                    </div>
                    <div class="container mt-2 mb-2 text-center">
                        <div id="page-buttons" class="btn-group" role="group" aria-label="First group">

                        </div>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

function postShout()
{
	postJSONCDS("https://api.alphaland.cc/user/feed/post", JSON.stringify({"shout":$('#new_shout_input').val()}))
	.done(function(object) {
		var alert = object.alert;
		if (alert == "Shout Posted") 
		{
            $('#new_shout_input').val('')
            $("#success_alert").text(alert);
			$("#success_alert").show();
            window.scrollTo({top: 0, behavior: "smooth"});
            setTimeout(function() 
			{
				$("#success_alert").hide();
			}, 3000);
        }
        else
        {
			$('#new_shout_input').val('')

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

function shoutPage(num)
{
    var html = `
    <div class="card mb-2">
        <div class="card-body">
            <div class="row">
                <div class="col-sm">
                    <p>Shout from <a href="/profile/view?id={userid}" class="red-a"><b>{username}</b></a> : <b>{date}</b></p>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-2">
                    <a href="/profile/view?id={userid}"> 
                        <img class="img-fluid rounded-circle" width="80" src="{thumbnail}">
                    </a>
                </div>
                <div class="col-sm overflow-hidden">
                    <p>"{shout}"</p>
                </div>
            </div>
        </div>
    </div>
    `;

	multiPageHelper("shoutPage", "https://api.alphaland.cc/user/feed/", "https://api.alphaland.cc/logo", "#shouts-container", "#page-buttons", html, num, 10, "", "No shouts");
}

function getRecents()
{
	var html = `
    <li>
        <div class="game-card-index">
            <a class="a-nounderline" href=/games/view?id={id}>
                <img src="{thumbnail}">
                <span>
                    <p>{name}</p>
                    <p>By: {creator}</p>
                    <div class="w-100 text-center mb-1">
                        <div class="inline-flex">
                            <p><i class="fas fa-users"></i> {playerCount}</p>
                        </div>
                        <div class="inline-flex">
                            <p><i class="fas fa-door-open"></i> {visits}</p>
                        </div>
                    </div>
                </span>
            </a>
        </div>
	</li>
    `;
		
	staticPageHelper("https://api.alphaland.cc/user/games/recents", "https://api.alphaland.cc/logo", "#recents-container", html, 1, 4, "", "No recently played games");
}

function setUsername()
{
    getJSONCDS('https://api.alphaland.cc/users/profile/info')
    .done(function(jsonData) 
    {
        $('#home_username').html('Hello, '+jsonData[0].username)
    });
}

setUsername()
getRecents();
shoutPage(1);
</script>
EOT;

pageHandler();
$ph->pageTitle("Home");
$ph->body = $body;
$ph->output();