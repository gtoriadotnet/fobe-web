<?php

/*
    Fobe 2021
*/

namespace Fobe\Assets {
    class AssetType
    {
        public function IsPurchasable($id)
        {
            switch ($id) {
            }
        }

        public function ConvertToString(int $assetTypeId): string
        {
            switch ($assetTypeId) {
                case 0:
                    return "Product";
                case 1:
                    return "Image";
                case 2:
                    return "T-Shirt";
                case 3:
                    return "Audio";
                case 4:
                    return "Mesh";
                case 5:
                    return "Lua";
                case 6:
                    return "HTML";
                case 7:
                    return "Text";
                case 8:
                    return "Hat";
                case 9:
                    return "Place";
                case 10:
                    return "Model";
                case 11:
                    return "Shirt";
                case 12:
                    return "Pants";
                case 13:
                    return "Decal";
                case 16:
                    return "Avatar";
                case 17:
                    return "Head";
                case 18:
                    return "Face";
                case 19:
                    return "Gear";
                case 21:
                    return "Badge";
                case 22:
                    return "Group Emblem";
                case 24:
                    return "Animation";
                case 25:
                    return "Arms";
                case 26:
                    return "Legs";
                case 27:
                    return "Torso";
                case 28:
                    return "Right Arm";
                case 29:
                    return "Left Arm";
                case 30:
                    return "Left Leg";
                case 31:
                    return "Right Leg";
                case 32:
                    return "Package";
                case 33:
                    return "YouTube Video";
                case 34:
                    return "Game Pass";
                case 35:
                    return "App";
                case 37:
                    return "Code";
                case 38:
                    return "Plugin";
                case 39:
                    return "SolidModel";
                case 40:
                    return "MeshPart";
                default:
                    return "Asset";
            }
        }

        public function ConvertToStringPlural(int $assetTypeId): string
        {
            $string = $this->ConvertToString($assetTypeId);
            switch ($string) {
                case "Lua":
                case "HTML":
                case "Text":
                case "Group Emblem":
                case "App":
                case "Code":
                    return $string;
                default:
                    return $string . "s";
            }
        }

        public function TypeToMaxCosmetic(int $assetTypeId): int
        {
            switch ($assetTypeId) {
                case 8: //hat
                    return 5;
                case 2: //tshirt
                    return 1;
                case 11: //shirt
                    return 1;
                case 12: //pants
                    return 1;
                case 18: //face
                    return 1;
                case 19: //gear
                    return 1;
                case 17: //head
                    return 1;
                case 32: //package
                    return 1;
                default: //what?
                    return 0;
            }
        }

        public function IsWearable(int $assetTypeId): bool
        {
            switch ($assetTypeId) {
                case 8:
                case 2:
                case 11:
                case 12:
                case 18:
                case 19:
                case 17:
                case 32:
                    return true;
                default:
                    return false;
            }
        }
    }
}
