local assetid, asseturl, url, fileExtension, x, y = ...

game:GetService('ThumbnailGenerator').GraphicsMode = 4
pcall(function() game:GetService('ContentProvider'):SetBaseUrl(url) end)

function tryorelse(tryfunc, failfunc)
    local r
    if(pcall(function () r = tryfunc() end)) then
        return r
    else
        return failfunc()
    end
 end

 pcall(function() game.Workspace.Camera:Remove() end) -- hack to make sure thumbnailcamera will work (probably no longer needed with batch jobs, but we are being safe)
 
 t = game:GetService('ThumbnailGenerator')
 
 game:GetService('ScriptContext').ScriptsDisabled = true
 
 for _,i in ipairs(game:GetObjects(asseturl)) do
 	if i.className=='Sky' then
	 print("Sky Render " .. assetid)
        return tryorelse(
            function() return t:ClickTexture(i.SkyboxFt, fileExtension, x, y) end,
            function() return t:Click(fileExtension, x, y, true) end)
 	elseif i.className=='SpecialMesh' then
		print("SpecialMesh Render " .. assetid)
 		part = Instance:new('Part')
 		part.Parent = workspace
 		i.Parent = part
		return t:Click(fileExtension, x, y, true)
 	else
	 	print("Generic Render " .. assetid)
 		i.Parent = workspace
		return t:Click(fileExtension, x, y, true)
 	end
 end