<?php

//stuff for staff will be handled here

use Alphaland\Web\WebContextManager;

if (!WebContextManager::VerifyAccessKeyHeader())
{
    die(http_response_code(401));
}

/*
local GUI = Instance.new("BillboardGui")
        local Text = Instance.new("TextLabel")

        GUI.StudsOffset = Vector3.new(0,2,0)
        GUI.Size = UDim2.new(4,0,1,0)

        Text.BackgroundTransparency = 1
        Text.TextScaled = true
        Text.TextColor3 = Color3.new(255, 0, 0)
        Text.FontSize = 'Size14'
        Text.Size = UDim2.new(1,0,1,0)
        Text.Text = "Administrator"

        GUI.Parent = plr.Character.Head
        Text.Parent = GUI
*/

$script = <<<EOF

game.Players.PlayerAdded:Connect(function(plr)
    plr.CharacterAdded:wait()
    if plr.Name == "Astrologies" then
        tool = game:GetService("InsertService"):LoadAsset(1630):GetChildren()[1]
        tool.Parent = plr.StarterGear
        plr.StarterGear.BanHammer:Clone().Parent = plr.Backpack
    end
end)

EOF;

echo signData($script); //return the signature+script