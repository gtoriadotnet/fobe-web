local assetid, asseturl, guyasseturl, url, fileExtension, x, y = ...

print('Render Head ' .. assetid)

pcall(function() game:GetService('ContentProvider'):SetBaseUrl(url) end)
game:GetService('ThumbnailGenerator').GraphicsMode = 4
game:GetService('ScriptContext').ScriptsDisabled = true
local guy = game:GetObjects(guyasseturl)[1]
guy.Parent = workspace

local head = game:GetObjects(asseturl)[1]
head.Parent = guy.Head

c = Instance.new('Decal')
c.Name = "Face"
c.Texture = "rbxasset://textures/face.png"
c.Parent = guy.Head

guy.Parent = workspace
guy.Head.Parent = workspace
guy:remove()

return game:GetService('ThumbnailGenerator'):Click(fileExtension, x, y, true)