--[[
  Finobe 2021 Gameserver Script
  Slightly modified from ROBLOX https://web.archive.org/web/20160714155618/http://roblox.com/Game/GameServer.ashx
--]]

local placeId, port, domain, creatorid, isPersonalServer = ...
local baseurl = "https://www." .. domain
local assetgame = "https://assetgame." .. domain
local api = "https://api." .. domain

------------------- UTILITY FUNCTIONS --------------------------

function waitForChild(parent, childName)
	while true do
		local child = parent:findFirstChild(childName)
		if child then
			return child
		end
		parent.ChildAdded:wait()
	end
end

function sendKillJobSignal()
	game:HttpGet(baseurl .. "/Game/KillServer?jobid=" .. game.JobId) --OCCASIONALLY fails
end

function sendIsAliveSignal()
	game:HttpGet(baseurl .. "/Game/ServerPing?PlaceID=" .. placeId .. "&JobID=" .. game.JobId)
end

function isAlive()
	while true do
		sendIsAliveSignal()
		wait(30) --ping every 30 seconds (subject to change?)
	end
end

-----------------------------------END UTILITY FUNCTIONS -------------------------

-----------------------------------"CUSTOM" SHARED CODE----------------------------------

pcall(function() settings().Network.UseInstancePacketCache = true end)
pcall(function() settings().Network.UsePhysicsPacketCache = true end)
pcall(function() settings()["Task Scheduler"].PriorityMethod = Enum.PriorityMethod.AccumulatedError end)

settings().Network.PhysicsSend = Enum.PhysicsSendMethod.TopNErrors
settings().Network.ExperimentalPhysicsEnabled = true
settings().Network.WaitingForCharacterLogRate = 100
settings().Diagnostics.LuaRamLimit = 0
pcall(function() settings().Diagnostics:LegacyScriptMode() end)

-----------------------------------START GAME SHARED SCRIPT------------------------------

-- initialize --
local scriptContext = game:GetService('ScriptContext')
pcall(function() scriptContext:AddStarterScript(37801172) end)
scriptContext.ScriptsDisabled = true

-- general datamodel settings --
game:SetPlaceID(placeId, false)
game:SetCreatorID(creatorid, Enum.CreatorType.User)
game:GetService("ChangeHistoryService"):SetEnabled(false)

-- establish this peer as the Server --
local ns = game:GetService("NetworkServer")

-- setup base services --
if baseurl~=nil then
	-- players service --
	pcall(function() game:GetService("Players"):SetAbuseReportUrl(api .. "/moderation/AbuseReport/InGameChatHandler") end) --TODO: Implement
	pcall(function() game:GetService("Players"):SetChatFilterUrl(baseurl .. "/Game/ChatFilter.ashx") end) --not even used, just enables filter (lol)

	-- scriptinformationprovider service --
	pcall(function() game:GetService("ScriptInformationProvider"):SetAssetUrl(baseurl .. "/Asset/") end)
	
	-- contentprovider service --
	pcall(function() game:GetService("ContentProvider"):SetBaseUrl(baseurl .. "/") end)
	
	-- badge service --
	pcall(function() game:GetService("BadgeService"):SetPlaceId(placeId) end)
	pcall(function() game:GetService("BadgeService"):SetAwardBadgeUrl(assetgame .. "/Game/Badge/AwardBadge?UserID=%d&BadgeID=%d&PlaceID=%d") end)
	pcall(function() game:GetService("BadgeService"):SetHasBadgeUrl(assetgame .. "/Game/Badge/HasBadge?UserID=%d&BadgeID=%d") end)
	pcall(function() game:GetService("BadgeService"):SetIsBadgeDisabledUrl(assetgame .. "/Game/Badge/IsBadgeDisabled?BadgeID=%d&PlaceID=%d") end)
	pcall(function() game:GetService("BadgeService"):SetIsBadgeLegalUrl("") end)

	-- social service --
	pcall(function() game:GetService("SocialService"):SetFriendUrl(assetgame .. "/Game/LuaWebService/HandleSocialRequest.ashx?method=IsFriendsWith&playerid=%d&userid=%d") end)
	pcall(function() game:GetService("SocialService"):SetBestFriendUrl(assetgame .. "/Game/LuaWebService/HandleSocialRequest.ashx?method=IsBestFriendsWith&playerid=%d&userid=%d") end)
	pcall(function() game:GetService("SocialService"):SetGroupUrl(assetgame .. "/Game/LuaWebService/HandleSocialRequest.ashx?method=IsInGroup&playerid=%d&groupid=%d") end)
	pcall(function() game:GetService("SocialService"):SetGroupRankUrl(assetgame .. "/Game/LuaWebService/HandleSocialRequest.ashx?method=GetGroupRank&playerid=%d&groupid=%d") end)
	pcall(function() game:GetService("SocialService"):SetGroupRoleUrl(assetgane .. "/Game/LuaWebService/HandleSocialRequest.ashx?method=GetGroupRole&playerid=%d&groupid=%d") end)

	-- gamepass service --
	pcall(function() game:GetService("GamePassService"):SetPlayerHasPassUrl(assetgame .. "/Game/GamePass/GamePassHandler.ashx?Action=HasPass&UserID=%d&PassID=%d") end)

	-- friends service --
	pcall(function() game:GetService("FriendService"):SetMakeFriendUrl(assetgame .. "/Game/CreateFriend?firstUserId=%d&secondUserId=%d") end)
	pcall(function() game:GetService("FriendService"):SetBreakFriendUrl(assetgame .. "/Game/BreakFriend?firstUserId=%d&secondUserId=%d") end)
	pcall(function() game:GetService("FriendService"):SetGetFriendsUrl(assetgame .. "/Game/AreFriends?userId=%d") end)
	
	-- insert service --
	pcall(function() game:GetService("InsertService"):SetBaseSetsUrl(baseurl .. "/Game/Tools/InsertAsset.ashx?nsets=10&type=base") end)
	pcall(function() game:GetService("InsertService"):SetUserSetsUrl(baseurl .. "/Game/Tools/InsertAsset.ashx?nsets=20&type=user&userid=%d") end)
	pcall(function() game:GetService("InsertService"):SetCollectionUrl(baseurl .. "/Game/Tools/InsertAsset.ashx?sid=%d") end)
	pcall(function() game:GetService("InsertService"):SetAssetUrl(baseurl .. "/Asset/?id=%d") end)
	pcall(function() game:GetService("InsertService"):SetAssetVersionUrl(baseurl .. "/Asset/?assetversionid=%d") end)

	-- marketplace service --
	pcall(function() game:GetService("MarketplaceService"):SetProductInfoUrl(api .. "/marketplace/productinfo?assetId=%d") end)
	pcall(function() game:GetService("MarketplaceService"):SetDevProductInfoUrl(api .. "/marketplace/productDetails?productId=%d") end)
	pcall(function() game:GetService("MarketplaceService"):SetPlayerOwnsAssetUrl(api .. "/ownership/hasasset?userId=%d&assetId=%d") end)
