local assetid, asseturl, url, fileExtension, x, y = ...

print('Render Hat ' .. assetid)

pcall(function() game:GetService('ContentProvider'):SetBaseUrl(url) end)
game:GetService('ThumbnailGenerator').GraphicsMode = 4
game:GetService('ScriptContext').ScriptsDisabled = true
game:GetObjects(asseturl)[1].Parent = workspace
t = game:GetService('ThumbnailGenerator')
return t:Click(fileExtension, x, y, true, true)
