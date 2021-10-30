-- Mesh v1.0.2

local assetid, assetUrl, baseUrl, fileExtension, x, y  = ...

print("Render Mesh "..assetid)
local ThumbnailGenerator = game:GetService("ThumbnailGenerator")

pcall(function() game:GetService("ContentProvider"):SetBaseUrl(baseUrl) end)
game:GetService("ScriptContext").ScriptsDisabled = true

local part = Instance.new("Part")
part.Parent = workspace

local specialMesh = Instance.new("SpecialMesh")
specialMesh.MeshId = assetUrl
specialMesh.Parent = part

return ThumbnailGenerator:Click(fileExtension, x, y, --[[hideSky = ]] true, --[[crop = ]] true)