end

-- Set player authentication required --
pcall(function() game:GetService("NetworkServer"):SetIsPlayerAuthenticationRequired(true) end)

-- Monitor players joining --
game:GetService("Players").PlayerAdded:connect(function(player)
	print("Player " .. player.userId .. " added")

	local didTeleportIn = "False"
	if player.TeleportedIn then didTeleportIn = "True" end

	if #game:GetService("Players"):GetPlayers() == 1 then -- so the server renews if the server has been inactive until this person joined
		sendIsAliveSignal()
	end

	game:HttpGet(baseurl .. "/Game/ClientPresence?action=connect&PlaceID=" .. placeId .. "&UserID=" .. player.userId .. "&JobID=" .. game.JobId) 
end)

-- Monitor players leaving --
game:GetService("Players").PlayerRemoving:connect(function(player)
	print("Player " .. player.userId .. " leaving")	

	local isTeleportingOut = "False"
	if player.Teleported then isTeleportingOut = "True" end

	game:HttpGet(baseurl .. "/Game/ClientPresence?action=disconnect&PlaceID=" .. placeId .. "&UserID=" .. player.userId .. "&JobID=" .. game.JobId) 
end)

if placeId~=nil and baseurl~=nil then
	-- yield so that file load happens in the heartbeat thread
	wait()
	
	-- start isalive ping
	spawn(isAlive)
	
	-- load the game
	game:Load(baseurl .. "/asset/?id=" .. placeId)
end

--[[Old Animations]]--
--[[
if game:GetService("StarterPlayer"):FindFirstChild("StarterCharacterScripts") ~= nil and game:GetService("StarterPlayer").StarterCharacterScripts:FindFirstChild("Animate") == nil then
	pcall(function()game:GetObjects("rbxasset://fonts/humanoidAnimateLocal.rbxm")[1].Parent = game:GetService("StarterPlayer").StarterCharacterScripts end)
end
]]--

-- Now start the connection
ns:Start(port) 

if isPersonalServer then
	game:GetService("ScriptContext"):AddStarterScript(124885177) -- Personal Build Server Script
end

-- Enable scripts --
scriptContext:SetTimeout(10)
scriptContext.ScriptsDisabled = false

------------------------------END START GAME SHARED SCRIPT--------------------------

-- StartGame -- 
game:GetService("RunService"):Run()

-- Register Job --
game:HttpGet(baseurl .. "/Game/RegisterServer?jobId=" .. game.JobId)

-- Remove server OnClose --
game.OnClose = function() sendKillJobSignal() end -- register game as closed when datamodel shutdowns