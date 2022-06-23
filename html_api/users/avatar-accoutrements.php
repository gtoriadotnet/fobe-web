<?php
$script = "charapp";
$id = (int)$_GET['userId'];
$assets = "";
	
$info = userInfo($id);
if($info !== false) 
{
	$id = $info->id;
	$wearing = "";
	
	$get = $pdo->prepare("SELECT * FROM wearing_items WHERE uid = :u ORDER BY `id` DESC");
	$get->bindParam(":u", $id, PDO::PARAM_INT);
	$get->execute();
	if($get->rowCount() > 0) {
		$items = $get->fetchAll(PDO::FETCH_ASSOC);
		
		foreach($items as $item) {
			$equipped = "";
			if (getAssetInfo($item['aid'])->AssetTypeId != 19) { //dont populate users gears here, we do that later
				$assets .= $url.'/asset/?id='.$item['aid'].$equipped.';';
			}
		}
	}
	$assets .= getUserGearsAccoutrements($id);
}
else
{
	$id = 0;
}

echo "https://api.idk16.xyz/users/bodycolors?userId=".$id."&tick=".time().";".$assets; //&tick to current timestamp cachebuster