local userid, asseturl, url, fileExtension, x, y = ...

print ('Render Player ' .. userid);

settings()["Task Scheduler"].ThreadPoolConfig = Enum.ThreadPoolConfig.PerCore4;
game:GetService("ContentProvider"):SetThreadPool(16)
game:GetService('ScriptContext').ScriptsDisabled=true 
pcall(function() game:GetService('ContentProvider'):SetBaseUrl(url) end)
player = game:GetService('Players'):CreateLocalPlayer(0)
player.CharacterAppearance = asseturl
player:LoadCharacter(false)

-- Raise up the character's arm if they have gear.
if player.Character then
	for _, child in pairs(player.Character:GetChildren()) do
		if child:IsA("Tool") then
			player.Character.Torso["Right Shoulder"].CurrentAngle = math.rad(90)
			break
		end
	end
end

game:GetService('ThumbnailGenerator').GraphicsMode = 4
 
return game:GetService('ThumbnailGenerator'):Click(fileExtension, x, y, true)