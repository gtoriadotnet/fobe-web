<?php

/*
	Alphaland 2021
	kinda shit but its meant for background render processes so not really a concern
*/

$assetid = $argv[1];
$type = $argv[2];

switch ($type)
{
	case "avatar":
		RenderPlayer($assetid);
		break;
	case "avatarcloseup":
		RenderPlayerCloseup($assetid);
		break;
	case "hat":
		RenderHat($assetid);
		break;
	case "tshirt":
		RenderTShirt($assetid);
		break;
	case "shirt":
		RenderShirt($assetid);
		break;
	case "pants":
		RenderPants($assetid);
		break;
	case "face":
		RenderFace($assetid);
		break;
	case "gear":
		RenderGear($assetid);
		break;
	case "head":
		RenderHead($assetid);
		break;
	case "place":
		RenderPlace($assetid);
		break;
	case "package":
		RenderPackage($assetid);
		break;
	case "model":
		RenderModel($assetid);
	case "mesh":
		RenderMesh($assetid);
		break;
	default:
		break;
}