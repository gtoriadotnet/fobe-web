local assetid, asseturl, guyasseturl, url, fileExtension, x, y = ...

print('Render Pants ' .. assetid)

pcall(function() game:GetService('ContentProvider'):SetBaseUrl(url) end)
game:GetService('ScriptContext').ScriptsDisabled = true
local guy = game:GetObjects(guyasseturl)[1]
guy.Parent = workspace

c = Instance.new('Decal')
c.Name = "Face"
c.Texture = "rbxasset://textures/face.png"
c.Parent = guy.Head

c = Instance.new('Pants')
c.PantsTemplate = game:GetObjects(asseturl)[1].PantsTemplate
c.Parent = guy

t = game:GetService('ThumbnailGenerator')
game:GetService('ThumbnailGenerator').GraphicsMode = 4
return t:Click(fileExtension, x, y, true)