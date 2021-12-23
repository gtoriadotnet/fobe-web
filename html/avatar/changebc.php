<?php

/*
0 = Head
1 = Torso
2 = Left Arm
3 = Right Arm
4 = Left Leg
5 = Right Leg
*/

use Alphaland\Users\Render;

$bcdb = array("0" => "h", "1" => "t", "2" => "la", "3" => "ra", "4" => "ll", "5" => "rl");
$cbc = (int)$_POST['bct'];
$clr = (int)$_POST['clr'];
if($cbc > 5 || $cbc < 0) 
{
	echo "Invalid body type.";
	die();
}
if(getBC($clr) != "-") 
{
	if (isThumbnailerAlive())
	{
		if (!Render::RenderCooldown($user->id))
		{
			$upd = $pdo->prepare("UPDATE body_colours SET {$bcdb[$cbc]} = :b WHERE uid = :u");
			$upd->bindParam(":u", $user->id, PDO::PARAM_INT);
			$upd->bindParam(":b", $clr, PDO::PARAM_INT);
			$upd->execute();

			Render::RenderPlayer($localuser);
			
			echo "s";
		}
		else
		{
			http_response_code(500);
		}
	}
}