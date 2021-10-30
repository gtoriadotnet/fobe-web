local assetid, asseturl, url, fileExtension, x, y = ...

print('Render Place ' .. assetid)

game:GetService('ThumbnailGenerator').GraphicsMode = 4 
pcall(function() game:GetService('ContentProvider'):SetBaseUrl(url) end)
game:GetService('ScriptContext').ScriptsDisabled=true 
game:Load(asseturl) 
return game:GetService('ThumbnailGenerator'):Click(fileExtension, x, y, false)