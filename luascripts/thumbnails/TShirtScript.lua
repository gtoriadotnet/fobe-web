local assetid, asseturl, guyasseturl, url, fileExtension, x, y = ...

print('Render TShirt ' .. assetid)

pcall(function() game:GetService('ContentProvider'):SetBaseUrl(url) end)
game:GetService('ThumbnailGenerator').GraphicsMode = 4
game:GetService('ScriptContext').ScriptsDisabled = true
local guy = game:GetObjects(guyasseturl)[1]
guy.Parent = workspace

c = Instance.new('Decal')
c.Name = "Face"
c.Texture = "rbxasset://textures/face.png"
c.Parent = guy.Head

c = Instance.new('ShirtGraphic')
c.Graphic = game:GetObjects(asseturl)[1].Graphic
c.Parent = guy

return game:GetService('ThumbnailGenerator'):Click(fileExtension, x, y, true)