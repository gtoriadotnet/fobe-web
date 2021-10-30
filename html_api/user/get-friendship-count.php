<?php

//re-created from: https://api.roblox.com/user/get-friendship-count?userId=29


header('Content-Type: application/json');

$userid = $_GET['userId'];

if ($userid) //user id parameter exists
{
	if (userExists($userid))
	{
		$friends = getFriends($userid);
		$friends_count = 0;
		foreach($friends as $friend) 
		{
			$friends_count = $friends_count + 1;
		}

		echo(json_encode(array(
				"success" => true,
				"message" => "Success",
				"count" => $friends_count,
			), JSON_UNESCAPED_SLASHES));
	}
	else
	{
		http_response_code(400); //user doesnt exist
	}
}
else //no user id provided, use the local users session (if exists)
{
	$friends = getFriends($GLOBALS['user']->id);
	$friends_count = 0;
	foreach($friends as $friend) 
	{
		$friends_count = $friends_count + 1;
	}
		
	echo(json_encode(array(
		"success" => true,
		"message" => "Success",
		"count" => $friends_count,
	), JSON_UNESCAPED_SLASHES));
}