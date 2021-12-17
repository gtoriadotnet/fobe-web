<?php

use Alphaland\Common\Signing;

$userid = $_GET['UserID'];
$isplaysolo = $_GET['IsPlaySolo'];
$placeid = $_GET['PlaceID'];
$universeid = $_GET['universeId'];

$username = getUsername($userid);
$script = "";

if ($isplaysolo == 1)
{
	$script = <<<EOT

	-- Prepended to Edit.lua and Visit.lua and Studio.lua --

	pcall(function() game:SetPlaceID(${placeid}) end)
	pcall(function() game:SetUniverseId(0) end)

	visit = game:GetService("Visit")

	local message = Instance.new("Message")
	message.Parent = workspace
	message.archivable = false

	game:GetService("ContentProvider"):SetThreadPool(16)

	settings().Diagnostics:LegacyScriptMode()

	--game:GetService("InsertService"):SetBaseSetsUrl("http://assetgame.alphaland.cc/Game/Tools/InsertAsset.ashx?nsets=10&type=base")
	--game:GetService("InsertService"):SetUserSetsUrl("http://assetgame.alphaland.cc/Game/Tools/InsertAsset.ashx?nsets=20&type=user&userid=%d")
	--game:GetService("InsertService"):SetCollectionUrl("http://assetgame.alphaland.cc/Game/Tools/InsertAsset.ashx?sid=%d")
	game:GetService("InsertService"):SetAssetUrl("http://assetgame.alphaland.cc/Asset/?id=%d")
	game:GetService("InsertService"):SetAssetVersionUrl("http://assetgame.alphaland.cc/Asset/?assetversionid=%d")

	--pcall(function() game:GetService("SocialService"):SetFriendUrl("http://assetgame.alphaland.cc/Game/LuaWebService/HandleSocialRequest.ashx?method=IsFriendsWith&playerid=%d&userid=%d") end)
	--pcall(function() game:GetService("SocialService"):SetBestFriendUrl("http://assetgame.alphaland.cc/Game/LuaWebService/HandleSocialRequest.ashx?method=IsBestFriendsWith&playerid=%d&userid=%d") end)
	--pcall(function() game:GetService("SocialService"):SetGroupUrl("http://assetgame.alphaland.cc/Game/LuaWebService/HandleSocialRequest.ashx?method=IsInGroup&playerid=%d&groupid=%d") end)
	--pcall(function() game:GetService("SocialService"):SetGroupRankUrl("http://assetgame.alphaland.cc/Game/LuaWebService/HandleSocialRequest.ashx?method=GetGroupRank&playerid=%d&groupid=%d") end)
	--pcall(function() game:GetService("SocialService"):SetGroupRoleUrl("http://assetgame.alphaland.cc/Game/LuaWebService/HandleSocialRequest.ashx?method=GetGroupRole&playerid=%d&groupid=%d") end)
	--pcall(function() game:GetService("GamePassService"):SetPlayerHasPassUrl("http://assetgame.alphaland.cc/Game/GamePass/GamePassHandler.ashx?Action=HasPass&UserID=%d&PassID=%d") end)
	pcall(function() game:GetService("MarketplaceService"):SetProductInfoUrl("https://api.alphaland.cc/marketplace/productinfo?assetId=%d") end)
	--pcall(function() game:GetService("MarketplaceService"):SetDevProductInfoUrl("https://api.alphaland.cc/marketplace/productDetails?productId=%d") end)
	--pcall(function() game:GetService("MarketplaceService"):SetPlayerOwnsAssetUrl("https://api.alphaland.cc/ownership/hasasset?userId=%d&assetId=%d") end)
	pcall(function() game:SetCreatorID(0, Enum.CreatorType.User) end)

	pcall(function() game:SetScreenshotInfo("") end)
	pcall(function() game:SetVideoInfo("") end)

	pcall(function() settings().Rendering.EnableFRM = true end)
	pcall(function() settings()["Task Scheduler"].PriorityMethod = Enum.PriorityMethod.AccumulatedError end)

	game:GetService("ChangeHistoryService"):SetEnabled(false)
	--pcall(function() game:GetService("Players"):SetBuildUserPermissionsUrl("http://www.alphaland.cc/Game/BuildActionPermissionCheck.ashx?assetId=0&userId=%d&isSolo=true") end)

	workspace:SetPhysicsThrottleEnabled(true)

	local screenGui = game:GetService("CoreGui"):FindFirstChild("RobloxGui")

	function doVisit()
		message.Text = "Loading Game"
		pcall(function() visit:SetUploadUrl("") end)

		message.Text = "Running"
		game:GetService("RunService"):Run()
		message.Text = "Creating Player"
		player = game:GetService("Players"):CreateLocalPlayer(${userid})
		player.CharacterAppearance = "https://api.alphaland.cc/users/avatar-accoutrements?userId=${userid}"
		local propExists, canAutoLoadChar = false
		propExists = pcall(function()  canAutoLoadChar = game.Players.CharacterAutoLoads end)

		if (propExists and canAutoLoadChar) or (not propExists) then
			player:LoadCharacter()
		end
		
		message.Text = "Setting GUI"
		player:SetSuperSafeChat(false)
		pcall(function() player:SetUnder13(false) end)
		pcall(function() player:SetMembershipType(None) end)
		pcall(function() player:SetAccountAge(0) end)
	end

	success, err = pcall(doVisit)

	if success then
		message.Parent = nil
	else
		print(err)
		wait(5)
		message.Text = "Error on visit: " .. err
	end

EOT;
}

echo Signing::SignData($script);