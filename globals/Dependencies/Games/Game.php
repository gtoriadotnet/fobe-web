<?php

/*
    Alphaland 2021
*/

namespace Alphaland\Games {

    use PDO;

    class Game
    {
        public static function SetChatStyle(int $assetid, int $enum) 
        {
            if ($enum > -1 && $enum < 3)
            {
                $update = $GLOBALS['pdo']->prepare("UPDATE assets SET ChatStyleEnum = :enum WHERE id = :i");
                $update->bindParam(":enum", $enum, PDO::PARAM_INT);
                $update->bindParam(":i", $assetid, PDO::PARAM_INT);
                $update->execute();
            }
        }

        public static function GetChatStyle(int $assetid)
        {
            $enum = $GLOBALS['pdo']->prepare("SELECT ChatStyleEnum FROM assets WHERE id = :i");
			$enum->bindParam(":i", $assetid, PDO::PARAM_INT);
			$enum->execute();
            return $enum->fetch(PDO::FETCH_OBJ)->ChatStyleEnum;
        }

        public static function ConvertChatStyle(int $chatstyle)
        {
            switch ($chatstyle)
            {
                case 0:
                    return "Classic";
                case 1:
                    return "Bubble";
                case 2:
                    return "ClassicAndBubble";
                default:
                    return "ClassicAndBubble";
            }
        }
    }
}
