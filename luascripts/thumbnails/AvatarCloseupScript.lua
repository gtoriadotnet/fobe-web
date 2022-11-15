--[[
	Finobe 2021 Closeup

	OG Params:
	local quadratic = false
	local baseHatZoom = 30
	local maxHatZoom = 100
	local cameraOffsetX = 0
	local cameraOffsetY = 0
	local maxDimension = 0
]]


local userid, url, asseturl, fileExtension, x, y, angleRight, angleLeft = ...

local quadratic = false
local OnlyCheckHeadAccessoryInHeadShot = false
local baseHatZoom = 0
local maxHatZoom = 90
local cameraOffsetX = 0
local cameraOffsetY = -0.1

print ('Render Player Closeup ' .. userid);

settings()["Task Scheduler"].ThreadPoolConfig = Enum.ThreadPoolConfig.PerCore4;
game:GetService("ContentProvider"):SetThreadPool(16)
game:GetService('ScriptContext').ScriptsDisabled=true 
pcall(function() game:GetService('ContentProvider'):SetBaseUrl(url) end)
player = game:GetService('Players'):CreateLocalPlayer(0)
player.CharacterAppearance = asseturl
player:LoadCharacter(false)

local headAttachments = {}
if OnlyCheckHeadAccessoryInHeadShot then
    if player.Character:FindFirstChild("Head") then
	    for _,child in pairs(player.Character.Head:GetChildren()) do
		    if child:IsA("Attachment") then
			    headAttachments[child.Name] = true
		    end
	    end
    end
end

--local maxDimension = 0
local maxDimension = 2.3

function FindFirstChildWhichIsA(Inst, Name)
	local Found = nil
	for _, Child in pairs(Inst:GetChildren()) do
		if(Child:isA(Name)) then
			Found = Child
			break
		end
	end
	
	return Found
end

if player.Character then
	-- Remove gear
	for _, child in pairs(player.Character:GetChildren()) do
		if child:IsA("Tool") then
			child:Destroy()
		elseif child:IsA("Accoutrement") then
            local handle = child:FindFirstChild("Handle")
			if handle then
				local attachment = FindFirstChildWhichIsA(handle, "Attachment")
                --legacy hat does not have attachment in it and should be considered when zoom out camera
				if not OnlyCheckHeadAccessoryInHeadShot or not attachment or headAttachments[attachment.Name] then
					local size = handle.Size / 2 + handle.Position - player.Character.Head.Position
					local xy = Vector2.new(size.x, size.y)
					if xy.magnitude > maxDimension then
						maxDimension = xy.magnitude
					end
				end
			end
		end
	end

	-- Setup Camera
	local maxHatOffset = 0.5 -- Maximum amount to move camera upward to accomodate large hats
	maxDimension = math.min(1, maxDimension / 3) -- Confine maxdimension to specific bounds

	if quadratic then
		maxDimension = maxDimension * maxDimension -- Zoom out on quadratic interpolation
	end

	local viewOffset = player.Character.Head.CFrame * CFrame.new(cameraOffsetX, cameraOffsetY + maxHatOffset * maxDimension, 0.1) -- View vector offset from head

	local yAngle = 0 --angle straight by default
	if angleLeft then
		yAngle = math.pi / 16 --angle avatar left
	elseif angleRight then
		yAngle = -math.pi / 16 --angle avatar right
	end

	local positionOffset = player.Character.Head.CFrame + (CFrame.Angles(0, yAngle, 0).lookVector.unit * 3) -- Position vector offset from head

	local camera = Instance.new("Camera", player.Character)
	camera.Name = "ThumbnailCamera"
	camera.CameraType = Enum.CameraType.Scriptable
	camera.CoordinateFrame = CFrame.new(positionOffset.p, viewOffset.p)
	camera.FieldOfView = baseHatZoom + (maxHatZoom - baseHatZoom) * maxDimension

	--[[
	if angleCenter then
		-- New lighting setup: we want a light slightly in front of, to the right, and above the character.
		-- Adding Part to be anchor of light. For 3D thumbnails (like full avatar) we should be careful about adding parts as this can affect the bounds.
		local part = Instance.new("Part")
		part.Parent = game.Workspace
		part.Anchored = true
		part.Transparency = 1

		local light = Instance.new("PointLight")
		light.Color = Color3.new(255/255, 255/255, 255/255)
		light.Brightness = 3
		light.Range = 10
		light.Parent = part
		light.Shadows = true

		part.Position = Vector3.new(-5,110,-5)
	end
	]]--
end

game:GetService('ThumbnailGenerator').GraphicsMode = 4
 
return game:GetService('ThumbnailGenerator'):Click(fileExtension, x, y, true)