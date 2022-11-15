<?php

use Finobe\Moderation\UserModerationManager;

header('Content-Type: application/json');
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header("Expires: -1");
header("Last-Modified: " . gmdate("D, d M Y H:i:s T") . " GMT");

$notFound = json_encode([
		'success' => false,
		'errorMessage' => 'User not found'
	]);

$userid = (int)$_GET['id'];

$get = $pdo->prepare("SELECT * FROM users WHERE id = :u ORDER BY `id` DESC");
$get->bindParam(":u", $userid, PDO::PARAM_INT);
$get->execute();
if($get->rowCount() == 0)
	exit($notFound);

$user = $get->fetch(PDO::FETCH_OBJ);

if(UserModerationManager::IsBanned($user->id))
	exit($notFound);

exit(json_encode([
	'Id' => $user->id,
	'Username' => $user->username,
	'AvatarUri' => $GLOBALS['renderCDN'] . '/' . $user->ThumbHash,
	'AvatarFinal' => true,
	'IsOnline' => ($user->lastseen + 120) > time(),
]));