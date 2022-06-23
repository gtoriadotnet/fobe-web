<?php

use Finobe\Web\WebContextManager;

header('Content-Type: application/json');

$assetid = $_GET['assetId'];
$useroblox = $_GET['useRoblox'];

if (!$assetid)
{
	http_response_code(400);
}

if ($useroblox == "true")
{
	WebContextManager::Redirect("https://api.roblox.com/marketplace/productinfo?assetId=" . $assetid);	
}
else
{
	$assetInfo = getAssetInfo($assetid);

	if($assetInfo !== FALSE) //asset id exists in finobe db
	{
		$productinfo = json_encode(array(
				"TargetId" => $assetInfo->TargetId,
				"ProductType" => $assetInfo->ProductType,
				"AssetId" => $assetInfo->id,
				"ProductId" => 0,
				"Name" => $assetInfo->Name,
				"Description" => $assetInfo->Description,
				"AssetTypeId" => $assetInfo->AssetTypeId,
				"Creator" => array(
					"Id" => $assetInfo->CreatorId,
					"Name" => getUsername($assetInfo->CreatorId),
					"CreatorType" => "User",
					"CreatorTargetId" => $assetInfo->CreatorId
				),
				"IconImageAssetId" => $assetInfo->IconImageAssetId,
				"Created" => $assetInfo->Created,
				"Updated" => $assetInfo->Updated,
				"PriceInRobux" => $assetInfo->PriceInAlphabux,
				"PriceInTickets" => NULL, //no tickets
				"Sales" => $assetInfo->Sales,
				"IsNew" => $assetInfo->IsNew,
				"IsForSale" => $assetInfo->IsForSale,
				"IsPublicDomain" => boolval($assetInfo->IsPublicDomain),
				"IsLimited" => boolval($assetInfo->IsLimited),
				"IsLimitedUnique" => boolval($assetInfo->IsLimitedUnique),
				"Remaining" => $assetInfo->Remaining,
				"MinimumMembershipLevel" => $assetInfo->MinimumMembershipLevel,
				"ContentRatingTypeId" => $assetInfo->ContentRatingTypeId
				
			), JSON_UNESCAPED_SLASHES);

		die($productinfo);
	}
	http_response_code(400);
}