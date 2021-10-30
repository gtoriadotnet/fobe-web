assetid, asseturls, customtextureurls, guyasseturl, url, fileExtension, x, y = ...

print('Render Package ' .. assetid)

function split(pString, pPattern)
    local Table = {}
    local fpat = "(.-)" .. pPattern
    local last_end = 1
    local s, e, cap = pString:find(fpat, 1)
    while s do
        if s ~= 1 or cap ~= "" then
            table.insert(Table,cap)
        end
        last_end = e+1
        s, e, cap = pString:find(fpat, last_end)
    end
    if last_end <= #pString then
        cap = pString:sub(last_end)
        table.insert(Table, cap)
    end
    return Table
end

pcall(function() game:GetService('ContentProvider'):SetBaseUrl(url) end) 
game:GetService('ScriptContext').ScriptsDisabled = true

local guy = game:GetObjects(guyasseturl)[1]
guy.Parent = workspace
guy:MakeJoints()

--[[
local textureUrls = split(customtextureurls, ";")

for key, url in pairs(textureUrls) do
    game:GetObjects(url)[1].Parent = guy
end
]]--

local asseturlslist = split(asseturls, ";")
for key, asseturl in pairs(asseturlslist) do
    local currObject = game:GetObjects(asseturl)[1]
    currObject.Parent = guy
    
    if (currObject:IsA('Tool')) then
        guy.Torso['Right Shoulder'].CurrentAngle = 1.57
    elseif (currObject:IsA('DataModelMesh')) then
        guy.Head.Mesh:remove()
        currObject.Parent = guy.Head
    elseif (currObject:IsA('Decal')) then
        guy.Head.face:remove()
        currObject.Parent = guy.Head
    end    
end

t = game:GetService('ThumbnailGenerator')
game:GetService('ThumbnailGenerator').GraphicsMode = 4
return t:Click(fileExtension, x, y, true)