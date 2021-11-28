local assetid, asseturl, url, fileExtension, x, y = ...

print('Render Place ' .. assetid)

game:GetService('ThumbnailGenerator').GraphicsMode = 4 
pcall(function() game:GetService('ContentProvider'):SetBaseUrl(url) end)
game:GetService("ScriptContext").ScriptsDisabled = true
game:GetService("StarterGui").ShowDevelopmentGui = false
game:Load(asseturl) 
game:GetService("ScriptContext").ScriptsDisabled = true
game:GetService("StarterGui").ShowDevelopmentGui = false
return game:GetService('ThumbnailGenerator'):Click(fileExtension, x, y, false)