local assetid, asseturl, url, fileExtension, x, y = ...

print('Render Face '.. assetid);

pcall(function() game:GetService('ContentProvider'):SetBaseUrl(url) end)
game:GetService('ThumbnailGenerator').GraphicsMode = 4
game:GetService('ScriptContext').ScriptsDisabled = true
local face = game:GetObjects(asseturl)[1]
return game:GetService('ThumbnailGenerator'):ClickTexture(face.Texture, fileExtension, x, y, true)