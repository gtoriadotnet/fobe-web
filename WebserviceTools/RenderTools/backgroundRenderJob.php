<?php

/*
	Finobe 2021
	kinda shit but its meant for background render processes so not really a concern
*/

use Finobe\Assets\Render as AssetRender;
use Finobe\Users\Render as UserRender;

$assetid = $argv[1];
$type = $argv[2];

switch ($type)
{
	case "avatar":
		UserRender::RenderPlayer($assetid);
		break;
	case "avatarcloseup":
		UserRender::RenderPlayerCloseup($assetid);
		break;
	case "hat":
		AssetRender::RenderHat($assetid);
		break;
	case "tshirt":
		AssetRender::RenderTShirt($assetid);
		break;
	case "shirt":
		AssetRender::RenderShirt($assetid);
		break;
	case "pants":
		AssetRender::RenderPants($assetid);
		break;
	case "face":
		AssetRender::RenderFace($assetid);
		break;
	case "gear":
		AssetRender::RenderGear($assetid);
		break;
	case "head":
		AssetRender::RenderHead($assetid);
		break;
	case "place":
		AssetRender::RenderPlace($assetid);
		break;
	case "package":
		AssetRender::RenderPackage($assetid);
		break;
	case "model":
		AssetRender::RenderModel($assetid);
	case "mesh":
		AssetRender::RenderMesh($assetid);
		break;
	default:
		break;
}