<?php

/*
	Alphaland 2021
	A bunch of global functions used sitewide
	TODO: clean up a lot of legacy code
*/

//img tools (potentially high resource usage) (probably blocking)

use Alphaland\Assets\Render;
use Alphaland\Users\Render as UsersRender;
use Alphaland\Web\WebContextManager;

function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) {
	$cut = imagecreatetruecolor($src_w, $src_h);
	imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
	imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
	imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
} 

function isbase64png($base64) //must already be decoded
{
	$mimetype = finfo_buffer(finfo_open(), $base64, FILEINFO_MIME_TYPE); //file type
									
	if (in_array($mimetype, array('image/png'))) //verify that its a valid png image (not corrupted or something in a weird scenario)
	{	
		return true;
	}
	return false;
}

// ...

//obfuscation

function obfuscate_email($email)
{
    $em   = explode("@",$email);
    $name = implode('@', array_slice($em, 0, count($em)-1));
    $len  = floor(strlen($name)/2);

    return substr($name,0, $len) . str_repeat('.', $len) . "@" . end($em);   
}

// ..

//safe generation utilities

function genHash($len)
{
	return bin2hex(openssl_random_pseudo_bytes($len));
}

function genTicketHash($len)
{
	$hash = "";
	$alloc = true;
	while ($alloc) {
		$hash = genHash($len);
		
		$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM user_auth_tickets WHERE token = :t");
		$tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
		$tokencheck->execute();
		if ($tokencheck->rowCount() > 0) {
			continue;
		} else {
			$alloc = false;
		}
	}
	return $hash;
}

function genVerifcationEmailHash($len)
{
	$hash = "";
	$alloc = true;
	while ($alloc) {
		$hash = genHash($len);
		
		$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM verify_email_keys WHERE token = :t");
		$tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
		$tokencheck->execute();
		if ($tokencheck->rowCount() > 0) {
			continue;
		} else {
			$alloc = false;
		}
	}
	return $hash;
}

function genResetPasswordHash($len)
{
	$hash = "";
	$alloc = true;
	while ($alloc) {
		$hash = genHash($len);
		
		$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM password_reset_keys WHERE token = :t");
		$tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
		$tokencheck->execute();
		if ($tokencheck->rowCount() > 0) {
			continue;
		} else {
			$alloc = false;
		}
	}
	return $hash;
}

function genSignupKeyHash($len)
{
	$hash = "";
	$alloc = true;
	while ($alloc) {
		$hash = genHash($len);
		
		$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM signup_keys WHERE signupkey = :t");
		$tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
		$tokencheck->execute();
		if ($tokencheck->rowCount() > 0) {
			continue;
		} else {
			$alloc = false;
		}
	}
	return $hash;
}

function genSessionHash($len)
{
	$hash = "";
	$alloc = true;
	while ($alloc) {
		$hash = genHash($len);
		
		$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM sessions WHERE token = :t");
		$tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
		$tokencheck->execute();
		if ($tokencheck->rowCount() > 0) {
			continue;
		} else {
			$alloc = false;
		}
	}
	return $hash;
}

function genAssetHash($len)
{
	$hash = "";
	$alloc = true;
	while ($alloc) {
		$hash = genHash($len);
		
		$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE Hash = :t");
		$tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
		$tokencheck->execute();
		if ($tokencheck->rowCount() > 0) {
			continue;
		}
		else {
			$alloc = false;
		}
	}
	return $hash;
}

function safeAssetMD5($md5)
{
	$hashcheck = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE Hash = :t");
	$hashcheck->bindParam(":t", $md5, PDO::PARAM_STR);
	$hashcheck->execute();
	if ($hashcheck->rowCount() > 0) {
		$md5 = genAssetHash(16); //fallback to random gen hash (this should never happen)
	}
	return $md5;
}

function genGameLaunchTokenHash($len)
{
	$hash = "";
	$alloc = true;
	while ($alloc)
	{
		$hash = genHash($len);
		
		$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM game_launch_tokens WHERE token = :t");
		$tokencheck->bindParam(":t", $hash, PDO::PARAM_STR);
		$tokencheck->execute();
		if ($tokencheck->rowCount() > 0) {
			continue;
		} else {
			$alloc = false;
		}
	}
	return $hash;
}

//gen uuid
function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function genJobId()
{
	$uuid = "";
	$alloc = true;
	while ($alloc) {
		$uuid = gen_uuid();
		$uuidcheck = $GLOBALS['pdo']->prepare("SELECT * FROM open_servers WHERE jobid = :u");
		$uuidcheck->bindParam(":u", $uuid, PDO::PARAM_STR);
		$uuidcheck->execute();
		if ($uuidcheck->rowCount() > 0) {
			continue;
		} else {
			$alloc = false;
		}
	}
	return $uuid;
}

//

//auth ticket utilities

function genTicket() 
{
	$tokencheck = $GLOBALS['pdo']->prepare("SELECT * FROM user_auth_tickets WHERE uid = :u");
	$tokencheck->bindParam(":u", $GLOBALS['user']->id, PDO::PARAM_INT);
	$tokencheck->execute();
	if ($tokencheck->rowCount() > 0) {
		$tokenerase = $GLOBALS['pdo']->prepare("DELETE FROM user_auth_tickets WHERE uid = :u");
		$tokenerase->bindParam(":u", $GLOBALS['user']->id, PDO::PARAM_INT);
		$tokenerase->execute();
	}
	
	$t = genTicketHash(64); //128 characters long (value + value)
	$n = $GLOBALS['pdo']->prepare("INSERT INTO user_auth_tickets(token,session,uid,whenGenerated) VALUES(:t,:s,:u,UNIX_TIMESTAMP())");
	$n->bindParam(":t", $t, PDO::PARAM_STR);
	$n->bindParam(":s", $_COOKIE['token'], PDO::PARAM_STR);
	$n->bindParam(":u", $GLOBALS['user']->id, PDO::PARAM_INT);
	if($n->execute()) {
		return $t;
	}
	return false;
}

//end auth ticket utilities

//signing utilities

function signData($data, $rbxsig=true)
{
	$sig = "";
	$key = "-----BEGIN RSA PRIVATE KEY-----\n" . wordwrap(file_get_contents($GLOBALS['privateKeyPath']), 64, "\n",true) . "\n-----END RSA PRIVATE KEY-----\n";
	openssl_sign($data, $sig, $key, OPENSSL_ALGO_SHA1);

	if ($rbxsig) {
		return "--rbxsig%" . base64_encode($sig) . "%" . $data;
	}
	return base64_encode($sig);
}

//end signing utilities

//TODO: Render Queue?

//outfit system

function playerOutfitsCount($userid)
{
	$outfits = $GLOBALS['pdo']->prepare('SELECT * FROM user_outfits WHERE userid = :uid');
	$outfits->bindParam(":uid", $userid, PDO::PARAM_INT);
	$outfits->execute();
	return $outfits->rowCount();
}

function playerOwnsOutfit($userid, $outfitid)
{
	$outfit = $GLOBALS['pdo']->prepare('SELECT * FROM user_outfits WHERE userid = :uid AND id = :id');
	$outfit->bindParam(":uid", $userid, PDO::PARAM_INT);
	$outfit->bindParam(":id", $outfitid, PDO::PARAM_INT);
	$outfit->execute();
	if ($outfit->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function isThumbHashInOutfit($thumbhash)
{
	$outfit = $GLOBALS['pdo']->prepare('SELECT * FROM user_outfits WHERE ThumbHash = :hash');
	$outfit->bindParam(":hash", $thumbhash, PDO::PARAM_STR);
	$outfit->execute();
	if ($outfit->rowCount() > 0 || $thumbhash == $GLOBALS['defaultOutfitHash']) //default outfit hash
	{
		return true;
	}
	return false;
}

function isHeadshotThumbHashInOutfit($thumbhash)
{
	$outfit = $GLOBALS['pdo']->prepare('SELECT * FROM user_outfits WHERE HeadshotThumbHash = :hash');
	$outfit->bindParam(":hash", $thumbhash, PDO::PARAM_STR);
	$outfit->execute();
	if ($outfit->rowCount() > 0 || $thumbhash == $GLOBALS['defaultHeadshotHash']) //default headshot hash
	{
		return true;
	}
	return false;
}

function createOutfit($name, $userid)
{
	$name = cleanInput($name);

	if (strlen($name) > 50)
	{
		return "Name too long";
	}
	else if (strlen($name) < 3)
	{
		return "Name too short";
	}
	else
	{
		if (playerOutfitsCount($userid) < 24)
		{
			if (!checkUserPendingRender($userid))
			{
				//queries
				$hash = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = " . $userid);
				$hash->execute();
				$hash = $hash->fetch(PDO::FETCH_OBJ);
				$headshothash = $hash->HeadshotThumbHash;
				$headshotAngelRight = $hash->headshotAngleRight;
				$headshotAngleLeft = $hash->headshotAngleLeft;
				$hash = $hash->ThumbHash;

				
				$wearingcolors = $GLOBALS['pdo']->prepare('SELECT * FROM body_colours WHERE uid = ' . $userid);
				$wearingcolors->execute();
				$wearingcolors = $wearingcolors->fetch(PDO::FETCH_OBJ);

				//users current body colors
				$head = (int)$wearingcolors->h;
				$torso = (int)$wearingcolors->t;
				$leftarm = (int)$wearingcolors->la;
				$rightarm = (int)$wearingcolors->ra;
				$leftleg = (int)$wearingcolors->ll;
				$rightleg = (int)$wearingcolors->rl;

				//currently wearing items
				$assets = wearingAssets($userid);

				//add to db
				$outfit = $GLOBALS['pdo']->prepare("INSERT INTO user_outfits(userid, assets, name, h, t, la, ra, ll, rl, headshotAngleRight, headshotAngleLeft, ThumbHash, HeadshotThumbHash, whenCreated) VALUES (:uid, :assets, :name, :h, :t, :la, :ra, :ll, :rl, :har, :hal, :th, :hth, UNIX_TIMESTAMP())");
				$outfit->bindParam(":uid", $userid, PDO::PARAM_INT);
				$outfit->bindParam(":assets", $assets, PDO::PARAM_STR);
				$outfit->bindParam(":name", $name, PDO::PARAM_STR);
				$outfit->bindParam(":h", $head, PDO::PARAM_INT);
				$outfit->bindParam(":t", $torso, PDO::PARAM_INT);
				$outfit->bindParam(":la", $leftarm, PDO::PARAM_INT);
				$outfit->bindParam(":ra", $rightarm, PDO::PARAM_INT);
				$outfit->bindParam(":ll", $leftleg, PDO::PARAM_INT);
				$outfit->bindParam(":rl", $rightleg, PDO::PARAM_INT);
				$outfit->bindParam(":har", $headshotAngelRight, PDO::PARAM_INT);
				$outfit->bindParam(":hal", $headshotAngleLeft, PDO::PARAM_INT);
				$outfit->bindParam(":th", $hash, PDO::PARAM_STR);
				$outfit->bindParam(":hth", $headshothash, PDO::PARAM_STR);
				$outfit->execute();	
				return true;
			}
			return "Please wait for the current render";
		}
		return "Limit of 24 outfits";
	}
}

function deleteOutfit($userid, $outfitid)
{
	if (playerOwnsOutfit($userid, $outfitid))
	{
		$delete = $GLOBALS['pdo']->prepare("DELETE from user_outfits WHERE userid = :uid AND id = :id");
		$delete->bindParam(":uid", $userid, PDO::PARAM_INT);
		$delete->bindParam(":id", $outfitid, PDO::PARAM_INT);
		$delete->execute();
		if ($delete->rowCount() > 0)
		{
			return true;
		}
	}
	return "Error occurred";
}

function updateOutfit($userid, $outfitid, $name)
{
	$name = cleanInput($name);

	if (strlen($name) > 50)
	{
		return "Name too long";
	}
	else if (strlen($name) < 3)
	{
		return "Name too short";
	}
	else
	{
		if (playerOwnsOutfit($userid, $outfitid))
		{
			if (!checkUserPendingRender($userid))
			{
				if (deleteOutfit($userid, $outfitid) === TRUE);
				{
					createOutfit($name, $userid);
					return true;
				}
				return "Failed to update outfit, contact an Administrator";
			}
			return "Please wait for the current render";
		}
		return "Error occurred";
	}
}

function applyOutfit($userid, $outfitid)
{
	if (playerOwnsOutfit($userid, $outfitid))
	{
		if (!checkUserPendingRender($userid))
		{
			$outfit = $GLOBALS['pdo']->prepare('SELECT * FROM user_outfits WHERE userid = :uid AND id = :id');
			$outfit->bindParam(":uid", $userid, PDO::PARAM_INT);
			$outfit->bindParam(":id", $outfitid, PDO::PARAM_INT);
			$outfit->execute();
			if ($outfit->rowCount() > 0)
			{
				//vars
				$outfit = $outfit->fetch(PDO::FETCH_OBJ);
				$outfitassets = explode(";", $outfit->assets);

				//outfit body colors
				$outfithead = (int)$outfit->h;
				$outfittorso = (int)$outfit->t;
				$outfitleftarm = (int)$outfit->la;
				$outfitrightarm = (int)$outfit->ra;
				$outfitleftleg = (int)$outfit->ll;
				$outfitrightleg = (int)$outfit->rl;

				//headshot settings
				$headshotAngelRight = $outfit->headshotAngleRight;
				$headshotAngleLeft = $outfit->headshotAngleLeft;

				//apply outfit body colors
				$bodycolor = $GLOBALS['pdo']->prepare("UPDATE body_colours SET h = :h, t = :t, la = :la, ra = :ra, ll = :ll, rl = :rl WHERE uid = :uid");
				$bodycolor->bindParam(":h", $outfithead, PDO::PARAM_INT);
				$bodycolor->bindParam(":t", $outfittorso, PDO::PARAM_INT);
				$bodycolor->bindParam(":la", $outfitleftarm, PDO::PARAM_INT);
				$bodycolor->bindParam(":ra", $outfitrightarm, PDO::PARAM_INT);
				$bodycolor->bindParam(":ll", $outfitleftleg, PDO::PARAM_INT);
				$bodycolor->bindParam(":rl", $outfitrightleg, PDO::PARAM_INT);
				$bodycolor->bindParam(":uid", $userid, PDO::PARAM_INT);
				$bodycolor->execute();

				//delete all wearing items
				$deequip = $GLOBALS['pdo']->prepare("DELETE from wearing_items WHERE uid = :u"); //delete all wearing
				$deequip->bindParam(":u", $userid, PDO::PARAM_INT);
				$deequip->execute();

				//apply items in the outfit
				foreach($outfitassets as $asset)
				{
					if ($asset != "") //hack for outfits with no wearing items
					{
						$equip = $GLOBALS['pdo']->prepare("INSERT INTO wearing_items(uid,aid,whenWorn) VALUES(:u,:a,UNIX_TIMESTAMP())");
						$equip->bindParam(":u", $userid, PDO::PARAM_INT);
						$equip->bindParam(":a", $asset, PDO::PARAM_INT);
						$equip->execute();
					}
				}

				//delete current render and headshot if its not part of an outfit
				$prevhash = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :i");
				$prevhash->bindParam(":i", $userid, PDO::PARAM_INT);
				$prevhash->execute();
				$prevhash = $prevhash->fetch(PDO::FETCH_OBJ);
				$oldhash = $prevhash->ThumbHash;
				$oldheadshothash = $prevhash->HeadshotThumbHash;
				if (!isThumbHashInOutfit($oldhash))
				{
					unlink($GLOBALS['renderCDNPath'] . $oldhash);
				}
				if (!isHeadshotThumbHashInOutfit($oldheadshothash))
				{
					unlink($GLOBALS['renderCDNPath'] . $oldheadshothash);
				}
				
				//outfits hashes
				$hash = $outfit->ThumbHash;
				$headshothash = $outfit->HeadshotThumbHash;

				if ($headshothash == NULL) //outfit was created before headshots release (probably?)
				{
					UsersRender::RenderPlayerCloseup($userid);

					$headshothash = userInfo($userid)->HeadshotThumbHash;

					$update = $GLOBALS['pdo']->prepare('UPDATE user_outfits SET HeadshotThumbHash = :hhash WHERE id = :oid');
					$update->bindParam(":hhash", $headshothash, PDO::PARAM_STR);
					$update->bindParam(":oid", $outfitid, PDO::PARAM_INT);
					$update->execute();
				}

				//apply the outfit (yay less render server load)
				$user = $GLOBALS['pdo']->prepare('UPDATE users SET ThumbHash = :hash, HeadshotThumbHash = :hhash, headshotAngleRight = :har, headshotAngleLeft = :hal WHERE id = ' . $userid);
				$user->bindParam(":hash", $hash, PDO::PARAM_STR);
				$user->bindParam(":hhash", $headshothash, PDO::PARAM_STR);
				$user->bindParam(":har", $headshotAngelRight, PDO::PARAM_INT);
				$user->bindParam(":hal", $headshotAngleLeft, PDO::PARAM_INT);
				$user->execute();

				return true;
			}
		}
		return "Please wait for the current render";
	}
	return "Error occurred";
}

//place launcher queue system

function removePlayerFromQueue($userid)
{
	$removeQueue = $GLOBALS['pdo']->prepare("DELETE FROM game_launch_queue WHERE userid = :uid");
	$removeQueue->bindParam(":uid", $userid, PDO::PARAM_INT);
	$removeQueue->execute();
	if ($removeQueue->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function playerInQueue($placeid, $jobid, $userid)
{
	$playerinqueue = $GLOBALS['pdo']->prepare("SELECT * FROM game_launch_queue WHERE placeid = :pid AND jobid = :jid AND userid = :uid");
	$playerinqueue->bindParam(":pid", $placeid, PDO::PARAM_INT);
	$playerinqueue->bindParam(":jid", $jobid, PDO::PARAM_STR);
	$playerinqueue->bindParam(":uid", $userid, PDO::PARAM_INT);
	$playerinqueue->execute();
	if ($playerinqueue->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function addPlayerToQueue($placeid, $jobid, $userid)
{
	if (!playerInQueue($placeid, $jobid, $userid))
	{
		removePlayerFromQueue($userid); //if any queue leftover
		$newQueue = $GLOBALS['pdo']->prepare("INSERT INTO game_launch_queue(placeid, jobid, userid, queuePing, whenQueued) VALUES (:pid, :jid, :uid, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())");
		$newQueue->bindParam(":pid", $placeid, PDO::PARAM_INT);
		$newQueue->bindParam(":jid", $jobid, PDO::PARAM_STR);
		$newQueue->bindParam(":uid", $userid, PDO::PARAM_INT);
		$newQueue->execute();
	}
	else //ping
	{
		$updateQueue = $GLOBALS['pdo']->prepare("UPDATE game_launch_queue SET queuePing = UNIX_TIMESTAMP() WHERE placeid = :pid AND jobid = :jid AND userid = :uid");
		$updateQueue->bindParam(":pid", $placeid, PDO::PARAM_INT);
		$updateQueue->bindParam(":jid", $jobid, PDO::PARAM_STR);
		$updateQueue->bindParam(":uid", $userid, PDO::PARAM_INT);
		$updateQueue->execute();
	}
}

function isNextInQueue($placeid, $jobid, $userid)
{
	$queue = $GLOBALS['pdo']->prepare("SELECT * FROM game_launch_queue WHERE placeid = :pid AND jobid = :jid ORDER BY whenQueued DESC LIMIT 1");
	$queue->bindParam(":pid", $placeid, PDO::PARAM_INT);
	$queue->bindParam(":jid", $jobid, PDO::PARAM_STR);
	$queue->execute();
	$queue = $queue->fetch(PDO::FETCH_OBJ);
	if ((int)$queue->queuePing + 10 < time()) //hasnt pinged in 10 seconds, assume they left queue
	{
		removePlayerFromQueue($queue->userid);
	}
	else if ($queue->userid == $userid)
	{
		return true;
	}
	return false;
}

// ...

//filter shit

function getWordList()
{
	return array(
	'Afro-engineering',
	'Afroengineering',
	'Afro engineering',
	'African engineering',
	'Africanengineering',
	'African-engineering',
	'nigger rigging',
	'nigger-rigging',
	'niggerrigging',
	'Ashke Nazi',
	'Ashke-Nazi',
	'AshkeNazi',
	'nazi',
	'hitler',
	'gas chambers',
	'gaschambers',
	'gas-chambers',
	'gas chamber',
	'gaschamber',
	'gas-chamber',
	'genocide',
	'Beaner',
	'Beaney',
	'boonie',
	'Coon',
	'Coonass',
	'Cracker',
	'Dothead',
	'Jewboy',
	'Jigaboo',
	'jiggabo',
	'jigarooni',
	'jijjiboo',
	'zigabo',
	'jigger',
	'Niglet',
	'nigglet',
	'Nig-nog',
	'Nignog',
	'Nigger',
	'niger',
	'nigor',
	'niggur ',
	'niggar',
	//'Nigga',
	//'nigga',
	'Porch monkey',
	'Porchmonkey',
	'porch-monkey',
	'Sand nigger',
	'Sandnigger',
	'Sand-nigger',
	'Spearchucker',
	'spick',
	'Tacohead',
	'TarBaby',
	'Tar Baby',
	'Tar-Baby',
	'Towel head',
	'Towelhead',
	'Towel-head',
	'Wetback',
	'Wigger',
	'Whigger',
	'Wigga',
	'White trash',
	'Whitetrash',
	'White-trash',
	'Whitey',
	'Zipperhead',
	'fagot',
	'faggot',
	'fegot',
	'faget',
	'feget',
	'fag',
	'rape',
	'tranny',
	'tarbaby',
	'tar baby',
	'blackface',
	'black face',
	'dogwater',
	'dog water',
	'dog-water',
	'Mirai'
	);
}

function isFiltered($text)
{
	return checkIfFiltered($text, getWordList());
}

function checkIfFiltered($text, $badlist)
{
	foreach($badlist as $a) 
	{
		if (stripos($text,$a) !== false) return true;
	}
	return false;
}

function filterText($text)
{
	return performFilter($text, getWordList());
}

function performFilter($text, $badlist)
{
	$filterCount = sizeof($badlist);
	for ($i = 0; $i < $filterCount; $i++) 
	{
		$text = preg_replace_callback('/(' . $badlist[$i] . ')/i', function($matches){return str_repeat('#', strlen($matches[0]));}, $text);
	}
	return $text;
}

function logChatMessage($userid, $text, $trippedfilter) //privacy concern?
{
	if (userInfo($userid)->rank != 2) //dont log admins chats
	{
		$trippedfilter = boolval($trippedfilter);
		$p = $GLOBALS['pdo']->prepare("SELECT *  FROM game_presence WHERE uid = :i AND (lastPing + 50) > UNIX_TIMESTAMP()");
		$p->bindParam(":i", $userid, PDO::PARAM_INT);
		$p->execute();
		$placeid = $p->fetch(PDO::FETCH_OBJ)->placeid;		

		$logmessage = $GLOBALS['pdo']->prepare("INSERT INTO chat_logs(message, gameAssetId, whoSent, whenSent, trippedFilter) VALUES (:message, :gameAssetId, :whoSent, UNIX_TIMESTAMP(), :trippedFilter)");
		$logmessage->bindParam(":message", $text, PDO::PARAM_STR);
		$logmessage->bindParam(":gameAssetId", $placeid, PDO::PARAM_INT);
		$logmessage->bindParam(":whoSent", $userid, PDO::PARAM_INT);
		$logmessage->bindParam(":trippedFilter", $trippedfilter, PDO::PARAM_INT);
		$logmessage->execute();
	}
}

//personal build servers 

function isUserWhitelisted($placeid, $userid)
{
	$whitelist = $GLOBALS['pdo']->prepare("SELECT * FROM game_access WHERE placeid = :pid AND userid = :uid");
	$whitelist->bindParam(":pid", $placeid, PDO::PARAM_INT);
	$whitelist->bindParam(":uid", $userid, PDO::PARAM_INT);
	$whitelist->execute();
	if ($whitelist->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function gameWhitelistAddUser($placeid, $userid)
{
	if (isOwner($placeid))
	{
		if ($userid != getAssetInfo($placeid)->CreatorId && !isUserWhitelisted($placeid, $userid))
		{
			$whitelist = $GLOBALS['pdo']->prepare("INSERT INTO game_access(placeid, userid, whenWhitelisted) VALUES (:pid, :uid, UNIX_TIMESTAMP())");
			$whitelist->bindParam(":pid", $placeid, PDO::PARAM_INT);
			$whitelist->bindParam(":uid", $userid, PDO::PARAM_INT);
			if ($whitelist->execute())
			{
				return true;
			}
			return "Failed to whitelist user";
		}
		return "Invalid User";
	}
	return "No Permission";
}

function gameWhitelistRemoveUser($placeid, $userid)
{
	if (isOwner($placeid))
	{
		if ($userid != getAssetInfo($placeid)->CreatorId)
		{
			$whitelistremove = $GLOBALS['pdo']->prepare("DELETE FROM game_access WHERE placeid = :pid AND userid = :uid");
			$whitelistremove->bindParam(":pid", $placeid, PDO::PARAM_INT);
			$whitelistremove->bindParam(":uid", $userid, PDO::PARAM_INT);
			$whitelistremove->execute();
			if ($whitelistremove->rowCount() > 0)
			{
				return true;
			}
			return "Failed to unwhitelist user";
		}
		return "Invalid User";
	}
	return "No Permission";
}

function gameClearWhitelist($placeid)
{
	if (isOwner($placeid))
	{
		$whitelistclear = $GLOBALS['pdo']->prepare("DELETE FROM game_access WHERE placeid = :pid");
		$whitelistclear->bindParam(":pid", $placeid, PDO::PARAM_INT);
		$whitelistclear->execute();
		return true;
	}
	return false;
}

function getPBSRankName($rank) 
{
	switch ($rank)
	{
		case 255:
			return "Owner";
		case 240:
			return "Admin";
		case 128:
			return "Member";
		case 10:
			return "Visitor";
		case 0:
			return "Banned";
	}
}

function updateBuildServerRank($placeid, $userid, $rank)
{
	if ($userid != getAssetInfo($placeid)->CreatorId && getAssetInfo($placeid)->isPersonalServer == 1)
	{
		if ($rank == 240 || $rank == 128 || $rank == 0) //PBS RANKS ADMIN, MEMBER AND BANNED
		{
			$exists = $GLOBALS['pdo']->prepare("SELECT * FROM personal_build_ranks WHERE placeid = :pid AND userid = :uid");
			$exists->bindParam(":pid", $placeid, PDO::PARAM_INT);
			$exists->bindParam(":uid", $userid, PDO::PARAM_INT);
			$exists->execute();
			if ($exists->rowCount() > 0) //we got a rank already, update
			{
				$setrank = $GLOBALS['pdo']->prepare("UPDATE personal_build_ranks SET rank = :rank WHERE placeid = :pid AND userid = :uid");
				$setrank->bindParam(":rank", $rank, PDO::PARAM_INT);
				$setrank->bindParam(":pid", $placeid, PDO::PARAM_INT);
				$setrank->bindParam(":uid", $userid, PDO::PARAM_INT);
				$setrank->execute();
				return true;
			}
			else //no rank, create new one
			{
				$newrank = $GLOBALS['pdo']->prepare("INSERT INTO personal_build_ranks(placeid, userid, rank, whenRanked) VALUES (:pid, :uid, :rank, UNIX_TIMESTAMP())");
				$newrank->bindParam(":rank", $rank, PDO::PARAM_INT);
				$newrank->bindParam(":pid", $placeid, PDO::PARAM_INT);
				$newrank->bindParam(":uid", $userid, PDO::PARAM_INT);
				$newrank->execute();
				return true;
			}
		}
		else if ($rank == 10) //PBS RANK VISITOR
		{
			$delrank = $GLOBALS['pdo']->prepare("DELETE FROM personal_build_ranks WHERE placeid = :pid AND userid = :uid");
			$delrank->bindParam(":pid", $placeid, PDO::PARAM_INT);
			$delrank->bindParam(":uid", $userid, PDO::PARAM_INT);
			$delrank->execute();
			return true;
		}
	}
	return "Error occurred";
}

function getBuildServerRank($placeid, $userid)
{
	if ($userid == getAssetInfo($placeid)->CreatorId)
	{
		return 255;
	}
	else
	{
		$rank = $GLOBALS['pdo']->prepare("SELECT * FROM personal_build_ranks WHERE placeid = :pid AND userid = :uid");
		$rank->bindParam(":pid", $placeid, PDO::PARAM_INT);
		$rank->bindParam(":uid", $userid, PDO::PARAM_INT);
		$rank->execute();
		if ($rank->rowCount() > 0)
		{
			return $rank->fetch(PDO::FETCH_OBJ)->rank;
		}
	}
	return 10; //no rank. consider them Visitor rank
}

function removePBSUser($placeid, $userid)
{
	$remove = $GLOBALS['pdo']->prepare("DELETE FROM personal_build_ranks WHERE placeid = :pid AND userid = :uid");
	$remove->bindParam(":pid", $placeid, PDO::PARAM_INT);
	$remove->bindParam(":uid", $userid, PDO::PARAM_INT);
	$remove->execute();
	if ($remove->rowCount() > 0)
	{
		return true;
	}
	return "Error occurred";
}

function updatePBSGameSettings($placeid, $name, $description, $commentsenabled, $whitelistenabled, $maxplayers)
{
	if (isOwner($placeid) && getAssetInfo($placeid)->isPersonalServer == 1)
	{
		$name = cleanInput($name);
		$description = cleanInput($description);
		$comments = boolval($commentsenabled);
		$whitelistenabled = boolval($whitelistenabled);
		$players = (int)$maxplayers;

		if (getAssetInfo($placeid)->Name != $name) //dont run if name hasnt changed
		{
			if (strlen($name) > 50)
			{
				return "Name too long";
			}
			else if (strlen($name) < 3)
			{
				return "Name too short";
			}
		}
		
		if (getAssetInfo($placeid)->Description != $description) //dont run if description hasnt changed
		{
			if (strlen($description) > 1024)
			{
				return "Description too long";
			}
			else if (strlen($description) < 3)
			{
				return "Description too short";
			}
		}

		if (getAssetInfo($placeid)->IsCommentsEnabled != $comments) //dont run if iscommentsenabled hasnt changed
		{
			if (!is_bool($comments))
			{
				return "Error occurred";
			}
		}

		if (getAssetInfo($placeid)->isGameWhitelisted != $whitelistenabled) //dont run if isGameWhitelisted hasnt changed
		{
			if (!is_bool($whitelistenabled))
			{
				return "Error occurred";
			}

			if ($whitelistenabled == 0) //whitelist being disabled
			{
				if (!gameClearWhitelist($placeid))
				{
					$whitelistenabled = 1;
				}
			}
		}

		if (getAssetInfo($placeid)->MaxPlayers != $players) //dont run if MaxPlayers hasnt changed
		{
			if ($players > 12 || $players < 1)
			{
				return "Error occurred";
			}
		}
		
		$configgame = $GLOBALS['pdo']->prepare("UPDATE assets SET Name = :name, Description = :description, isGameWhitelisted  = :pbswhitelistenabled, IsCommentsEnabled = :commentsenabled, MaxPlayers = :maxplayers WHERE id = :assetid");
		$configgame->bindParam(":name", $name, PDO::PARAM_STR);
		$configgame->bindParam(":description", $description, PDO::PARAM_STR);
		$configgame->bindParam(":pbswhitelistenabled", $whitelistenabled, PDO::PARAM_INT);
		$configgame->bindParam(":commentsenabled", $comments, PDO::PARAM_INT);
		$configgame->bindParam(":maxplayers", $players, PDO::PARAM_INT);
		$configgame->bindParam(":assetid", $placeid, PDO::PARAM_INT);
		$configgame->execute();
		return true;
	}
	return "No permission";
}

// ...

//groups (admins have full access to every group they join)

function isInGroup($userid, $groupid)
{
	$member = $GLOBALS['pdo']->prepare("SELECT * FROM group_members WHERE userid = :uid AND groupid = :gid");
	$member->bindParam(":uid", $userid, PDO::PARAM_INT);
	$member->bindParam(":gid", $groupid, PDO::PARAM_INT);
	$member->execute();
	if ($member->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function userGroupsCount()
{
	$localplayer = $GLOBALS['user']->id;
	$groups = $GLOBALS['pdo']->prepare("SELECT * FROM groups WHERE creatorid = :creatorid");
	$groups->bindParam(":creatorid", $localplayer, PDO::PARAM_INT);
	$groups->execute();
	return $groups->rowCount();
}

function createGroup($name, $description, $approval, $base64emblem)
{
	if (userGroupsCount() == 6 && !$GLOBALS['user']->isAdmin())
	{
		return "Limited to 6 groups per player";
	}
	
	$name = cleanInput($name);
	$description = cleanInput($description);
	$approval = boolval($approval);
	$base64emblem = file_get_contents($base64emblem); //this removes the header from js post and base64 decodes it, very convenient
	$mimetype = finfo_buffer(finfo_open(), $base64emblem, FILEINFO_MIME_TYPE); //file type
		
	if (groupNameExists($name))
	{
		return "Group name taken";
	}
	else if (strlen($name) > 50)
	{
		return "Group name too long";
	}
	else if (strlen($name) < 3)
	{
		return "Group name too short";
	}
	else if (strlen($description) > 1024)
	{
		return "Group description too long";
	}
	else if (strlen($description) < 3)
	{
		return "Group description too short";
	}
	else if (!is_bool($approval))
	{
		return "Error occurred";
	}
	else if (!$base64emblem)
	{
		return "Image required";
	}
	else if (!in_array($mimetype, array('image/png','image/jpeg')))
	{
		return "Invalid image provided";
	}
	else if (!removeCurrency(20, "Purchase of group name ".$name))
	{
		return "Not enough Alphabux";
	}
	else
	{
		try 
		{
			$textureUploadDirectory = $GLOBALS['thumbnailCDNPath']; //directory where the textures are stored
			$emblemhash = genAssetHash(16);
				
			//check dimensions
			$imagedetails = getimagesizefromstring($base64emblem);
			$width = $imagedetails[0];
			$height = $imagedetails[1];
				
			if ($width > 150 || $height > 150 || $width < 150 || $height < 150)
			{
				$img = imagecreatefromstring($base64emblem);
				$width = imagesx($img);
				$height = imagesy($img);
				$tmp = imagecreatetruecolor(150, 150);
				imagealphablending($tmp , false);
				imagesavealpha($tmp , true);
				imagecopyresampled($tmp, $img, 0, 0, 0, 0, 150, 150, $width, $height);
				if (!imagepng($tmp, $textureUploadDirectory . $emblemhash)) {
					return "Error occurred";
				}
			}
			else
			{
				if (!file_put_contents($textureUploadDirectory . $emblemhash, $base64emblem))
				{
					return "Error occurred";
				}
			}
			
			$creatorid = $GLOBALS['user']->id;
				
			$GLOBALS['pdo']->exec("LOCK TABLES assets WRITE"); //lock since this stuff is sensitive
				
			$b = $GLOBALS['pdo']->prepare("SELECT * FROM assets");
			$b->execute();
													
			//grab auto increment values
			$autoincrement = $b->rowCount() + 1; //initial auto increment value
				
			//add texture to assets
			$assetname = $name . " Emblem";
			$x = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`, `Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,22,:aname,'Group Emblem',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),:oid,:aid2,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,:hash)");
			$x->bindParam(":aid", $autoincrement, PDO::PARAM_INT);
			$x->bindParam(":aname", $assetname, PDO::PARAM_STR);
			$x->bindParam(":oid", $creatorid, PDO::PARAM_INT);
			$x->bindParam(":aid2", $autoincrement, PDO::PARAM_INT);
			$x->bindParam(":hash", $emblemhash, PDO::PARAM_STR);
			$x->execute();
				
			$GLOBALS['pdo']->exec("UNLOCK TABLES"); 
				
				
			$GLOBALS['pdo']->exec("LOCK TABLES groups WRITE"); //lock since this stuff is sensitive
			
			$g = $GLOBALS['pdo']->prepare("SELECT * FROM groups");
			$g->execute();
													
			//grab auto increment values
			$nextgroup = $g->rowCount() + 1; //initial auto increment value
				
			$group = $GLOBALS['pdo']->prepare("INSERT INTO `groups` (`id`, `name`, `description`, `manualapproval`, `creatorid`, `emblem`, `moderated`) VALUES (:id, :name, :description, :approvals, :creatorid, :emblem, 0)");
			$group->bindParam(":id", $nextgroup, PDO::PARAM_INT);
			$group->bindParam(":name", $name, PDO::PARAM_STR);
			$group->bindParam(":description", $description, PDO::PARAM_STR);
			$group->bindParam(":approvals", $approval, PDO::PARAM_INT);
			$group->bindParam(":creatorid", $creatorid, PDO::PARAM_INT);
			$group->bindParam(":emblem", $autoincrement, PDO::PARAM_INT);
			$group->execute();
				
			$GLOBALS['pdo']->exec("UNLOCK TABLES"); 
				
			$groupjoin = $GLOBALS['pdo']->prepare("INSERT INTO `group_members` (`userid`, `groupid`, `rank`, `whenJoined`) VALUES (:userid, :groupid, '255', UNIX_TIMESTAMP())");
			$groupjoin->bindParam(":userid", $creatorid, PDO::PARAM_INT);
			$groupjoin->bindParam(":groupid", $nextgroup, PDO::PARAM_INT);
			$groupjoin->execute();
				
			$ownerrole = $GLOBALS['pdo']->prepare("INSERT INTO `group_roles` (`groupid`, `rolename`, `rank`, `AccessGroupWall`, `PostGroupWall`, `DeleteGroupWallPosts`, `PostGroupShout`, `ManageLowerRanks`, `KickLowerRanks`, `AcceptJoinRequests`, `ViewAuditLog`) VALUES (:groupid, 'Owner', '255', '1', '1', '1', '1', '1', '1', '1', '1')");
			$ownerrole->bindParam(":groupid", $nextgroup, PDO::PARAM_INT);
			$ownerrole->execute();
				
			$adminrole = $GLOBALS['pdo']->prepare("INSERT INTO `group_roles` (`groupid`, `rolename`, `rank`, `AccessGroupWall`, `PostGroupWall`, `DeleteGroupWallPosts`, `PostGroupShout`, `ManageLowerRanks`, `KickLowerRanks`, `AcceptJoinRequests`, `ViewAuditLog`) VALUES (:groupid, 'Admin', '254', '1', '1', '1', '1', '0', '0', '0', '0')");
			$adminrole->bindParam(":groupid", $nextgroup, PDO::PARAM_INT);
			$adminrole->execute();
				
			$memberrole = $GLOBALS['pdo']->prepare("INSERT INTO `group_roles` (`groupid`, `rolename`, `rank`, `AccessGroupWall`, `PostGroupWall`, `DeleteGroupWallPosts`, `PostGroupShout`, `ManageLowerRanks`, `KickLowerRanks`, `AcceptJoinRequests`, `ViewAuditLog`) VALUES (:groupid, 'Member', '253', '1', '1', '0', '0', '0', '0', '0', '0')");
			$memberrole->bindParam(":groupid", $nextgroup, PDO::PARAM_INT);
			$memberrole->execute();
			
			return true;
		}
		catch (Exception $e) //UH OH SOMETHING WENT WRONG
		{
			giveCurrency(20, $creatorid);
			return "Error Occurred";
		}
	}
}

function updateGeneralConfig($groupid, $description, $approval, $base64emblem) //no changing name after creation!!
{
	if (isGroupOwner($groupid))
	{
		$description = cleanInput($description);
		$approval = boolval($approval);
		if ($base64emblem)
		{
			$base64emblem = file_get_contents($base64emblem); //this removes the header from js post and base64 decodes it, very convenient
			$mimetype = finfo_buffer(finfo_open(), $base64emblem, FILEINFO_MIME_TYPE); //file type
		}
		
		if (getGroupDescription($groupid) != $description) //dont run if group description hasnt changed
		{
			if (strlen($description) > 1024)
			{
				return "Group description too long";
			}
			else if (strlen($description) < 3)
			{
				return "Group description too short";
			}
		}
		
		if (!is_bool($approval))
		{
			return "Error occurred";
		}
		
		if ($base64emblem)
		{
			if (!in_array($mimetype, array('image/png','image/jpeg')))
			{
				return "Invalid image provided";
			}
				
			$textureUploadDirectory = $GLOBALS['thumbnailCDNPath']; //directory where the textures are stored
			$emblemhash = genAssetHash(16);
					
			//check dimensions
			$imagedetails = getimagesizefromstring($base64emblem);
					
			$img = imagecreatefromstring($base64emblem);
			$width = imagesx($img);
			$height = imagesy($img);
			$tmp = imagecreatetruecolor(150, 150);
			imagealphablending($tmp , false);
			imagesavealpha($tmp , true);
			imagecopyresampled($tmp, $img, 0, 0, 0, 0, 150, 150, $width, $height);
			if (!imagepng($tmp, $textureUploadDirectory . $emblemhash)) {
				return "Error occurred";
			}
			
			$creatorid = $GLOBALS['user']->id;

			$assetname = getGroupName($groupid) . " Emblem";
					
			$GLOBALS['pdo']->exec("LOCK TABLES assets WRITE"); //lock since this stuff is sensitive
					
			$b = $GLOBALS['pdo']->prepare("SELECT * FROM assets");
			$b->execute();
														
			//grab auto increment values
			$autoincrement = $b->rowCount() + 1; //initial auto increment value
					
			//add texture to assets
			$x = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(`id`, `AssetTypeId`, `Name`, `Description`, `Created`, `Updated`, `CreatorId`, `TargetId`, `PriceInAlphabux`, `Sales`, `IsNew`, `IsForSale`, `IsPublicDomain`, `IsLimited`, `IsLimitedUnique`, `IsApproved`, `Remaining`, `MinimumMembershipLevel`, `ContentRatingTypeId`, `Favorited`, `Visited`, `MaxPlayers`, `UpVotes`, `DownVotes`, `Hash`) VALUES (:aid,22,:aname,'Group Emblem',UNIX_TIMESTAMP(),UNIX_TIMESTAMP(),:oid,:aid2,0,0,0,0,1,0,0,0,0,0,0,0,0,0,0,0,:hash)");
			$x->bindParam(":aid", $autoincrement, PDO::PARAM_INT);
			$x->bindParam(":aname", $assetname, PDO::PARAM_STR);
			$x->bindParam(":oid", $creatorid, PDO::PARAM_INT);
			$x->bindParam(":aid2", $autoincrement, PDO::PARAM_INT);
			$x->bindParam(":hash", $emblemhash, PDO::PARAM_STR);
			$x->execute();
					
			$GLOBALS['pdo']->exec("UNLOCK TABLES"); 
		}
		
		$configgroup = $GLOBALS['pdo']->prepare("UPDATE groups SET description = :description, manualapproval = :approval" . (!empty($base64emblem) ? " ,emblem = ".$autoincrement."":"") . " WHERE id = :gid");
		$configgroup->bindParam(":gid", $groupid, PDO::PARAM_INT);
		$configgroup->bindParam(":description", $description, PDO::PARAM_STR);
		$configgroup->bindParam(":approval", $approval, PDO::PARAM_INT);
		$configgroup->execute();
			
		return true;
	}
	return "No permission";
}

function updateRole($groupid, $rank, $newrank, $name, $accessgroupwall, $postgroupwall, $deletegroupwallposts, $postgroupshout, $managelowerranks, $kicklowerranks, $acceptjoinrequests, $auditaccess)
{
	if (!$groupid || !is_int($groupid) || !$rank || !is_int($rank))
	{
		return "Error Occurred";
	}
	else
	{
		if (isGroupOwner($groupid))
		{
			$grouproles = $GLOBALS['pdo']->prepare("SELECT * FROM group_roles WHERE groupid = :gid AND rank = :rank");
			$grouproles->bindParam(":gid", $groupid, PDO::PARAM_INT);
			$grouproles->bindParam(":rank", $rank, PDO::PARAM_INT);
			$grouproles->execute();
			if ($grouproles->rowCount() > 0)
			{
				$grouproles = $grouproles->fetch(PDO::FETCH_OBJ);
			
				if (!$name)
				{
					$name = $grouproles->rolename;
				}
				else
				{
					if (strlen($name) > 30)
					{
						return "Role name too long";
					}
					else if (strlen($name) < 3)
					{
						return "Role name too short";
					}
				}
				
				if ($grouproles->rank == $newrank || !$newrank || !is_int($newrank) || $grouproles->rank == 255)
				{
					$newrank = $grouproles->rank;
				}
				else
				{
					if (rankExists($groupid, $newrank))
					{
						return "Rank " . $newrank . " already exists";
					}
					else if ($newrank == 255)
					{
						return "Rank 255 is reserved for the Owner of the group";
					}
					else if ($newrank < 1 || $newrank > 254)
					{
						return "Invalid Rank";
					}
				}
				
				if (!is_bool($accessgroupwall) || $grouproles->rank == 255)
				{
					$accessgroupwall = $grouproles->AccessGroupWall;
				}
				if (!is_bool($postgroupwall) || $grouproles->rank == 255)
				{
					$postgroupwall = $grouproles->PostGroupWall;
				}
				if (!is_bool($deletegroupwallposts) || $grouproles->rank == 255)
				{
					$deletegroupwallposts = $grouproles->DeleteGroupWallPosts;
				}
				if (!is_bool($postgroupshout) || $grouproles->rank == 255)
				{
					$postgroupshout = $grouproles->PostGroupShout;
				}
				if (!is_bool($managelowerranks) || $grouproles->rank == 255)
				{
					$managelowerranks = $grouproles->ManageLowerRanks;
				}
				if (!is_bool($kicklowerranks) || $grouproles->rank == 255)
				{
					$kicklowerranks = $grouproles->KickLowerRanks;
				}
				if (!is_bool($acceptjoinrequests) || $grouproles->rank == 255)
				{
					$acceptjoinrequests = $grouproles->AcceptJoinRequests;
				}
				if (!is_bool($auditaccess) || $grouproles->rank == 255)
				{
					$auditaccess = $grouproles->ViewAuditLog;
				}
				
				$updaterole = $GLOBALS['pdo']->prepare("UPDATE group_roles SET rolename = :rolename, rank = :newrank, AccessGroupWall = :groupwallaccess, PostGroupWall = :postgroupwall, DeleteGroupWallPosts = :deletegroupwallposts, PostGroupShout = :postgroupshout, ManageLowerRanks = :managelowerranks, KickLowerRanks = :kicklowerranks, AcceptJoinRequests = :acceptjoinrequest, ViewAuditLog = :viewauditlog WHERE groupid = :gid AND rank = :rank");
				$updaterole->bindParam(":rolename", $name, PDO::PARAM_STR);
				$updaterole->bindParam(":newrank", $newrank, PDO::PARAM_INT);
				$updaterole->bindParam(":groupwallaccess", $accessgroupwall, PDO::PARAM_INT);
				$updaterole->bindParam(":postgroupwall", $postgroupwall, PDO::PARAM_INT);
				$updaterole->bindParam(":deletegroupwallposts", $deletegroupwallposts, PDO::PARAM_INT);
				$updaterole->bindParam(":postgroupshout", $postgroupshout, PDO::PARAM_INT);
				$updaterole->bindParam(":managelowerranks", $managelowerranks, PDO::PARAM_INT);
				$updaterole->bindParam(":kicklowerranks", $kicklowerranks, PDO::PARAM_INT);
				$updaterole->bindParam(":acceptjoinrequest", $acceptjoinrequests, PDO::PARAM_INT);
				$updaterole->bindParam(":viewauditlog", $auditaccess, PDO::PARAM_INT);
				$updaterole->bindParam(":gid", $groupid, PDO::PARAM_INT);
				$updaterole->bindParam(":rank", $rank, PDO::PARAM_INT);
				if ($updaterole->execute())
				{
					return true;
				}
				return "Error occurred";
			}
			return "Group rank doesn't exist";
		}
		return "No permission";
	}
}

function updateUserRank($groupid, $userid, $rank)
{
	$localplayer = $GLOBALS['user']->id;
	
	if (!$groupid || !is_int($groupid) || !$rank || !is_int($rank) || !$userid || !is_int($userid))
	{
		return "Error occurred";
	}
	else if (!manageLowerRankPermission($groupid) || getRank($userid, $groupid) >= getRank($localplayer, $groupid))
	{
		return "No permission";
	}
	else if (getRank($userid, $groupid) == 255)
	{
		return "Cannot change rank of group Owner";
	}
	else if ($rank == 255)
	{
		return "Rank 255 is reserved for the Owner of the group";
	}
	else if (!rankExists($groupid, $rank))
	{
		return "Rank " . $rank . " doesn't exist";
	}
	else
	{
		$updateuser = $GLOBALS['pdo']->prepare("UPDATE `group_members` SET rank = :rank WHERE userid = :userid AND groupid = :groupid");
		$updateuser->bindParam(":rank", $rank, PDO::PARAM_INT);
		$updateuser->bindParam(":userid", $userid, PDO::PARAM_INT);
		$updateuser->bindParam(":groupid", $groupid, PDO::PARAM_INT);
		$updateuser->execute();
		if ($updateuser->rowCount() > 0)
		{
			return true;
		}
		return "Error occurred";
	}
}

function exileUser($groupid, $userid)
{
	if (isGroupOwner($groupid))
	{
		if (isGroupMember($userid, $groupid))
		{
			if (getRank($userid, $groupid) != 255)
			{
				$deleteuser = $GLOBALS['pdo']->prepare("DELETE FROM group_members WHERE userid = :userid AND groupid = :groupid");
				$deleteuser->bindParam(":userid", $userid, PDO::PARAM_INT);
				$deleteuser->bindParam(":groupid", $groupid, PDO::PARAM_INT);
				$deleteuser->execute();
				if ($deleteuser->rowCount() > 0)
				{
					return true;
				}
			}
			return "Error occurred";
		}
		return "Member doesn't exist";	
	}
	return "No permission";	
}

function leaveGroup($groupid)
{
	$localplayer = $GLOBALS['user']->id;
	
	if (!isGroupOwner($groupid) && isGroupMember($localplayer, $groupid) && !isPendingRequest($groupid))
	{
		$deletegroupuser = $GLOBALS['pdo']->prepare("DELETE FROM group_members WHERE userid = :userid AND groupid = :groupid");
		$deletegroupuser->bindParam(":userid", $localplayer, PDO::PARAM_INT);
		$deletegroupuser->bindParam(":groupid", $groupid, PDO::PARAM_INT);
		$deletegroupuser->execute();
		if ($deletegroupuser->rowCount() > 0)
		{
			return true;
		}
	}
	return "Error occurred";
}

function performJoinGroup($groupid, $userid) //performs actual joining group
{
	$getrole = $GLOBALS['pdo']->prepare("SELECT * FROM `group_roles` WHERE groupid = :groupid ORDER BY rank ASC LIMIT 1"); //lowest rank available
	$getrole->bindParam(":groupid", $groupid, PDO::PARAM_INT);
	$getrole->execute();
	if ($getrole->rowCount() > 0)
	{
		$getrole = $getrole->fetch(PDO::FETCH_OBJ)->rank;
		
		$join = $GLOBALS['pdo']->prepare("INSERT INTO group_members(userid, groupid, rank, whenJoined) VALUES(:userid, :groupid, :rank, UNIX_TIMESTAMP())");
		$join->bindParam(":userid", $userid, PDO::PARAM_INT);
		$join->bindParam(":groupid", $groupid, PDO::PARAM_INT);
		$join->bindParam(":rank", $getrole, PDO::PARAM_INT);
		$join->execute();
		if ($join->rowCount() > 0)
		{
			return true;
		}
	}
	return false;
}

function attemptJoinGroup($groupid) //called by the API
{
	$localplayer = $GLOBALS['user']->id;
	
	if (groupExists($groupid))
	{
		if (!isGroupMember($localplayer, $groupid) && !isPendingRequest($groupid))
		{
			if (isManualApproval($groupid))
			{
				//handle manual approvals
				if (newJoinRequest($groupid))
				{
					return true;
				}
			}
			else
			{
				//handle joining without approval
				if (performJoinGroup($groupid, $localplayer))
				{
					return true;
				}
			}
		}
		return "Already Joined";
	}
	return "Failure joining group";		
}

function deletePost($postid, $groupid)
{
	if (wallDeletePermission($groupid))
	{
		$deletepost = $GLOBALS['pdo']->prepare("DELETE FROM group_posts WHERE id = :id AND groupid = :groupid"); //lowest rank available
		$deletepost->bindParam(":id", $postid, PDO::PARAM_INT);
		$deletepost->bindParam(":groupid", $groupid, PDO::PARAM_INT);
		$deletepost->execute();
		if ($deletepost->rowCount() > 0)
		{
			return true;
		}
		return "Post not found";
	}
	return "No permission";
}

function deleteRequest($groupid, $userid)
{
	$deleterequest = $GLOBALS['pdo']->prepare("DELETE FROM group_join_requests WHERE groupid = :groupid AND userid = :userid");
	$deleterequest->bindParam(":groupid", $groupid, PDO::PARAM_INT);
	$deleterequest->bindParam(":userid", $userid, PDO::PARAM_INT);
	$deleterequest->execute();
	if ($deleterequest->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function denyRequest($groupid, $userid)
{
	if (isGroupOwner($groupid))
	{
		if (deleteRequest($groupid, $userid))
		{
			return true;
		}
	}
	return "Error occurred";
}

function approveRequest($groupid, $userid)
{
	if (isGroupOwner($groupid))
	{
		if (deleteRequest($groupid, $userid))
		{
			if (performJoinGroup($groupid, $userid))
			{
				return true;
			}
		}
	}
	return "Error occurred";
}

function newJoinRequest($groupid)
{
	$localplayer = $GLOBALS['user']->id;
	
	$newrequest = $GLOBALS['pdo']->prepare("INSERT INTO `group_join_requests`(`groupid`, `userid`, `whenRequested`) VALUES (:groupid, :userid, UNIX_TIMESTAMP())");
	$newrequest->bindParam(":groupid", $groupid, PDO::PARAM_INT);
	$newrequest->bindParam(":userid", $localplayer, PDO::PARAM_INT);
	$newrequest->execute();
	if ($newrequest->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function createRole($groupid, $name, $rank)
{
	if (!$groupid || !is_int($groupid) || !$rank || !is_int($rank))
	{
		return "Error occurred";
	}
	else if (!$rank || !is_int($rank) || $rank < 0)
	{
		return "Invalid rank";
	}
	else if (!isGroupOwner($groupid))
	{
		return "No permission";
	}
	else if ($rank == 255)
	{
		return "Rank 255 is reserved for the Owner of the group";
	}
	else if (rankExists($groupid, $rank))
	{
		return "Rank " . $rank . " already exists";
	}
	else if (strlen($name) > 30)
	{
		return "Role name too long";
	}
	else if (strlen($name) < 3)
	{
		return "Role name too short";
	}
	else 
	{
		$interval = 0;
		$intervalcheck = $GLOBALS['pdo']->prepare("SELECT * FROM group_roles WHERE groupid = :groupid ORDER BY whenCreated DESC LIMIT 1");
		$intervalcheck->bindParam(":groupid", $groupid, PDO::PARAM_INT);
		$intervalcheck->execute();
			
		if ($intervalcheck->rowCount() > 0) //we dont want to be calling an object that is NULL
		{
			$interval = (int)$intervalcheck->fetch(PDO::FETCH_OBJ)->whenCreated;
		}
			
		if(($interval + (60)) < time()) //60 second interval
		{
			$name = cleanInput($name);
			
			$newrole = $GLOBALS['pdo']->prepare("INSERT INTO `group_roles` (`groupid`, `rolename`, `rank`, `AccessGroupWall`, `PostGroupWall`, `DeleteGroupWallPosts`, `PostGroupShout`, `ManageLowerRanks`, `KickLowerRanks`, `AcceptJoinRequests`, `ViewAuditLog`, `whenCreated`) VALUES (:groupid, :rolename, :rank, '1', '1', '0', '0', '0', '0', '0', '0', UNIX_TIMESTAMP())");
			$newrole->bindParam(":groupid", $groupid, PDO::PARAM_INT);
			$newrole->bindParam(":rolename", $name, PDO::PARAM_STR);
			$newrole->bindParam(":rank", $rank, PDO::PARAM_INT);
			$newrole->execute();
			if ($newrole->rowCount() > 0)
			{
				removeCurrency(15, "Purchase of role groupid ".$groupid);
				return true;
			}
		}
		return "Please wait before creating another role";
	}
	return "Error occurred";	
}

function rankExists($groupid, $rank)
{
	$role = $GLOBALS['pdo']->prepare("SELECT * FROM group_roles WHERE groupid = :groupid AND rank = :rank");
	$role->bindParam(":groupid", $groupid, PDO::PARAM_INT);
	$role->bindParam(":rank", $rank, PDO::PARAM_INT);
	$role->execute();
	if ($role->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function groupExists($groupid)
{
	$group = $GLOBALS['pdo']->prepare("SELECT * FROM groups WHERE id = :u");
	$group->bindParam(":u", $groupid, PDO::PARAM_INT);
	$group->execute();
	if ($group->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function groupNameExists($name)
{
	$checkname = $GLOBALS['pdo']->prepare("SELECT * FROM groups WHERE name = :na");
	$checkname->bindParam(":na", $name, PDO::PARAM_STR);
	$checkname->execute();
	if ($checkname->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function getGroupName($id)
{
	$name = $GLOBALS['pdo']->prepare("SELECT * FROM groups WHERE id = :u");
	$name->bindParam(":u", $id, PDO::PARAM_INT);
	$name->execute();
	$name = $name->fetch(PDO::FETCH_OBJ);
	return $name->name;
}

function getRankName($rank, $groupid)
{
	$name = $GLOBALS['pdo']->prepare("SELECT * FROM group_roles WHERE groupid = :gid AND rank = :rank");
	$name->bindParam(":gid", $groupid, PDO::PARAM_INT);
	$name->bindParam(":rank", $rank, PDO::PARAM_INT);
	$name->execute();
	return $name->fetch(PDO::FETCH_OBJ)->rolename;
}

function getUserRankName($userid, $groupid)
{
	return getRankName(groupMemberInfo($groupid, $userid)->rank, $groupid);
}

function getRank($userid, $groupid)
{
	if (isGroupMember($userid, $groupid))
	{
		return groupMemberInfo($groupid, $userid)->rank;
	}
}

function getGroupDescription($id)
{
	$name = $GLOBALS['pdo']->prepare("SELECT * FROM groups WHERE id = :u");
	$name->bindParam(":u", $id, PDO::PARAM_INT);
	$name->execute();
	$name = $name->fetch(PDO::FETCH_OBJ);
	return $name->description;
}

function groupMemberCount($groupid)
{
	$count = $GLOBALS['pdo']->prepare("SELECT * FROM group_members WHERE groupid = :gid");
	$count->bindParam(":gid", $groupid, PDO::PARAM_INT);
	$count->execute();
	return $count->rowCount();
}

function rankMemberCount($groupid, $rank)
{
	$count = $GLOBALS['pdo']->prepare("SELECT * FROM group_members WHERE groupid = :gid AND rank = :r");
	$count->bindParam(":gid", $groupid, PDO::PARAM_INT);
	$count->bindParam(":r", $rank, PDO::PARAM_INT);
	$count->execute();
	return $count->rowCount();
}

function isGroupMember($userid, $groupid)
{
	$member = $GLOBALS['pdo']->prepare("SELECT * FROM group_members WHERE userid = :uid AND groupid = :gid");
	$member->bindParam(":uid", $userid, PDO::PARAM_INT);
	$member->bindParam(":gid", $groupid, PDO::PARAM_INT);
	$member->execute();
	
	if ($member->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function groupMemberInfo($groupid, $userid)
{
	$member = $GLOBALS['pdo']->prepare("SELECT * FROM group_members WHERE userid = :uid AND groupid = :gid");
	$member->bindParam(":uid", $userid, PDO::PARAM_INT);
	$member->bindParam(":gid", $groupid, PDO::PARAM_INT);
	$member->execute();
	return $member->fetch(PDO::FETCH_OBJ);
}

function groupRoleInfo($groupid, $rank)
{
	$role = $GLOBALS['pdo']->prepare("SELECT * FROM group_roles WHERE groupid = :gid AND rank = :r");
	$role->bindParam(":gid", $groupid, PDO::PARAM_INT);
	$role->bindParam(":r", $rank, PDO::PARAM_INT);
	$role->execute();
	return $role->fetch(PDO::FETCH_OBJ);
}

function isGroupOwner($groupid)
{
	$localplayer = $GLOBALS['user']->id;
	
	$owner = $GLOBALS['pdo']->prepare("SELECT * FROM groups WHERE id = :gid AND creatorid = :cid");
	$owner->bindParam(":gid", $groupid, PDO::PARAM_INT);
	$owner->bindParam(":cid", $localplayer, PDO::PARAM_INT);
	$owner->execute();
	if ($owner->rowCount() > 0 || $GLOBALS['user']->isAdmin())
	{
		return true;
	}
	return false;
}

function isPendingRequest($groupid)
{
	$localplayer = $GLOBALS['user']->id;
	
	$pending = $GLOBALS['pdo']->prepare("SELECT * FROM group_join_requests WHERE groupid = :gid AND userid = :uid");
	$pending->bindParam(":gid", $groupid, PDO::PARAM_INT);
	$pending->bindParam(":uid", $localplayer, PDO::PARAM_INT);
	$pending->execute();
	if ($pending->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function isManualApproval($groupid)
{
	$manual = $GLOBALS['pdo']->prepare("SELECT * FROM groups WHERE id = :gid AND manualapproval = 1");
	$manual->bindParam(":gid", $groupid, PDO::PARAM_INT);
	$manual->execute();
	if ($manual->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function submitPost($groupid, $post)
{
	$post = cleanInput($post);
	$interval = 0;
	$localuser = $GLOBALS['user']->id;
	
	if (wallPostPermission($groupid))
	{
		$intervalcheck = $GLOBALS['pdo']->prepare("SELECT * FROM group_posts WHERE userid = :u ORDER BY postdate DESC LIMIT 1");
		$intervalcheck->bindParam(":u", $localuser, PDO::PARAM_INT);
		$intervalcheck->execute();
			
		if ($intervalcheck->rowCount() > 0) //we dont want to be calling an object that is NULL
		{
			$interval = (int)$intervalcheck->fetch(PDO::FETCH_OBJ)->postdate;
		}
			
		if(($interval + (60)) < time()) //60 second interval
		{
			if(strlen($post) < 4)
			{
				return "Post too short, must be above 4 Characters";
			}
			elseif(strlen($post) > 256)
			{
				return "Post too long, must be under 256 Characters";
			}
			else
			{
				$newpost = $GLOBALS['pdo']->prepare("INSERT INTO group_posts(userid, groupid, post, postdate) VALUES(:u, :gid, :p, UNIX_TIMESTAMP())");
				$newpost->bindParam(":u", $localuser, PDO::PARAM_INT);
				$newpost->bindParam(":gid", $groupid, PDO::PARAM_INT);
				$newpost->bindParam(":p", $post, PDO::PARAM_INT);
				if ($newpost->execute())
				{
					return true;
				}
				return "An error has occurred";
			}
		}
		return "Please wait before posting again";
	}
	return "No permission to post";
}

function wallViewPermission($groupid)
{
	$localplayer = $GLOBALS['user']->id;
	
	if (isGroupMember($localplayer, $groupid))
	{
		$role = groupRoleInfo($groupid, groupMemberInfo($groupid, $localplayer)->rank);
		
		if ($role->AccessGroupWall == 1 || $GLOBALS['user']->isAdmin())
		{
			return true;
		}	
	}
	return false;
}

function wallPostPermission($groupid)
{
	$localplayer = $GLOBALS['user']->id;
	
	if (isGroupMember($localplayer, $groupid))
	{
		$role = groupRoleInfo($groupid, groupMemberInfo($groupid, $localplayer)->rank);
		
		if ($role->PostGroupWall == 1 || $GLOBALS['user']->isAdmin())
		{
			return true;
		}	
	}
	return false;
}

function wallDeletePermission($groupid)
{
	$localplayer = $GLOBALS['user']->id;
	
	if (isGroupMember($localplayer, $groupid))
	{
		$role = groupRoleInfo($groupid, groupMemberInfo($groupid, $localplayer)->rank);
		
		if ($role->DeleteGroupWallPosts == 1 || $GLOBALS['user']->isAdmin())
		{
			return true;
		}	
	}
	return false;
}

function postShoutPermission($groupid)
{
	$localplayer = $GLOBALS['user']->id;
	
	if (isGroupMember($localplayer, $groupid))
	{
		$role = groupRoleInfo($groupid, groupMemberInfo($groupid, $localplayer)->rank);
		
		if ($role->PostGroupShout == 1 || $GLOBALS['user']->isAdmin())
		{
			return true;
		}	
	}
	return false;
}

function manageLowerRankPermission($groupid)
{
	$localplayer = $GLOBALS['user']->id;
	
	if (isGroupMember($localplayer, $groupid))
	{
		$role = groupRoleInfo($groupid, groupMemberInfo($groupid, $localplayer)->rank);
		
		if ($role->ManageLowerRanks == 1 || $GLOBALS['user']->isAdmin())
		{
			return true;
		}	
	}
	return false;
}

function kickLowerRankPermission($groupid)
{
	$localplayer = $GLOBALS['user']->id;
	
	if (isGroupMember($localplayer, $groupid))
	{
		$role = groupRoleInfo($groupid, groupMemberInfo($groupid, $localplayer)->rank);
		
		if ($role->KickLowerRanks == 1 || $GLOBALS['user']->isAdmin())
		{
			return true;
		}	
	}
	return false;
}

function acceptJoinRequestPermission($groupid)
{
	$localplayer = $GLOBALS['user']->id;
	
	if (isGroupMember($localplayer, $groupid))
	{
		$role = groupRoleInfo($groupid, groupMemberInfo($groupid, $localplayer)->rank);
		
		if ($role->AcceptJoinRequests == 1 || $GLOBALS['user']->isAdmin())
		{
			return true;
		}	
	}
	return false;
}

function viewAuditLogPermission($groupid)
{
	$localplayer = $GLOBALS['user']->id;
	
	if (isGroupMember($localplayer, $groupid))
	{
		$role = groupRoleInfo($groupid, groupMemberInfo($groupid, $localplayer)->rank);
		
		if ($role->ViewAuditLog == 1 || $GLOBALS['user']->isAdmin())
		{
			return true;
		}	
	}
	return false;
}

function configPermission($groupid)
{
	$localplayer = $GLOBALS['user']->id;
	
	if (isGroupMember($localplayer, $groupid) || $GLOBALS['user']->isAdmin())
	{
		if (manageLowerRankPermission($groupid) || kickLowerRankPermission($groupid) || acceptJoinRequestPermission($groupid) || viewAuditLogPermission($groupid) || $GLOBALS['user']->isAdmin())
		{
			return true;
		}
	}
	return false;
}

// ...

//game utility functions 

function generateClientTicket($userid, $accountage, $username, $characterappearance, $jobid) //generates a client ticket with the provided data, this is later verified on RCC preventing any important info being spoofed
{
	$timestamp = date("m/d/Y h:m:s A", time()); //timestamp for the client ticket
	$sig1 = signData($userid . "\n" . $accountage . "\n" . $username . "\n" . $characterappearance . "\n" . $jobid . "\n" . $timestamp, false);
	$sig2 = signData($userid . "\n" . $jobid . "\n" . $timestamp, false);
	return $timestamp.";".$sig1.";".$sig2; //proper format for the timestamp and signatures
}

function userAccessToGame($placeid, $userid)
{
	if (getAssetInfo($placeid)->isGameWhitelisted == 1) //game whitelisted
	{
		$whitelist = $GLOBALS['pdo']->prepare("SELECT * FROM game_access WHERE placeid = :pid AND userid = :uid");
		$whitelist->bindParam(":pid", $placeid, PDO::PARAM_INT);
		$whitelist->bindParam(":uid", $userid, PDO::PARAM_INT);
		$whitelist->execute();
		if ($whitelist->rowCount() > 0 || $userid == getAssetInfo($placeid)->CreatorId || $GLOBALS['user']->isAdmin())
		{
			return true;
		}
		return false;
	}
	return true;
}

function checkForDeadJobs($placeid)
{
	$jobinfo = $GLOBALS['pdo']->prepare("SELECT * FROM open_servers WHERE gameID = :g AND (lastPing + 95) < UNIX_TIMESTAMP() AND (status = 0 OR status = 1)"); 
	$jobinfo->bindParam(":g", $placeid, PDO::PARAM_INT);
	$jobinfo->execute();

	foreach ($jobinfo as $job)
	{
		$editjob = $GLOBALS['pdo']->prepare("UPDATE open_servers SET status = 2, killedby = 0, whenDied = UNIX_TIMESTAMP() WHERE jobid = :j"); 
		$editjob->bindParam(":j", $job['jobid'], PDO::PARAM_STR);
		$editjob->execute();
	}
}

function isJobMarkedClosed($jobid)
{
	$job = $GLOBALS['pdo']->prepare("SELECT * FROM open_servers WHERE jobid = :j AND status = 2");
	$job->bindParam(":j", $jobid, PDO::PARAM_STR);
	$job->execute();
	
	if ($job->rowCount() > 0)
	{
		return true;
	}
	return false;
}

//end game utility functions

//render utility functions

function setHeadshotAngleRight($userid)
{
	$right = $GLOBALS['pdo']->prepare('UPDATE users SET headshotAngleRight = 1, headshotAngleLeft = 0 WHERE id = :uid');
	$right->bindParam(":uid", $userid, PDO::PARAM_INT);
	$right->execute();
	if ($right->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function setHeadshotAngleLeft($userid)
{
	$left = $GLOBALS['pdo']->prepare('UPDATE users SET headshotAngleRight = 0, headshotAngleLeft = 1 WHERE id = :uid');
	$left->bindParam(":uid", $userid, PDO::PARAM_INT);
	$left->execute();
	if ($left->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function setHeadshotAngleCenter($userid)
{
	$center = $GLOBALS['pdo']->prepare('UPDATE users SET headshotAngleRight = 0, headshotAngleLeft = 0 WHERE id = :uid');
	$center->bindParam(":uid", $userid, PDO::PARAM_INT);
	$center->execute();
	if ($center->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function wearingAssets($userid) //returns wearing asset list separated by ;
{
	$wearingitems = $GLOBALS['pdo']->prepare('SELECT * FROM wearing_items WHERE uid = :uid ORDER BY aid ASC'); //wearing items from lowest to highest (EZ)
	$wearingitems->bindParam(":uid", $userid, PDO::PARAM_INT);
	$wearingitems->execute();
	
	$iter = 0;
	$wearingassets = "";
	foreach($wearingitems as $item)
	{
		$iter += 1;
		$wearingassets .= ($iter == $wearingitems->rowCount()) ? $item['aid'] : $item['aid'] . ';';
	}
	return $wearingassets;
}

function rerenderutility()
{
	$localplayer = $GLOBALS['user']->id;
	
	$setrenderstat = $GLOBALS['pdo']->prepare("UPDATE users SET pendingRender = 1, pendingHeadshotRender = 1, renderCount = renderCount+1, lastRender = UNIX_TIMESTAMP(), lastHeadshotRender = UNIX_TIMESTAMP() WHERE id = :u");
	$setrenderstat->bindParam(":u", $localplayer, PDO::PARAM_INT);
	$setrenderstat->execute();	
	UsersRender::RenderPlayer($localplayer);
}

function checkUserPendingRender($player)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :u");
	$check->bindParam(":u", $player, PDO::PARAM_INT);
	$check->execute();
	$checkdata = $check->fetch(PDO::FETCH_OBJ);
	
	$waspendingrender = false;

	if ($checkdata->pendingRender == true) //render pending
	{
		if (($checkdata->lastRender + 15) < time()) //last render still pending after 15 seconds
		{
			$update = $GLOBALS['pdo']->prepare("UPDATE users SET pendingRender = 0 WHERE id = :u");
			$update->bindParam(":u", $player, PDO::PARAM_INT);
			$update->execute();
		}
		else
		{
			$waspendingrender = true;
		}
	}

	if ($checkdata->pendingHeadshotRender == true) //headshot render pending
	{
		if (($checkdata->lastHeadshotRender + 15) < time()) //last render still pending after 15 seconds
		{
			$update = $GLOBALS['pdo']->prepare("UPDATE users SET pendingHeadshotRender = 0 WHERE id = :u");
			$update->bindParam(":u", $player, PDO::PARAM_INT);
			$update->execute();
		}
		else
		{
			$waspendingrender = true;
		}
	}

	return $waspendingrender;
}

//end local user render utility functions

//asset functions

function availableAssetId() {
	$GLOBALS['pdo']->exec("LOCK TABLES assets WRITE");
	$b = $GLOBALS['pdo']->prepare("SELECT * FROM assets");
	$b->execute();
	return $b->rowCount() + 1;
	$GLOBALS['pdo']->exec("UNLOCK TABLES");
}

function createGenericAsset($assetid, $assettypeid, $targetid, $producttype, $name, $description, $creatorid, $price, $onsale, $ispublicdomain, $isapproved, $hash) {
	$GLOBALS['pdo']->exec("LOCK TABLES assets WRITE");

	$asset = $GLOBALS['pdo']->prepare("INSERT INTO assets (id, AssetTypeId, TargetId, ProductType, Name, Description, Created, Updated, CreatorId, PriceInAlphabux, IsForSale, isPublicDomain, isApproved, Hash) VALUES(:id, :AssetTypeId, :TargetId, :ProductType, :Name, :Description, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :CreatorId, :PriceInAlphabux, :IsForSale, :isPublicDomain, :isApproved, :Hash)");
	$asset->bindParam(":id", $assetid, PDO::PARAM_INT);
	$asset->bindParam(":AssetTypeId", $assettypeid, PDO::PARAM_INT);
	$asset->bindParam(":TargetId", $targetid, PDO::PARAM_INT);
	$asset->bindParam(":ProductType", $producttype, PDO::PARAM_STR);
	$asset->bindParam(":Name", $name, PDO::PARAM_STR);
	$asset->bindParam(":Description", $description, PDO::PARAM_STR);
	$asset->bindParam(":CreatorId", $creatorid, PDO::PARAM_INT);
	$asset->bindParam(":isPublicDomain", $ispublicdomain, PDO::PARAM_INT);
	$asset->bindParam(":isApproved", $isapproved, PDO::PARAM_INT);
	$asset->bindParam(":Hash", $hash, PDO::PARAM_STR);
	$asset->bindParam(":PriceInAlphabux", $price, PDO::PARAM_INT);
	$asset->bindParam(":IsForSale", $onsale, PDO::PARAM_INT);
	$asset->execute();

	$GLOBALS['pdo']->exec("UNLOCK TABLES");
}

function convertAssetUrlToId($asseturl) {
	if (strpos($asseturl, "rbxassetid://") !== false) {
		return substr($asseturl, strpos($asseturl, "rbxassetid://")+13, strlen($asseturl));
	} else if (strpos($asseturl, "id=") !== false) {
		return substr($asseturl, strpos($asseturl, "id=")+3, strlen($asseturl));
	}
	return false;
}

function isMeshSupported($meshstr) {
	if (strpos($meshstr, "version 1.00") !== false || strpos($meshstr, "version 1.01") !== false || strpos($meshstr, "version 2.00") !== false) {
		return true;
	}
	return false;
}

function uploadXML($xml, $name, $description, $price, $onsale, $assettypeid, $creatorid) {
	$hash = genAssetHash(16);
	$assetid = availableAssetId();
	$name = cleanInput($name);

	if (file_put_contents($GLOBALS['assetCDNPath'] . $hash, $xml)) {
		createGenericAsset($assetid, $assettypeid, $assetid, "", $name, $description, $creatorid, $price, $onsale, 1, 1, $hash);
		return $assetid;
	}
	return false;
}

function uploadRobloxMesh($name, $assetid, $creatorid) {
	if (getRobloxProductInfo($assetid)->AssetTypeId == 4) {
		$meshstr = file_get_contents('compress.zlib://'.$GLOBALS['ROBLOXAssetAPI'].$assetid);
		if (isMeshSupported($meshstr)) {
			$hash = genAssetHash(16);
			$assetid = availableAssetId();
			$name = cleanInput($name) . " Mesh";

			if (file_put_contents($GLOBALS['assetCDNPath'] . $hash, $meshstr)) {
				createGenericAsset($assetid, 4, $assetid, "", $name, "", $creatorid, 0, 0, 1, 1, $hash);
				return $assetid;
			}
		}
	}
	return false;
}

function uploadRobloxTexture($name, $assetid, $creatorid) {
	if (getRobloxProductInfo($assetid)->AssetTypeId == 1) {
		$texturestr = file_get_contents('compress.zlib://'.$GLOBALS['ROBLOXAssetAPI'].$assetid);
	
		$hash = genAssetHash(16);
		$assetid = availableAssetId();
		$name = cleanInput($name) . " Texture";

		if (file_put_contents($GLOBALS['thumbnailCDNPath'] . $hash, $texturestr)) {
			createGenericAsset($assetid, 1, $assetid, "", $name, "", $creatorid, 0, 0, 1, 1, $hash);
			return $assetid;
		}
	}
	return false;
}

function uploadRobloxAnimation($name, $assetid, $creatorid) {
	if (getRobloxProductInfo($assetid)->AssetTypeId == 24) {
		$animationstr = file_get_contents('compress.zlib://'.$GLOBALS['ROBLOXAssetAPI'].$assetid);
	
		$hash = genAssetHash(16);
		$assetid = availableAssetId();
		$name = cleanInput($name);

		if (file_put_contents($GLOBALS['assetCDNPath'] . $hash, $animationstr)) {
			createGenericAsset($assetid, 24, $assetid, "", $name, "", $creatorid, 0, 0, 1, 1, $hash);
			return $assetid;
		}
	}
	return false;
}

function submitRobloxAssetWorker($requestedassetid, $assettypeid, $assetname, $assetdescription, $price, $onsale) {
	//multiple occasions of the same item being uploaded, double check the name now
	$isduplicate = false;
	$duplicate = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE Name = :n");
	$duplicate->bindParam(":n", $assetname, PDO::PARAM_STR);
	$duplicate->execute();
	if ($duplicate->rowCount() > 0) {
		return "Asset exists with the same name";
	}

	$xml = file_get_contents('compress.zlib://'.$GLOBALS['ROBLOXAssetAPI'].$requestedassetid);

	$validXML = true;
	try {
		$ParsedXML = new SimpleXMLElement($xml);
	} catch (Exception $e) {
		$validXML = false;
	}
						
	if ($validXML) {
		//handle all hat types
		if ($assettypeid == 41 || //HairAccessory
		$assettypeid == 42 || //FaceAccessory
		$assettypeid == 43 || //NeckAccessory
		$assettypeid == 44 || //ShoulderAccessory
		$assettypeid == 45 || //FrontAccessory
		$assettypeid == 46 || //BackAccessory
		$assettypeid == 47) { //WaistAccessory
			$assettypeid = 8; //we want all those variations above just to be a reg hat assettypeid
		}

		if ($assettypeid == 8 ||  //Hat
		$assettypeid == 18 || //Decal/Face
		$assettypeid == 19) { //Gear
			//currently only support texture and meshe auto uploading, TODO: audios and linked sources?

			$xmlhash = genAssetHash(16);

			//find two known xml elements that contain texture urls
			$textures = $ParsedXML->xpath('//Content[@name="TextureId"]/url | //Content[@name="Texture"]/url');

			//find mesh urls
			$meshes = $ParsedXML->xpath('//Content[@name="MeshId"]/url');

			$meshuploadsuccess = true;
			foreach ($meshes as $mesh) {
				$assetid = convertAssetUrlToId($mesh);
				if (!$assetid) {
					return "Unsupported Asset";
				}
				$assetid = uploadRobloxMesh($assetname, $assetid, 1);
				if ($assetid !== FALSE) {
					$xml=str_replace($mesh, $GLOBALS['url'] . "/asset/?id=" . $assetid, $xml);
					Render::RenderMesh($assetid);
				} else {
					$meshuploadsuccess = false;
					break;
				}
			}

			if (!$meshuploadsuccess && $assettypeid != 18) {
				return "Error occurred, one or more invalid Meshes";
			}

			$textureuploadsuccess = true;
			foreach ($textures as $texture) {
				$assetid = convertAssetUrlToId($texture);
				if (!$assetid) {
					return "Unsupported Asset";
				}
				$assetid = uploadRobloxTexture($assetname, $assetid, 1);
				if ($assetid !== FALSE) {
					$xml=str_replace($texture, $GLOBALS['url'] . "/asset/?id=" . $assetid, $xml);
				} else {
					$textureuploadsuccess = false;
					break;
				}
			}

			if (!$textureuploadsuccess) {
				return "Error occurred, one or more invalid Textures. Some meshes may have been uploaded";
			}

			$newassetid = uploadXML($xml, $assetname, $assetdescription, $price, $onsale, $assettypeid, 1);

			if ($newassetid !== FALSE) {
				giveItem(1, $newassetid); //give the user Alphaland the created asset
				$assettypeid = getAssetInfo($newassetid)->AssetTypeId;
			
				switch ($assettypeid) {
					case 8:
						Render::RenderHat($newassetid);
						break;
					case 18:
						Render::RenderFace($newassetid);
						break;
					case 19:
						Render::RenderGear($newassetid);
						break;
					default:
						break;
				}

				//discord bot api
				if ($onsale) {
					WebContextManager::HttpGetPing("localhost:4098/?type=itemrelease&assetid=".$newassetid."&name=".urlencode($assetname)."&description=".urlencode($assetdescription)."&price=".$price."&image=".$GLOBALS['renderCDN']."/".getAssetInfo($newassetid)->ThumbHash, 8000);
				}

				return true;
			}
			return "Error Occurred";
		}
		return "Invalid Asset";
	}
	return "Invalid XML";
}

function isAssetEquipped($assetid, $userid)
{
	$get = $GLOBALS['pdo']->prepare("SELECT * FROM wearing_items WHERE aid = :aid AND uid = :uid");
	$get->bindParam(":aid", $assetid, PDO::PARAM_INT);
	$get->bindParam(":uid", $userid, PDO::PARAM_INT);
	$get->execute();
	if ($get->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function getUserGearsAccoutrements($userid) //ghetto
{
	$gears = "";
	$get = $GLOBALS['pdo']->prepare("SELECT * FROM owned_assets WHERE uid = :u");
	$get->bindParam(":u", $userid, PDO::PARAM_INT);
	$get->execute();
	if($get->rowCount() > 0) {
		foreach($get as $item)  {
			if (getAssetInfo($item['aid'])->AssetTypeId == 19) {
				$equipped = "";
				if (isAssetEquipped($item['aid'], $userid)) {
					$equipped = "&equipped=1";
				}
				$gears .= $GLOBALS['url'].'/asset/?id='.$item['aid'].$equipped.';';
			}
		}
	}
	return $gears;
}

//TODO: FIX THIS DUMB SHIT
//file_get_contents adds &amp after every &
//2 lazy to look further into this right now, annoying as fuck
function getRobloxAssetThumbnail($assetid, $width, $height, $fileformat)
{
	return json_decode(file_get_contents($GLOBALS['ROBLOXAssetThumbnailAPI'].$assetid."&size=".$width."x".$height."&format=".$fileformat."&isCircular=false"))->data[0]->imageUrl;
}

function getRobloxProductInfo($assetid)
{
	$json = file_get_contents($GLOBALS['ROBLOXProductInfoAPI'].$assetid);
	return json_decode($json);
}

function ReturnAssetFromHash($hash) //asset CDN
{
	//alphaland assets cdn
	WebContextManager::Redirect(constructAssetHashUrl($hash));
}

function ReturnThumbnailFromHash($hash) //thumb CDN (images)
{
	//alphaland thumb (images) cdn
	WebContextManager::Redirect(constructThumbnailHashUrl($hash));
}

function CreateAsset($AssetTypeId, $IconImageAssetId, $TargetId, $ProductType, $Name, $Description, $Created, $Updated, $CreatorId, $PriceInAlphabux, $Sales, $isPersonalServer, $IsNew, $IsForSale, $IsPublicDomain, $IsLimited, $IsLimitedUnique, $IsCommentsEnabled, $IsApproved, $IsModerated, $Remaining, $MinimumMembershipLevel, $ContentRatingTypeId, $Favorited, $Visited, $MaxPlayers, $UpVotes, $DownVotes, $Hash, $ThumbHash)
{
	//setup the new asset in the DB, lock it!
	$GLOBALS['pdo']->exec("LOCK TABLES assets WRITE");
	
	//assets increment
	$autoincrement = $GLOBALS['pdo']->prepare("SELECT * FROM assets");
	$autoincrement->execute();
	$autoincrement = $autoincrement->rowCount() + 1; //initial auto increment value
	
	//db entry
	$m = $GLOBALS['pdo']->prepare("INSERT INTO `assets`(Id, AssetTypeId, IconImageAssetId, TargetId, ProductType, Name, Description, Created, Updated, CreatorId, PriceInAlphabux, Sales, isPersonalServer, IsNew, IsForSale, IsPublicDomain, IsLimited, IsLimitedUnique, IsCommentsEnabled, IsApproved, IsModerated, Remaining, MinimumMembershipLevel, ContentRatingTypeId, Favorited, Visited, MaxPlayers, UpVotes, DownVotes,Hash,ThumbHash) VALUES (:Id, :AssetTypeId, :IconImageAssetId, :TargetId, :ProductType, :Name, :Description, :Created, :Updated, :CreatorId, :PriceInAlphabux, :Sales, :isPersonalServer, :IsNew, :IsForSale, :IsPublicDomain, :IsLimited, :IsLimitedUnique, :IsCommentsEnabled, :IsApproved, :IsModerated, :Remaining, :MinimumMembershipLevel, :ContentRatingTypeId, :Favorited, :Visited, :MaxPlayers, :UpVotes, :DownVotes, :Hash, :ThumbHash)");		
	$m->bindParam(":Id", $autoincrement, PDO::PARAM_INT);
	$m->bindParam(":AssetTypeId", $AssetTypeId, PDO::PARAM_INT);
	$m->bindParam(":IconImageAssetId", $IconImageAssetId, PDO::PARAM_INT);
	$m->bindParam(":TargetId", $autoincrement, PDO::PARAM_INT); //im not sure what to use this for so for now just use autoincrement
	$m->bindParam(":ProductType", $ProductType, PDO::PARAM_STR);
	$m->bindParam(":Name", $Name, PDO::PARAM_STR);
	$m->bindParam(":Description", $Description, PDO::PARAM_STR);
	$m->bindParam(":Created", $Created, PDO::PARAM_INT);
	$m->bindParam(":Updated", $Updated, PDO::PARAM_INT);
	$m->bindParam(":CreatorId", $CreatorId, PDO::PARAM_INT);
	$m->bindParam(":PriceInAlphabux", $PriceInAlphabux, PDO::PARAM_INT);
	$m->bindParam(":Sales", $Sales, PDO::PARAM_INT);
	$m->bindParam(":isPersonalServer", $isPersonalServer, PDO::PARAM_INT);
	$m->bindParam(":IsNew", $IsNew, PDO::PARAM_INT);
	$m->bindParam(":IsForSale", $IsForSale, PDO::PARAM_INT);
	$m->bindParam(":IsPublicDomain", $IsPublicDomain, PDO::PARAM_INT);
	$m->bindParam(":IsLimited", $IsLimited, PDO::PARAM_INT);
	$m->bindParam(":IsLimitedUnique", $IsLimitedUnique, PDO::PARAM_INT);
	$m->bindParam(":IsCommentsEnabled", $IsCommentsEnabled, PDO::PARAM_INT);
	$m->bindParam(":IsApproved", $IsApproved, PDO::PARAM_INT);
	$m->bindParam(":IsModerated", $IsModerated, PDO::PARAM_INT);
	$m->bindParam(":Remaining", $Remaining, PDO::PARAM_INT);
	$m->bindParam(":MinimumMembershipLevel", $MinimumMembershipLevel, PDO::PARAM_INT);
	$m->bindParam(":ContentRatingTypeId", $ContentRatingTypeId, PDO::PARAM_INT);
	$m->bindParam(":Favorited", $Favorited, PDO::PARAM_INT);
	$m->bindParam(":Visited", $Visited, PDO::PARAM_INT);
	$m->bindParam(":MaxPlayers", $MaxPlayers, PDO::PARAM_INT);
	$m->bindParam(":UpVotes", $UpVotes, PDO::PARAM_INT);
	$m->bindParam(":DownVotes", $DownVotes, PDO::PARAM_INT);
	$m->bindParam(":Hash", $Hash, PDO::PARAM_STR);
	$m->bindParam(":ThumbHash", $ThumbHash, PDO::PARAM_STR);
	$m->execute();
	
	$GLOBALS['pdo']->exec("UNLOCK TABLES"); //unlock since we are done with sensitive asset stuff
	
	return $autoincrement;
}

function setAssetModerated($id)
{
	$moderate = $GLOBALS['pdo']->prepare("UPDATE assets SET IsModerated = 1, IsApproved = 0, IsForSale = 0 WHERE id = :i");
	$moderate->bindParam(":i", $id, PDO::PARAM_INT);
	$moderate->execute();
}

function setAssetApproved($id)
{
	$approve = $GLOBALS['pdo']->prepare("UPDATE assets SET IsApproved = 1, IsModerated = 0 WHERE id = :i");
	$approve->bindParam(":i", $id, PDO::PARAM_INT);
	$approve->execute();
}

function approveAsset($id) //currently supports t-shirts, shirts and pants
{
	//the logic behind this is it uses the asset id of the item, then + 1 to get to the texture. We need a way to be sure the texture is always next, or a new way to detect a situation where the texture isn't next (a queue system for uploading assets?)
	
	$assettype = getAssetInfo($id)->AssetTypeId;
	
	$textureassetid = $id+1;

	setAssetApproved($id);
	setAssetApproved($textureassetid);

	if ($assettype == 1 && isImageAssetAssociatedWithBadge($id))
	{
		enableUserBadgeFromAssociatedImageAsset($id);
	}
	else
	{
		switch ($assettype)
		{
			case 2: //TShirt
				Render::RenderTShirt($id, true);
				break;
			case 11: //Shirt
				Render::RenderShirt($id, true);
				break;
			case 12: //Pants
				Render::RenderPants($id, true);
				break;
			default:
				break;
		}
	}
	return true;
}

function fetchPendingAssets($extraparams="")
{
	$pending = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE IsApproved = 0 AND IsModerated = 0 AND (AssetTypeId = 1 AND Description = 'Place Thumbnail' OR AssetTypeId = 1 AND Description = 'Badge Image' OR AssetTypeId = 2 OR AssetTypeId = 11 OR AssetTypeId = 12 OR AssetTypeId = 22 OR AssetTypeId = 21) ".$extraparams."");
	$pending->execute();
	return $pending;
}

function moderateAsset($id) //currently supports t-shirts, shirts and pants
{
	$rendercdn = $GLOBALS['renderCDNPath'];
	$thumbscdn = $GLOBALS['thumbnailCDNPath'];
	$assetscdn = $GLOBALS['assetCDNPath'];
	
	$assetinfo = getAssetInfo($id);
	$assetData = file_get_contents($assetscdn . $assetinfo->Hash);

	//first handle assets that have no dependencies
	if ($assetinfo->AssetTypeId == 22) //group emblem
	{
		unlink($thumbscdn . $assetinfo->Hash);
		setAssetModerated($id);
		return true;
	}
	else
	{
		$validXML = true;
		try
		{
			$ParsedXML = new SimpleXMLElement($assetData);
		}
		catch (Exception $e)
		{
			$validXML = false;
		}

		if ($validXML)
		{
			$userCosmetic = false;
			$userModel = false;
			$userSolidModel = false;
			$textureUrl = "";
			$textureAssetId = "";
			$textureHash = "";
			$assetHash = "";
			$assetThumbHash = "";

			switch ($assetinfo->AssetTypeId) 
			{
				case 2: //tshirt
					$userCosmetic = true;
					$textureUrl = $ParsedXML->xpath('//Properties/Content[@name="Graphic"]/url')[0];
					break;
				case 11: //shirt
					$userCosmetic = true;
					$textureUrl = $ParsedXML->xpath('//Properties/Content[@name="ShirtTemplate"]/url')[0];
					break;
				case 12: //pants
					$userCosmetic = true;
					$textureUrl = $ParsedXML->xpath('//Properties/Content[@name="PantsTemplate"]/url')[0];
					break;
				case 39: //solid model
					$userSolidModel = true;
					return "SolidModel Moderation coming soon";
				case 10: //model
					$userModel = true;
					return "Model Moderation coming soon";
				default:
					return "Unsupported Asset";
			}

			if ($userCosmetic)
			{
				if (strpos($textureUrl, "id="))
				{
					$textureAssetId = substr($textureUrl, strpos($textureUrl, "id=")+3, strlen($textureUrl));
					$textureHash = getAssetInfo($textureAssetId)->Hash;
					$assetHash = getAssetInfo($id)->Hash;
					$assetThumbHash = getAssetInfo($id)->ThumbHash;
				}
				else
				{
					return "Failed to fetch the Texture, contact an Administrator";
				}
			}

			//update the texture hash to blank, description to Content Deleted, name to Content Deleted, IsForSale to 0 and IsCommentsEnabled to 0
			$updatetexture = $GLOBALS['pdo']->prepare("UPDATE assets SET Hash = '', Name = 'Content Deleted', Description = 'Content Deleted', IsForSale = 0, IsCommentsEnabled = 0 WHERE id = :i");
			$updatetexture->bindParam(":i", $textureAssetId, PDO::PARAM_INT);
			$updatetexture->execute();

			//update the shirt asset hash to blank and set the thumbhash to blank, description to Content Deleted, name to Content Deleted, IsForSale to 0 and IsCommentsEnabled to 0
			$updatecosmetic = $GLOBALS['pdo']->prepare("UPDATE assets SET Hash = '', ThumbHash = '', Name = 'Content Deleted', Description = 'Content Deleted', IsForSale = 0, IsCommentsEnabled = 0 WHERE id = :i");
			$updatecosmetic->bindParam(":i", $id, PDO::PARAM_INT);
			$updatecosmetic->execute();

			//delete the texture asset, shirt asset and shirt thumb hash from the cdn
			unlink($thumbscdn . $textureHash);
			unlink($assetscdn . $assetHash);
			unlink($rendercdn . $assetThumbHash);

			setAssetModerated($id);
			setAssetModerated($textureAssetId);

			//re render players wearing the asset (if its a tshirt, shirt or pants)
			if ($assetinfo->AssetTypeId == 2 || $assetinfo->AssetTypeId == 11 || $assetinfo->AssetTypeId == 12)
			{
				$assetowners = $GLOBALS['pdo']->prepare("SELECT * FROM wearing_items WHERE aid = :a");
				$assetowners->bindParam(":a", $id, PDO::PARAM_INT);
				$assetowners->execute();
				
				foreach($assetowners as $owner)
				{
					UsersRender::RenderPlayer($owner['uid']);
					Sleep(2);
				}
			}

			return true;
		}
	}
	return "Error Occurred";
}
/*
function moderateAsset($id) //currently supports t-shirts, shirts and pants
{
	//the logic behind this is it uses the asset id of the item, then + 1 to get to the texture. We need a way to be sure the texture is always next, or a new way to detect a situation where the texture isn't next (a queue system for uploading assets?)
	
	$rendercdn = $GLOBALS['renderCDNPath'];
	$thumbscdn = $GLOBALS['thumbnailCDNPath'];
	$assetscdn = $GLOBALS['assetCDNPath'];
	
	$assetinfo = getAssetInfo($id);
	
	if ($assetinfo->AssetTypeId == 2 or $assetinfo->AssetTypeId == 11 or $assetinfo->AssetTypeId == 12) //t-shirt, shirt or pants
	{
		//get the texture asset and hash
		$textureid = $id + 1;
		$textureinfo = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i");
		$textureinfo->bindParam(":i", $textureid, PDO::PARAM_INT);
		$textureinfo->execute();
		$textureinfo = $textureinfo->fetch(PDO::FETCH_OBJ);
		$texturehash = $textureinfo->Hash;
		// ...
		
		//update the texture hash to blank
		$updatetexture = $GLOBALS['pdo']->prepare("UPDATE assets SET Hash = '' WHERE id = :i");
		$updatetexture->bindParam(":i", $textureid, PDO::PARAM_INT);
		$updatetexture->execute();
		// ...
		
		//delete the texture from the cdn
		unlink($thumbscdn . $texturehash);
		// ...

		//update the shirt asset hash to blank and set the thumbhash to blank
		$updatetexture = $GLOBALS['pdo']->prepare("UPDATE assets SET Hash = '', ThumbHash = '' WHERE id = :i");
		$updatetexture->bindParam(":i", $id, PDO::PARAM_INT);
		$updatetexture->execute();
		// ...
		
		//delete the shirt asset from the cdn
		$hash = $assetinfo->Hash;
		unlink($assetscdn . $hash);
		// ...
		
		//delete the shirt render from the cdn (if the shirt wasn't ever approved, this is useless. But keeping it for future moderation of an asset)
		$renderhash = $assetinfo->ThumbHash;
		unlink($rendercdn . $renderhash);
		// ...
		
		//set the assets to moderated
		setAssetModerated($id);
		setAssetModerated($textureid);
		// ...
		return true;
	}
	else if ($assetinfo->AssetTypeId == 22) //reg imagesavealpha
	{
		//delete the texture from the cdn
		unlink($thumbscdn . $assetinfo->Hash);
		setAssetModerated($id);
		return true;
	}
	return "Error occurred";
}
*/

function isAssetApproved($id)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i AND IsApproved = 1");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	
	if ($check->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function isAssetModerated($id)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i AND IsModerated = 1");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	
	if ($check->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function getAssetInfo($id) 
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0) {
		return $check->fetch(PDO::FETCH_OBJ);
	}
	return false;
}

function assetTypeArray()
{
	$types = array(	
	0  => "Product",
	1  => "Image",
    2  => "T-Shirt",
    3  => "Audio",
    4  => "Mesh",
    5  => "Lua",
    6  => "HTML",
    7  => "Text",
    8  => "Hat",
    9  => "Place",
    10 => "Model",
    11 => "Shirt",
    12 => "Pants",
    13 => "Decal",
    16 => "Avatar",
    17 => "Head",
    18 => "Face",
    19 => "Gear",
    21 => "Badge",
    22 => "Group Emblem",
    24 => "Animation",
    25 => "Arms",
    26 => "Legs",
    27 => "Torso",
    28 => "Right Arm",
    29 => "Left Arm",
    30 => "Left Leg",
    31 => "Right Leg",
    32 => "Package",
    33 => "YouTube Video",
    34 => "Game Pass",
	35 => "App",
	37 => "Code",
	38 => "Plugin",
	39 => "SolidModel",
	40 => "MeshPart"
	);	
	return $types;
}

function isWearable($itemtype)
{
	if ($itemtype == 8 || //hats
	$itemtype == 2 || //tshirts
	$itemtype == 11 || //shirts
	$itemtype == 12 || //pants
	$itemtype == 18 || //faces
	$itemtype == 19 || //gears
	$itemtype == 17 || //heads
	$itemtype == 32) //packages
	{
		return true;
	}
	return false;
}

function typeToMaxCosmetic($itemtype) //itemtype 8 = hats, 2 = tshirts, 11 = shirts, 12 = pants, 18 = faces, 19 = gears, 17 = heads, 32 = packages
{
	switch ($itemtype) {
		case 8: //hats
			return 6;
		case 2: //tshirts
			return 1;
		case 11: //shirts
			return 1;
		case 12: //pants
			return 1;
		case 18: //faces
			return 1;
		case 19: //gears
			return 1;
		case 17: //heads
			return 1;
		case 32: //packages
			return 1;
		default: //what?
			return 0;
	}
}

function playerOwnsAsset($id, $userid=NULL) 
{
	if ($userid === NULL) {
		$userid = $GLOBALS['user']->id;
	}

	$check = $GLOBALS['pdo']->prepare("SELECT * FROM owned_assets WHERE aid = :a AND uid = :u");
	$check->bindParam(":a", $id, PDO::PARAM_INT);
	$check->bindParam(":u", $userid, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0) {
		return true;
	}
	return false;
}

//end asset functions

//user functions

function userPlaying($userid)
{
	$p = $GLOBALS['pdo']->prepare("SELECT *  FROM game_presence WHERE uid = :i AND (lastPing + 50) > UNIX_TIMESTAMP()");
	$p->bindParam(":i", $userid, PDO::PARAM_INT);
	$p->execute();
					
	if($p->rowCount() > 0) //if the ingame check has any results
	{
		if (canJoinUser($userid))
		{
			$playingInfo = $p->fetch(PDO::FETCH_OBJ);
			$info = array (
				"placeid" => $playingInfo->placeid,
				"jobid" =>  $playingInfo->jobid
			);
			return $info;
		}
	}		
	$info = array (
		"placeid" => null,
		"jobid" =>  null
	);
	return $info;
}

function isUserInventoryPrivate($userid)
{
	if(userInfo($userid)->privateInventory && !$GLOBALS['user']->isAdmin())
	{
		return true;
	}
	return false;
}

function chatFilterInfractionLimit($userid, $limit, $seconds)
{
	$infractions = $GLOBALS['pdo']->prepare("SELECT * FROM chat_logs WHERE whoSent = :uid AND (whenSent + :seconds) > UNIX_TIMESTAMP() AND trippedFilter = 1");
	$infractions->bindParam(":uid", $userid, PDO::PARAM_INT);
	$infractions->bindParam(":seconds", $seconds, PDO::PARAM_INT);
	$infractions->execute();
	if ($infractions->rowCount() >= $limit)
	{
		return true;
	}
	return false;
}

function kickUserIfInGame($userid, $message)
{
	$gamesession = $GLOBALS['pdo']->prepare("SELECT *  FROM game_presence WHERE uid = :i AND (lastPing + 50) > UNIX_TIMESTAMP()");
	$gamesession->bindParam(":i", $userid, PDO::PARAM_INT);
	$gamesession->execute();

	if ($gamesession->rowCount() > 0)
	{
		soapExecuteEx($GLOBALS['gamesArbiter'], $gamesession->fetch(PDO::FETCH_OBJ)->jobid, "Kick Message ".$userid, "game.Players." . getUsername($userid) . ":Kick(\"".$message."\")");
	}
}

function siteStatus($userid)
{
	$p = $GLOBALS['pdo']->prepare("SELECT *  FROM game_presence WHERE uid = :i AND (lastPing + 50) > UNIX_TIMESTAMP()");
	$p->bindParam(":i", $userid, PDO::PARAM_INT);
	$p->execute();

	$userinfo = $GLOBALS['pdo']->prepare('SELECT * FROM `users` WHERE id = :uid');
	$userinfo->bindParam(':uid', $userid, PDO::PARAM_INT);
	$userinfo->execute();
	$userinfo = $userinfo->fetch(PDO::FETCH_OBJ);
					
	if($p->rowCount() > 0) //if the ingame check has any results
	{
		$serverInfo = $p->fetch(PDO::FETCH_OBJ);
					
		$g = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i");
		$g->bindParam(":i", $serverInfo->placeid, PDO::PARAM_INT);
		$g->execute();
					
		$gameInfo = $g->fetch(PDO::FETCH_OBJ);
					
		if (canJoinUser($userinfo->id)) //depending on the user's settings, show what game they are playing (might wanna also pass the userID variable if there are options such as everyone, friends only, etc)
		{
			//user viewing profile has permission to see what game they are in
			return cleanOutput($gameInfo->Name);
		}
		else
		{
			//no perms
			return 'In-Game';
		}			
	}
	else //if no ingame result, check if the user has pinged the site in a while
	{
		if (($userinfo->lastseen + 120) > time()) 
		{
			return 'Online';
		}
		else
		{
			return 'Offline';
		}
	}
}
// ...

//friend request button check

function friendStatus($userid)
{
	if(friendsWith($userid))
	{
		return "Friends";
	} 
	elseif(friendsPending($userid))
	{
		return "Pending";
	}
	elseif(userSentFriendReq($userid))
	{
		return "Incoming";
	}
	elseif ($GLOBALS['user']->id != $userid)
	{
		return "User";
	}
	return "";
}

//end user functions

//asset comments stuff {
	
function placeAssetComment($aid, $comment) //1 = comment placed, 2 = cooldown, 3 = error
{
	$interval = 0;
	$localuser = $GLOBALS['user']->id;
	
	$commentscheck = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i");
	$commentscheck->bindParam(":i", $aid, PDO::PARAM_INT);
	$commentscheck->execute();
	$commentscheck = $commentscheck->fetch(PDO::FETCH_OBJ);
		
	if ($commentscheck->IsCommentsEnabled == true) //check if comments are enabled for the asset
	{
		$intervalcheck = $GLOBALS['pdo']->prepare("SELECT * FROM asset_comments WHERE uid = :u ORDER BY whenCommented DESC LIMIT 1");
		$intervalcheck->bindParam(":u", $localuser, PDO::PARAM_INT);
		$intervalcheck->execute();
			
		if ($intervalcheck->rowCount() > 0) //we dont want to be calling an object that is NULL
		{
			$interval = (int)$intervalcheck->fetch(PDO::FETCH_OBJ)->whenCommented;
		}
			
		if(($interval + (60)) < time()) //60 second interval
		{
			if(strlen($comment) < 5)
			{
				return "Comment too short, must be above 5 Characters";
			}
			elseif(strlen($comment) > 200)
			{
				return "Comment too long, must be under 200 Characters";
			}
			else
			{
				$setcomment = $GLOBALS['pdo']->prepare("INSERT INTO asset_comments(uid, aid, comment, whenCommented) VALUES(:u, :aid, :c, UNIX_TIMESTAMP())");
				$setcomment->bindParam(":u", $localuser, PDO::PARAM_INT);
				$setcomment->bindParam(":aid", $aid, PDO::PARAM_INT);
				$setcomment->bindParam(":c", $comment, PDO::PARAM_INT);
				if ($setcomment->execute())
				{
					return true;
				}
				return "Error Occurred";
			}
		}
		return "Please wait before commenting again";
	}
	return "Error Occurred";
}

//end catalog comments stuff }

//canjoin stuff {
	
function setCanJoinUser($status)
{
	$localuser = $GLOBALS['user']->id;
	$maxcanjoinstatus = 2;
	
	if ($status <= $maxcanjoinstatus)
	{
		$setstatus = $GLOBALS['pdo']->prepare("UPDATE users SET canJoin = :c WHERE id = :u");
		$setstatus->bindParam(":c", $status, PDO::PARAM_INT);
		$setstatus->bindParam(":u", $localuser, PDO::PARAM_INT);
		$setstatus->execute();
		
		return true;
	}
	return false;
}
	
function canJoinUser($uid) //
{
	$canjoinstatusquery = $GLOBALS['pdo']->prepare("SELECT canJoin FROM users WHERE id = :i");
	$canjoinstatusquery->bindParam(":i", $uid, PDO::PARAM_INT);
	$canjoinstatusquery->execute();
	$canjoinstatus = (int)$canjoinstatusquery->fetch(PDO::FETCH_OBJ)->canJoin;
	
	//0 = noone, 1 = friends, 2 = everyone
	if($canjoinstatus == 1)
	{
		if (friendsWith($uid))
		{
			return true;
		}
	}
	elseif($canjoinstatus == 2)
	{
		return true;
	}
	return false;
}
	
//end canjoin stuff }

//email stuff {
	
function emailRegistered($email)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE email = :e"); //verify user
	$check->bindParam(":e", $email, PDO::PARAM_STR);
	$check->execute();
	if ($check->rowCount() > 0)
	{
		return true;
	}
	return false;
}
	
function sendMail($from, $recipient, $subject, $body, $altbody)
{
	$job = popen("cd C:/Webserver/nginx/Alphaland && start /B php sendEmail.php ".$from." ".$recipient." ".base64_encode($subject)." ".base64_encode($body)." ".base64_encode($altbody), "r"); 
    if ($job !== FALSE);
    {
        pclose($job);
        return true;
    }
    return false;
}
	
function verifyEmail($token)
{
	$localuser = $GLOBALS['user']->id;
	
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM verify_email_keys WHERE uid = :u AND token = :t AND valid = 1");
	$check->bindParam(":u", $localuser, PDO::PARAM_INT);
	$check->bindParam(":t", $token, PDO::PARAM_INT);
	$check->execute();
		
	if ($check->rowCount() > 0) //verify email if everything checks out
	{
		$verify = $GLOBALS['pdo']->prepare("UPDATE users SET verified = 1 WHERE id = :u"); //verify user
		$verify->bindParam(":u", $localuser, PDO::PARAM_INT);
		$verify->execute();
			
		$invalidatekey = $GLOBALS['pdo']->prepare("DELETE from verify_email_keys WHERE uid = :u"); //delete db key
		$invalidatekey->bindParam(":u", $localuser, PDO::PARAM_INT);
		$invalidatekey->execute();
		
		//verified badge
		$verifiedbadgeid = 1;
		$checkifbadgeexist = $GLOBALS['pdo']->prepare("SELECT * FROM user_badges WHERE uid = :u AND bid = :b");
		$checkifbadgeexist->bindParam(":u", $localuser, PDO::PARAM_INT);
		$checkifbadgeexist->bindParam(":b", $verifiedbadgeid, PDO::PARAM_INT);
		$checkifbadgeexist->execute();
		if ($checkifbadgeexist->rowCount() == 0)
		{
			$verifiedbadge = $GLOBALS['pdo']->prepare("INSERT INTO user_badges(uid,bid,isOfficial,whenEarned) VALUES(:n, :d, 1, UNIX_TIMESTAMP())");
			$verifiedbadge->bindParam(":n", $localuser, PDO::PARAM_INT);
			$verifiedbadge->bindParam(":d", $verifiedbadgeid, PDO::PARAM_INT);
			$verifiedbadge->execute();
		}	
		// ...
		return true;
	}
	return false;
}
	
function sendVerificationEmail($from, $recipient) //1 = success, 2 = cooldown, 3 = fail
{
	$localuser = $GLOBALS['user']->id;
	
	$t = genVerifcationEmailHash(24);
	
	$email_html = '
	<div style="width:40rem;background-color:white;border:1px solid rgba(0,0,0,.125);border-radius:5px;padding:12px;margin:auto;">
		<div style="text-align:center;">
			<img src="https://alphaland.cc/alphaland/cdn/imgs/alphaland-1024.png" style="width:18rem;">
		</div>
		<div style="text-align:center;">
			<strong>Click the button below to verify your email!</strong>
		</div>
		<div style="text-align:center;margin-top:10px;">
			<a style="text-decoration:none;" href="https://alphaland.cc/verifyemail?token='.$t.'"><span style="border:none;color:white;background-color:#c82333;border-radius:4px;padding:10px;cursor:pointer;">Verify Email</span></a>
		</div>
	</div>';
	
	$email_altbody = 'https://alphaland.cc/verifyemail?token='.$t.'';
	
	$checkifverified = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :u AND verified = 0");
	$checkifverified->bindParam(":u", $localuser, PDO::PARAM_INT);
	$checkifverified->execute();
	
	if ($checkifverified->rowCount() > 0) //player isnt verified
	{
		$check = $GLOBALS['pdo']->prepare("SELECT * FROM verify_email_keys WHERE uid = :u AND valid = 1");
		$check->bindParam(":u", $localuser, PDO::PARAM_INT);
		$check->execute();
		
		if ($check->rowCount() > 0) //already a verification email pending
		{
			$keyinfo = $check->fetch(PDO::FETCH_OBJ);
			
			if(($keyinfo->lastAttempt + (60)) < time()) //under the 60 second interval
			{
				$updatetoken = $GLOBALS['pdo']->prepare("UPDATE verify_email_keys SET lastAttempt = UNIX_TIMESTAMP(), token = :t WHERE uid = :u AND valid = 1");
				$updatetoken->bindParam(":t", $t, PDO::PARAM_INT);
				$updatetoken->bindParam(":u", $localuser, PDO::PARAM_INT);
				$updatetoken->execute();
				
				if (sendMail($from, $recipient, "Verify Email", $email_html, $email_altbody))
				{
					return 1;
				}
			}
			else
			{
				return 2;
			}
		}
		else //create new one
		{
			$n = $GLOBALS['pdo']->prepare("INSERT INTO verify_email_keys(uid, token, valid, lastAttempt) VALUES(:u, :t, 1, UNIX_TIMESTAMP())");
			$n->bindParam(":u", $localuser, PDO::PARAM_INT);
			$n->bindParam(":t", $t, PDO::PARAM_INT);
			if($n->execute()) 
			{
				if (sendMail($from, $recipient, "Verify Email", $email_html, $email_altbody))
				{
					return 1;
				}
			}
		}	
	}
	return 3;
}

function changeEmail($newemail) //1 = success, 2 = cooldown, 3 = fail
{
	$localuser = $GLOBALS['user']->id;
	
	$sendver = sendVerificationEmail("info@alphaland.cc", $newemail);
	
	if ($sendver == 1) //all good
	{
		$updateemail = $GLOBALS['pdo']->prepare("UPDATE users SET email = :e WHERE id = :u");
		$updateemail->bindParam(":e", $newemail, PDO::PARAM_STR);
		$updateemail->bindParam(":u", $localuser, PDO::PARAM_INT);
		$updateemail->execute();
		return 1;
	}
	elseif ($sendver == 2) //cooldown
	{
		return 2;
	}
	elseif ($sendver == 3) //player is verified, do it again but unverify first
	{
		$unverify = $GLOBALS['pdo']->prepare("UPDATE users SET verified = 0 WHERE id = :u");
		$unverify->bindParam(":u", $localuser, PDO::PARAM_INT);
		if ($unverify->execute())
		{
			$sendver2 = sendVerificationEmail("info@alphaland.cc", $newemail);
			
			if ($sendver2 == 1) //all good
			{
				$updateemail = $GLOBALS['pdo']->prepare("UPDATE users SET email = :e WHERE id = :u");
				$updateemail->bindParam(":e", $newemail, PDO::PARAM_STR);
				$updateemail->bindParam(":u", $localuser, PDO::PARAM_INT);
				$updateemail->execute();
				return 1;
			}
			elseif ($sendver2 == 2) //cooldown
			{
				return 2;
			}
			elseif ($sendver2 == 3) //still didnt go thru, return error
			{
				return 3;
			}
		}
		else //couldnt unverify, return error
		{
			return 3;
		}
	}
}

function changePassword($newpass)
{
	$localuser = $GLOBALS['user']->id;
	
	$encryptedpassword = password_hash($newpass, PASSWORD_DEFAULT);
	
	$updatepassword = $GLOBALS['pdo']->prepare("UPDATE users SET pwd = :p WHERE id = :u");
	$updatepassword->bindParam(":p", $encryptedpassword, PDO::PARAM_STR);
	$updatepassword->bindParam(":u", $localuser, PDO::PARAM_INT);
	if ($updatepassword->execute())
	{
		return true;
	}
	return false;
}

function changePasswordUid($uid, $newpass)
{
	$encryptedpassword = password_hash($newpass, PASSWORD_DEFAULT);
	
	$updatepassword = $GLOBALS['pdo']->prepare("UPDATE users SET pwd = :p WHERE id = :u");
	$updatepassword->bindParam(":p", $encryptedpassword, PDO::PARAM_STR);
	$updatepassword->bindParam(":u", $uid, PDO::PARAM_INT);
	if ($updatepassword->execute())
	{
		return true;
	}
	return false;
}

function confirmPasswordReset($token) //0 = error, > 0 = good
{
	//echo $token;
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM password_reset_keys WHERE token = :t AND valid = 1");
	$check->bindParam(":t", $token, PDO::PARAM_INT);
	$check->execute();
		
	if ($check->rowCount() > 0) //change password if everything checks out
	{
		$userdetails2 = $check->fetch(PDO::FETCH_OBJ);
		$invalidatekey = $GLOBALS['pdo']->prepare("DELETE from password_reset_keys WHERE token = :u"); //delete db key
		$invalidatekey->bindParam(":u", $token, PDO::PARAM_INT);
		$invalidatekey->execute();
		return $userdetails2->uid;
	}
	return 0;
}

function sendPasswordReset($from, $recipient, $recipientuid) //1 = success, 2 = cooldown, 3 = fail
{
	$t = genResetPasswordHash(24);
	
	$email_html = '
	<div style="width:40rem;background-color:white;border:1px solid rgba(0,0,0,.125);border-radius:5px;padding:12px;margin:auto;">
		<div style="text-align:center;">
			<img src="https://alphaland.cc/alphaland/cdn/imgs/alphaland-1024.png" style="width:18rem;">
		</div>
		<div style="text-align:center;">
			<strong>Click the button below to reset your password!</strong>
		</div>
		<div style="text-align:center;margin-top:10px;">
			<a style="text-decoration:none;" href="https://alphaland.cc/settings/resetpassword?token='.$t.'"><span style="border:none;color:white;background-color:#c82333;border-radius:4px;padding:10px;cursor:pointer;">Reset Password</span></a>
		</div>
	</div>';
	
	$email_altbody = 'https://alphaland.cc/settings/resetpassword?token='.$t.'';
	
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM password_reset_keys WHERE uid = :u AND valid = 1");
	$check->bindParam(":u", $recipientuid, PDO::PARAM_INT);
	$check->execute();
		
	if ($check->rowCount() > 0) //already a pass reset email pending
	{
		$keyinfo = $check->fetch(PDO::FETCH_OBJ);
			
		if(($keyinfo->lastAttempt + (60)) < time()) //under the 60 second interval
		{
			$updatetoken = $GLOBALS['pdo']->prepare("UPDATE password_reset_keys SET lastAttempt = UNIX_TIMESTAMP(), token = :t WHERE uid = :u AND valid = 1");
			$updatetoken->bindParam(":t", $t, PDO::PARAM_INT);
			$updatetoken->bindParam(":u", $recipientuid, PDO::PARAM_INT);
			$updatetoken->execute();
				
			if (sendMail($from, $recipient, "Reset Password", $email_html, $email_altbody))
			{
				return 1;
			}
		}
		else
		{
			return 2;
		}
	}
	else //create new one
	{
		$n = $GLOBALS['pdo']->prepare("INSERT INTO password_reset_keys(uid, token, valid, lastAttempt) VALUES(:u, :t, 1, UNIX_TIMESTAMP())");
		$n->bindParam(":u", $recipientuid, PDO::PARAM_INT);
		$n->bindParam(":t", $t, PDO::PARAM_INT);
		if($n->execute()) 
		{
			if (sendMail($from, $recipient, "Reset Password", $email_html, $email_altbody))
			{
				return 1;
			}
		}
	}	
	return 3;
}
	
//email stuff end }

//signup keys {
	
function verifySignupKey($key) 
{
	$n = $GLOBALS['pdo']->prepare("SELECT * FROM signup_keys WHERE signupkey = :t AND valid = 1");
	$n->bindParam(":t", $key, PDO::PARAM_INT);
	$n->execute();
	
	if ($n->rowCount() > 0)
	{
		$invalidate = $GLOBALS['pdo']->prepare("UPDATE signup_keys SET valid = 0 WHERE signupkey = :t");
		$invalidate->bindParam(":t", $key, PDO::PARAM_INT);
		$invalidate->execute();
		return true;
	}
	return false;
}
	
function genSignupKey() 
{
	$t = genSignupKeyHash(16);
	$n = $GLOBALS['pdo']->prepare("INSERT INTO signup_keys(signupkey, whenGenerated) VALUES(:t, UNIX_TIMESTAMP())");
	$n->bindParam(":t", $t, PDO::PARAM_INT);
	if($n->execute()) 
	{
		return $t;
	}
}
	
//signup keys end }

//badges {
	
function allOfficialBadges() {
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM official_badges");
	$check->execute();
	return $check;
}
	
function officialBadgeInfo($id) {
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM official_badges WHERE id = :i");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0) {
		return $check->fetch(PDO::FETCH_OBJ);
	}
	return false;
}
	
function officialPlayerBadges($id) {
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM user_badges WHERE uid = :i AND isOfficial = 1");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	return $check;
}

function giveBadge($badgeid, $userid)
{
	$gbadge = $GLOBALS['pdo']->prepare("INSERT INTO user_badges(uid,bid,isOfficial,whenEarned) VALUES(:n, :d, 1, UNIX_TIMESTAMP())");
	$gbadge->bindParam(":n", $userid, PDO::PARAM_INT);
	$gbadge->bindParam(":d", $badgeid, PDO::PARAM_INT);
	$gbadge->execute();
}

function removeBadge($badgeid, $userid)
{
	$rbadge = $GLOBALS['pdo']->prepare("DELETE FROM user_badges WHERE uid = :u AND bid = :b");
	$rbadge->bindParam(":u", $userid, PDO::PARAM_INT);
	$rbadge->bindParam(":b", $badgeid, PDO::PARAM_INT);
	$rbadge->execute();
}

/////////////////

function getUserBadgeInfo($id)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM badges WHERE id = :i");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0) {
		return $check->fetch(PDO::FETCH_OBJ);
	}
	return false;
}

function getUserBadgeOwner($id)
{
	return getAssetInfo(getUserBadgeInfo($id)->AwardingPlaceID)->CreatorId;
}

function isImageAssetAssociatedWithBadge($id)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM badges WHERE BadgeImageAssetID = :i");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	if ($check->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function getUserBadgeImage($id)
{
	return getAssetRender(getUserBadgeInfo($id)->BadgeImageAssetID);
}

function enableUserBadge($id)
{
	$check = $GLOBALS['pdo']->prepare("UPDATE badges SET isEnabled = 1 WHERE id = :i");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
}

function enableUserBadgeFromAssociatedImageAsset($id)
{
	$badge = $GLOBALS['pdo']->prepare("SELECT * FROM badges WHERE BadgeImageAssetID = :i");
	$badge->bindParam(":i", $id, PDO::PARAM_INT);
	$badge->execute();
	$badge = $badge->fetch(PDO::FETCH_OBJ)->id;
	enableUserBadge($badge);
}

function getPlayerBadges($userid)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM user_badges WHERE uid = :i AND isOfficial = 0");
	$check->bindParam(":i", $userid, PDO::PARAM_INT);
	$check->execute();
	return $check;
}

function hasUserBadge($userid, $badgeid)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM user_badges WHERE uid = :i AND bid = :bid AND isOfficial = 0");
	$check->bindParam(":i", $userid, PDO::PARAM_INT);
	$check->bindParam(":bid", $badgeid, PDO::PARAM_INT);
	$check->execute();
	if ($check->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function rewardUserBadge($UserID, $BadgeID, $PlaceID)
{
	$badge = getUserBadgeInfo($BadgeID);
	if ($badge !== FALSE && $badge->AwardingPlaceID == $PlaceID) //badge exists and the placeid matches the badges awardingplaceid
	{
		if (!hasUserBadge($UserID, $BadgeID)) //user doesnt already have the badge
		{
			$rbadge = $GLOBALS['pdo']->prepare("INSERT INTO user_badges(uid,bid,isOfficial,whenEarned) VALUES(:n, :d, 0, UNIX_TIMESTAMP())");
			$rbadge->bindParam(":n", $UserID, PDO::PARAM_INT);
			$rbadge->bindParam(":d", $BadgeID, PDO::PARAM_INT);
			$rbadge->execute();
			return true;
		}
	}
	return false;
}
		
//end of badges }

//backend communication and utilities for jobs {

function gameCloseAllJobs($id)
{
	$s = $GLOBALS['pdo']->prepare("SELECT * FROM open_servers WHERE gameID = :gid AND status < 2");
	$s->bindParam(":gid", $id, PDO::PARAM_INT);
	
	if ($s->execute())
	{
		foreach ($s as $server)
		{
			soapCloseJob($GLOBALS['gamesArbiter'], $server['jobid']);
		}
		return true;
	}
	return false;
}

function logSoapFault($soapresult, $description, $script)
{
	$theFault = print_r($soapresult, TRUE);
	$fault = $GLOBALS['pdo']->prepare("INSERT INTO soap_faults(jobdescription, script, fault, whenOccurred) VALUES(:jd, :sc, :f, UNIX_TIMESTAMP())");
	$fault->bindParam(":jd", $description, PDO::PARAM_STR);
	$fault->bindParam(":sc", $script, PDO::PARAM_STR);
	$fault->bindParam(":f", $theFault, PDO::PARAM_STR);
	$fault->execute();
}

function allocGamePort() //allocs a port between 50000 - 60000, verifies the port isn't in use by another game server
{
	$port = 0;
	$alloc = true;
	while ($alloc)
	{
		$port = rand(50000,60000); //port range forwarded on the server side (support up to 10000 jobs)
		
		$s = $GLOBALS['pdo']->prepare("SELECT * FROM open_servers WHERE port = :p AND status < 2");
		$s->bindParam(":p", $port, PDO::PARAM_STR);
		$s->execute();
		
		if ($s->rowCount() > 0 || $port == 57236) {
			continue;
		} else {
			$alloc = false;
		}
	}
	return $port;
}

function isGameServerAlive() //the main portion of this check is now a background script
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM websettings WHERE isGameServerAlive = 1");
	$check->execute();
	
	if ($check->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function isThumbnailerAlive() //the main portion of this check is now a background script
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM websettings WHERE isThumbnailerAlive = 1");
	$check->execute();
	
	if ($check->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function verifyLuaValue($value) //mostly due to booleans, but maybe something will come up in the future
{
	switch ($value)
	{
		case is_bool(json_encode($value)) || $value == 1:
			return json_encode($value);
		default:
			return $value;
	}
}

function getLuaType($value) //currently only supports booleans, integers and strings
{
	switch ($value)
	{
		case $value == "true" || $value == "false": //this is so gay
			return "LUA_TBOOLEAN";
		case !is_string($value) && !is_bool($value) && filter_var($value, FILTER_VALIDATE_INT):
			return "LUA_TNUMBER";
		default:
			return "LUA_TSTRING";
	}
}

function luaArguments($arguments=[]) //arguments for a script being executed
{
	if (!empty($arguments)) {
		$luavalue = array("LuaValue"=>array());
		foreach ($arguments as $argument) { 
			array_push($luavalue['LuaValue'], array(
				"type" => getLuaType($argument),
				"value" => verifyLuaValue($argument)
			));
		}
		return $luavalue;
	}
}

function soapCloseAllJobs($arbiter)
{
	return soapCallService($arbiter, "CloseAllJobs");
}

function soapCloseJob($arbiter, $jobid)
{
	return soapCallService($arbiter, "CloseJob", array("jobID" => $jobid));
}

function soapExecuteEx($arbiter, $jobid, $scriptname, $script, $arguments=[])
{
	return soapCallService($arbiter, "ExecuteEx", array(
			"jobID" => $jobid, 
			"script" => array(
				"name" => $scriptname,
				"script" => $script,
				"arguments" => luaArguments($arguments)
			)
		)
	);
}

function soapBatchJobEx($arbiter, $jobid, $expiration, $scriptname, $script, $arguments=[])
{
	return soapJobTemplate($arbiter, "BatchJobEx", $jobid, $expiration, 1, 3, $scriptname, $script, $arguments);
}

function soapJobTemplate($arbiter, $servicename, $jobid, $expiration, $category, $cores, $scriptname, $script, $arguments=[])
{
	return soapCallService(
		$arbiter,
		$servicename,
		array(
			"job" => array(
				"id" => $jobid,
				"expirationInSeconds" => $expiration,
				"category" => $category,
				"cores" => $cores
			),
			"script" => array(
				"name" => $scriptname,
				"script" => $script,
				"arguments" => luaArguments($arguments)
			)
		)
	);
}

function soapCallService($arbiter, $name, $arguments = []) 
{
	$soapcl = new \SoapClient($GLOBALS['RCCwsdl'], ["location" => "http://".$arbiter, "uri" => "http://roblox.com/", "exceptions" => false]);
	return $soapcl->{$name}($arguments); //thanks BrentDaMage didnt know u can do this
}

//end backend communication }

//thumbnails portion {

function constructRenderHashUrl($hash)
{
	return $GLOBALS['renderCDN']."/".$hash;
}

function constructThumbnailHashUrl($hash)
{
	return $GLOBALS['thumbnailCDN']."/".$hash;
}

function constructAssetHashUrl($hash)
{
	return $GLOBALS['assetCDN']."/".$hash;
}

function getSPTCosmeticTexture($id) //get shirt, pants or t shirt texture (used for asset approval system)
{
	//this is going by the logic that every shirt,pants and tshirt cosmetic will have the texture after the xml's asset id
	$id = $id + 1;
	
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i"); 
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	$check = $check->fetch(PDO::FETCH_OBJ);
	
	$thumbhash = $check->Hash;
	
	return constructThumbnailHashUrl($thumbhash);
}

function getImageFromAsset($id)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i"); 
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	$check = $check->fetch(PDO::FETCH_OBJ);
	
	$thumbhash = $check->Hash;
	
	return constructThumbnailHashUrl($thumbhash);
}

function getPlayerRender($uid, $headshot=false)
{
	//check if the user has a stalled render
	checkUserPendingRender($uid);
	
	$player = userInfo($uid);

	if ($player !== FALSE)
	{	
		if ($headshot && !empty($player->HeadshotThumbHash))
		{
			if (file_exists($GLOBALS['renderCDNPath'] . $player->HeadshotThumbHash))
			{
				return constructRenderHashUrl($player->HeadshotThumbHash); 
			}
		}
		else
		{
			if (file_exists($GLOBALS['renderCDNPath'] . $player->ThumbHash))
			{
				return constructRenderHashUrl($player->ThumbHash);
			}
		}
	}
	return getImageFromAsset(229); //229 is the pending render image
}

function getAssetRender($id)
{
	$assetinfo = getAssetInfo($id); //asset info

	if ($assetinfo !== FALSE)
	{
		if ($assetinfo->IsModerated == true)
		{
			return getImageFromAsset(193); //193 is moderated asset image
		}
		elseif ($assetinfo->IsApproved == false)
		{
			return getImageFromAsset(194); //194 is pending asset image
		}
		else
		{
			if ($assetinfo->AssetTypeId == 1 || $assetinfo->AssetTypeId == 22) //images and group emblems
			{
				$assethash = $assetinfo->Hash;
				if (file_exists($GLOBALS['thumbnailCDNPath'].$assethash))  {
					return constructThumbnailHashUrl($assethash);
				}	
			}
			else //default to grab the assets ThumbHash
			{
				if (!empty($assetinfo->ThumbHash)) //if a render was ever performed
				{
					$thumbhash = $assetinfo->ThumbHash;
					if (file_exists($GLOBALS['renderCDNPath'].$thumbhash))  {
						return constructRenderHashUrl($thumbhash);
					}
				}
			}
		}
	}
	return getImageFromAsset(126); //126 is default image asset id
}

function setPlaceUsingCustomThumbnail($id)
{
	$check = $GLOBALS['pdo']->prepare("UPDATE assets SET isPlaceRendered = 0, ThumbHash = NULL WHERE id = :i"); 
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
}

function isPlaceUsingRender($id)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i"); 
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	$check = $check->fetch(PDO::FETCH_OBJ);
	if ($check->isPlaceRendered == 1)
	{
		return true;
	}
	return false;
}

function usingCustomThumbnail($id) //so the thumb doesnt break while rendering
{
	//first grab the place iconimageassetid
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i"); 
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	$check = $check->fetch(PDO::FETCH_OBJ);
	$thumbhash = $check->ThumbHash;
	if (file_exists($GLOBALS['thumbnailCDNPath'] . $thumbhash))
	{
		return true;
	}
	return false;
}

function handleRenderPlace($placeid) //we have a 60 second wait, and we verify the render job was sent.
{
	$assetinfo = getAssetInfo($placeid);
	$lastrender = $assetinfo->lastPlaceRender;
			
	if(($lastrender + (60)) < time()) //60 second interval
	{
		if (Render::RenderPlace($placeid))
		{
			$c = $GLOBALS['pdo']->prepare("UPDATE assets SET lastPlaceRender = UNIX_TIMESTAMP() WHERE id = :i");
			$c->bindParam(":i", $placeid, PDO::PARAM_INT); //place id
			$c->execute();

			return true;
		}
		return "Failed to render Place";
	}
	return "Please wait before switching Thumbnail Settings again";
}

function handleGameThumb($id)
{
	$placeinfo = getAssetInfo($id);

	//see if the place is using rendered thumb
	$thumbhash = $placeinfo->ThumbHash;
	if ($thumbhash)
	{
		//broken check
		if (!file_get_contents($GLOBALS['renderCDNPath'].$thumbhash))
		{
			return getImageFromAsset(4);
		}
		return constructRenderHashUrl($thumbhash);
	}

	//get the iconimageassetid
	$iconimageassetid = getAssetInfo($placeinfo->IconImageAssetId); 

	if ($iconimageassetid->IsModerated == true)
	{
		return getImageFromAsset(193); //193 is moderated asset image
	}
	elseif ($iconimageassetid->IsApproved == false)
	{
		return getImageFromAsset(194); //194 is pending asset image
	}
	else
	{
		return constructThumbnailHashUrl($iconimageassetid->Hash);
	}	
}
//end thumbnails portion }

//character colors portion {
	
function getBC($id) {
	$bc = array("1" => "rgb(242, 243, 243)", "5" => "rgb(215, 197, 154)", "9" => "rgb(232, 186, 200)", "11" => "rgb(128, 187, 219)", "18" => "rgb(204, 142, 105)", "21" => "rgb(196, 40, 28)", "23" => "rgb(13, 105, 172)", "24" => "rgb(245, 208, 48)", "26" => "rgb(27, 42, 53)", "28" => "rgb(40, 127, 71)", "29" => "rgb(161, 196, 140)", "37" => "rgb(75, 151, 75)", "38" => "rgb(160, 95, 53)", "45" => "rgb(180, 210, 228)", "101" => "rgb(218, 134, 122)", "102" => "rgb(110, 153, 202)", "104" => "rgb(107, 50, 124)", "105" => "rgb(226, 155, 64)", "106" => "rgb(218, 133, 65)", "107" => "rgb(0, 143, 156)", "119" => "rgb(164, 189, 71)", "125" => "rgb(234, 184, 146)", "135" => "rgb(116, 134, 157)", "141" => "rgb(39, 70, 45)", "151" => "rgb(120, 144, 130)", "153" => "rgb(149, 121, 119)", "192" => "rgb(105, 64, 40)", "194" => "rgb(163, 162, 165)", "199" => "rgb(99, 95, 98)", "208" => "rgb(229, 228, 223)", "217" => "rgb(124, 92, 70)", "226" => "rgb(253, 234, 141)", "360" => "rgb(150, 103, 102)", "1001" => "rgb(248, 248, 248)", "1002" => "rgb(205, 205, 205)", "1003" => "rgb(17, 17, 17)", "1004" => "rgb(255, 0, 0)", "1005" => "rgb(255, 176, 0)", "1006" => "rgb(180, 128, 255)", "1007" => "rgb(163, 75, 75)", "1008" => "rgb(193, 190, 66)", "1009" => "rgb(255, 255, 0)", "1010" => "rgb(0, 0, 255)", "1011" => "rgb(0, 32, 96)", "1012" => "rgb(33, 84, 185)", "1013" => "rgb(4, 175, 236)", "1014" => "rgb(170, 85, 0)", "1015" => "rgb(170, 0, 170)", "1016" => "rgb(255, 102, 204)", "1018" => "rgb(18, 238, 212)", "1019" => "rgb(0, 255, 255)", "1020" => "rgb(0, 255, 0)", "1021" => "rgb(58, 125, 21)", "1022" => "rgb(127, 142, 100)", "1023" => "rgb(140, 91, 159)", "1024" => "rgb(175, 221, 255)", "1025" => "rgb(255, 201, 201)", "1026" => "rgb(177, 167, 255)", "1027" => "rgb(159, 243, 233)", "1028" => "rgb(204, 255, 204)", "1029" => "rgb(255, 255, 204)", "1030" => "rgb(255, 204, 153)", "1031" => "rgb(98, 37, 209)", "1032" => "rgb(255, 0, 191)");
	return ((array_key_exists($id, $bc))? $bc[$id]:"-");
}

//end character colors portion }

//settings portion {

function setBlurb($newblurb)
{
	$newblurb = cleanInput($newblurb);
	if (strlen($newblurb)<=4096) //limit 4096 characters
	{
		$localplayer = $GLOBALS['user']->id;
		
		$data = ['blurb' => $newblurb, 'id' => $localplayer];
		
		$blurb = $GLOBALS['pdo']->prepare("UPDATE users SET blurb=:blurb WHERE id=:id");
		if ($blurb->execute($data))
		{
			return true;
		}
	}
	return false;
}
	
//end settings portion }

//player appearance/inventory/purchases portion {
	
function setDefaults($uid) //gives default shirt and pants, body colors and wears the shirt and pants
{
	$check = $GLOBALS['pdo']->prepare("INSERT into owned_assets (uid, aid, stock, when_sold, givenby) VALUES(:u, 133, 0, UNIX_TIMESTAMP(), 1)"); //give asset 133
	$check->bindParam(":u", $uid, PDO::PARAM_INT);
	$check->execute();
	
	$check2 = $GLOBALS['pdo']->prepare("INSERT into owned_assets (uid, aid, stock, when_sold, givenby) VALUES(:u, 135, 0, UNIX_TIMESTAMP(), 1)"); //give asset 135
	$check2->bindParam(":u", $uid, PDO::PARAM_INT);
	$check2->execute();
	
	$check3 = $GLOBALS['pdo']->prepare("INSERT into owned_assets (uid, aid, stock, when_sold, givenby) VALUES(:u, 1, 0, UNIX_TIMESTAMP(), 1)"); //give asset 1
	$check3->bindParam(":u", $uid, PDO::PARAM_INT);
	$check3->execute();
	
	$check4 = $GLOBALS['pdo']->prepare("INSERT into wearing_items (uid, aid, whenWorn) VALUES(:u, 133, UNIX_TIMESTAMP())"); //wear asset 133
	$check4->bindParam(":u", $uid, PDO::PARAM_INT);
	$check4->execute();
	
	$check5 = $GLOBALS['pdo']->prepare("INSERT into wearing_items (uid, aid, whenWorn) VALUES(:u, 135, UNIX_TIMESTAMP())"); //wear asset 135
	$check5->bindParam(":u", $uid, PDO::PARAM_INT);
	$check5->execute();
	
	$check6 = $GLOBALS['pdo']->prepare("INSERT into wearing_items (uid, aid, whenWorn) VALUES(:u, 1, UNIX_TIMESTAMP())"); //wear asset 1
	$check6->bindParam(":u", $uid, PDO::PARAM_INT);
	$check6->execute();
	
	$check7 = $GLOBALS['pdo']->prepare("INSERT into body_colours (uid) VALUES(:u)"); //body colors (we just need a uid since the default is in the db)
	$check7->bindParam(":u", $uid, PDO::PARAM_INT);
	$check7->execute();

	$defaulthash = $GLOBALS['defaultOutfitHash']; //default outfit hash
	$defaultheadshothash = $GLOBALS['defaultHeadshotHash']; //default headshot hash
	$check8 = $GLOBALS['pdo']->prepare("UPDATE users SET ThumbHash = :dh, HeadshotThumbHash = :hdh WHERE id = :u");
	$check8->bindParam(":dh", $defaulthash, PDO::PARAM_STR);
	$check8->bindParam(":hdh", $defaultheadshothash, PDO::PARAM_STR);
	$check8->bindParam(":u", $uid, PDO::PARAM_INT);
	$check8->execute();
}

function wearingItems($type) //returns how many of the item type the user is wearing
{
	$localuser = $GLOBALS['user']->id;
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM wearing_items WHERE uid = :u"); 
	$check->bindParam(":u", $localuser, PDO::PARAM_INT);
	$check->execute();
	
	$count = 0;
	foreach ($check as $item)
	{
		$iteminfo = getAssetInfo($item['aid']);
		if ($iteminfo->AssetTypeId == $type)
		{
			$count = $count + 1;
		}
	}
	return $count;
}

function equippedAssetByType($type) //returns the users last equipped item by type, limited to one
{
	$localuser = $GLOBALS['user']->id;
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM wearing_items WHERE uid = :u ORDER BY whenWorn ASC"); 
	$check->bindParam(":u", $localuser, PDO::PARAM_INT);
	$check->execute();
	
	$wearing = 0;
	foreach ($check as $item)
	{
		$iteminfo = getAssetInfo($item['aid']);
		if ($iteminfo->AssetTypeId == $type)
		{
			$wearing = $item['aid'];
			break;
		}
	}
	return $wearing;
}

function currentRenderCount($userid)
{
	$userinfo = userInfo($userid);
	if (($userinfo->lastRender + 15) < time())
	{
		$update = $GLOBALS['pdo']->prepare("UPDATE users SET renderCount = 0 WHERE id = :u");
		$update->bindParam(":u", $userid, PDO::PARAM_INT);
		$update->execute();
	}

	return $userinfo->renderCount;
}

function isRenderCooldown($userid)
{
	if (currentRenderCount($userid) > 3)
	{
		return true;
	}
	return false;
}

function deequipItem($assetId)
{
	$localuser = $GLOBALS['user']->id;
	$item = $GLOBALS['pdo']->prepare("SELECT * FROM wearing_items WHERE uid = :u AND aid = :i");
	$item->bindParam(":u", $localuser, PDO::PARAM_INT);
	$item->bindParam(":i", $assetId, PDO::PARAM_INT);
	$item->execute();
	if($item->rowCount() > 0) 
	{
		if (isThumbnailerAlive())
		{
			if (!isRenderCooldown($localuser))
			{
				$deequip = $GLOBALS['pdo']->prepare("DELETE from wearing_items WHERE uid = :u AND aid = :a"); //delete db key
				$deequip->bindParam(":u", $localuser, PDO::PARAM_INT);
				$deequip->bindParam(":a", $assetId, PDO::PARAM_INT);
				$deequip->execute();

				rerenderutility();
			}
			else
			{
				return "Slow down!";
			}
		}
		else
		{
			return "Thumbnail Server is offline";
		}
	}
	return true;
}

function equipItem($assetId)
{
	$localuser = $GLOBALS['user']->id;
	$asset = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i");
	$asset->bindParam(":i", $assetId, PDO::PARAM_INT);
	$asset->execute();
	
	if($asset->rowCount() > 0) 
	{
		if (playerOwnsAsset($assetId))
		{
			$item = $GLOBALS['pdo']->prepare("SELECT * FROM wearing_items WHERE uid = :u AND aid = :i");
			$item->bindParam(":u", $localuser, PDO::PARAM_INT);
			$item->bindParam(":i", $assetId, PDO::PARAM_INT);
			$item->execute();
			if(!($item->rowCount() > 0)) 
			{
				if (isThumbnailerAlive())
				{
					if (!isRenderCooldown($localuser))
					{
						if (!isAssetModerated($assetId))
						{
							//pdo object, assettypeid
							$iteminfo = getAssetInfo($assetId);
							$type = $iteminfo->AssetTypeId;
							
							if (isWearable($type))
							{
								$maxitems = (int)typeToMaxCosmetic($type);
								if (wearingItems($type) == $maxitems)
								{
									//grab the currently wearing asset of the type
									$wearingasset = (int)equippedAssetByType($type);
										
									//unwear current asset of that type
									$deequip = $GLOBALS['pdo']->prepare("DELETE from wearing_items WHERE uid = :u AND aid = :a"); //delete db key
									$deequip->bindParam(":u", $localuser, PDO::PARAM_INT);
									$deequip->bindParam(":a", $wearingasset, PDO::PARAM_INT);
									$deequip->execute();
								}
								
								$equip = $GLOBALS['pdo']->prepare("INSERT INTO wearing_items(uid,aid,whenWorn) VALUES(:u,:a,UNIX_TIMESTAMP())");
								$equip->bindParam(":u", $localuser, PDO::PARAM_INT);
								$equip->bindParam(":a", $assetId, PDO::PARAM_INT);
								$equip->execute();

								rerenderutility();
							}
						}
						else
						{
							return "Item is Moderated";
						}
					}
					else
					{
						return "Slow down!";
					}
				}
				else
				{
					return "Thumbnail Server is offline";
				}
			}
			else
			{
				return "Already wearing this item";
			}
		}
		else
		{
			return "Error Occurred";
		}
	}
	return true;
}

function itemSalesCount($id)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	$check = $check->fetch(PDO::FETCH_OBJ);
	
	return $check->Sales;
}

function giveCurrency($amount, $userid)
{
	//log the transaction
	$info = "Gave user ".$userid." ".$amount;
	$log = $GLOBALS['pdo']->prepare("INSERT INTO transaction_logs (info, amount, userid, whenTransaction) VALUES (:info, :amount, :userid, UNIX_TIMESTAMP())");
	$log->bindParam(":info", $info, PDO::PARAM_STR);
	$log->bindParam(":amount", $amount, PDO::PARAM_INT);
	$log->bindParam(":userid", $userid, PDO::PARAM_INT);
	$log->execute();

	$check = $GLOBALS['pdo']->prepare("UPDATE users SET currency = (currency + :u) WHERE id = :i");
	$check->bindParam(":i", $userid, PDO::PARAM_INT);
	$check->bindParam(":u", $amount, PDO::PARAM_INT);
	$check->execute();
}

function removeCurrency($amount, $info="")
{
	$localuser = $GLOBALS['user']->id;
	$playercurrency = $GLOBALS['user']->currency;
	
	if ($playercurrency >= $amount) //if player currency is greater than or equal to the amount to remove
	{
		//log the transaction
		$log = $GLOBALS['pdo']->prepare("INSERT INTO transaction_logs (info, amount, userid, whenTransaction) VALUES (:info, :amount, :userid, UNIX_TIMESTAMP())");
		$log->bindParam(":info", $info, PDO::PARAM_STR);
		$log->bindParam(":amount", $amount, PDO::PARAM_INT);
		$log->bindParam(":userid", $localuser, PDO::PARAM_INT);
		$log->execute();

		//remove amount from user
		$check = $GLOBALS['pdo']->prepare("UPDATE users SET currency = (currency - :u) WHERE id = :i");
		$check->bindParam(":i", $localuser, PDO::PARAM_INT);
		$check->bindParam(":u", $amount, PDO::PARAM_INT);
		$check->execute();
		return true;
	}
	return false;
}

function giveItem($uid, $id)
{
	//give the user the item
	$setitem = $GLOBALS['pdo']->prepare("INSERT INTO owned_assets (uid, aid, when_sold, givenby) VALUES (:d, :a, UNIX_TIMESTAMP(), :b)");
	$setitem->bindParam(":d", $uid, PDO::PARAM_INT);
	$setitem->bindParam(":a", $id, PDO::PARAM_INT);
	$setitem->bindParam(":b", $GLOBALS['user']->id, PDO::PARAM_INT);
	if ($setitem->execute())
	{
		return true;
	}
	// ...
	return false;
}

function buyItem($id) //0 = not enough currency, 1 = already owned, 2 = bought, 3 = error
{
	$localuser = $GLOBALS['user']->id;
	$playercurrency = $GLOBALS['user']->currency;
	
	$iteminfo = getAssetInfo($id);
	$itemprice = $iteminfo->PriceInAlphabux;
	$itemcreator = $iteminfo->CreatorId;
	$onsale = $iteminfo->IsForSale;
	
	if (!isAssetModerated($id))
	{
		if ($onsale == 1) //if asset is onsale
		{
			if ($playercurrency >= $itemprice) //if the player has greater or equal amount of currency required
			{
				if (playerOwnsAsset($id)) //if player owns the asset
				{
					return 1; //already owned
				}
				else //everything passed, do the do
				{
					$tax = 0.30; //tax percentage
					$taxtoremove = 0;
					if ($itemcreator != 1) //we dont want to tax the account Alphaland items
					{
						$taxtoremove = $tax * $itemprice;
					}

					removeCurrency($itemprice, "Purchase of asset ".$id);
					
					//give creator of the item the currency, remove tax depending on the item
					$itemprice = $itemprice - $taxtoremove; //remove tax (if any)
					
					$check = $GLOBALS['pdo']->prepare("UPDATE users SET currency = (currency + :u) WHERE id = :i");
					$check->bindParam(":i", $itemcreator, PDO::PARAM_INT);
					$check->bindParam(":u", $itemprice, PDO::PARAM_INT);
					$check->execute();
					// ...
					
					//give the user the item
					$setitem = $GLOBALS['pdo']->prepare("INSERT INTO owned_assets (uid, aid, when_sold, givenby) VALUES (:d, :a, UNIX_TIMESTAMP(), :b)");
					$setitem->bindParam(":d", $localuser, PDO::PARAM_INT);
					$setitem->bindParam(":a", $id, PDO::PARAM_INT);
					$setitem->bindParam(":b", $itemcreator, PDO::PARAM_INT);
					$setitem->execute();
					// ...
					
					//sales + 1
					$sales = $GLOBALS['pdo']->prepare("UPDATE assets SET Sales = (Sales + 1) WHERE id = :i");
					$sales->bindParam(":i", $id, PDO::PARAM_INT);
					$sales->execute();
					// ...
					
					return 2; //bought
				}
			}
			else
			{
				return 0; //not enough currency
			}
		}
	}
	return 3;
}

function isOwner($id, $userid=NULL)
{
	if ($userid === NULL){
		$userid = $GLOBALS['user']->id;
	}

	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i");
	$check->bindParam(":i", $id, PDO::PARAM_INT);
	$check->execute();
	if ($check->rowCount() > 0)
	{
		$check = $check->fetch(PDO::FETCH_OBJ);
		$assettypeid = $check->AssetTypeId;
		$creatorid = $check->CreatorId;

		//handle game assets
		if ($assettypeid == 9) {
			if ($creatorid == $userid || $GLOBALS['user']->isOwner()) {
				return true;
			}
			return false;
		}
		
		//others
		if ($creatorid == $userid || $GLOBALS['user']->isStaff()) {
			return true;
		}
	}
	return false;
}

//end player appearance/inventory portion }

//friends portion {
	
function getFriends($uid) 
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM friends WHERE (rid = :u OR sid = :u2) AND valid = 1 ORDER BY id DESC");
	$check->bindParam(":u", $uid, PDO::PARAM_INT);
	$check->bindParam(":u2", $uid, PDO::PARAM_INT);
	$check->execute();
	return $check;
}

function getFriendRequests()
{
	if(isLoggedIn())
	{
		$localuser = $GLOBALS['user']->id;
		
		$check = $GLOBALS['pdo']->prepare("SELECT * FROM friend_requests WHERE (rid = :u) AND valid = 1");
		$check->bindParam(":u", $localuser, PDO::PARAM_INT);
		$check->execute();
		return $check;
	}
}

function friendsWithUser($user1, $user2) //for external requests not relying on cookies
{ 
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM friends WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2) AND valid = 1");
	$check->bindParam(":u", $user1, PDO::PARAM_INT);
	$check->bindParam(":u2", $user2, PDO::PARAM_INT);
	$check->bindParam(":ua", $user2, PDO::PARAM_INT);
	$check->bindParam(":ua2", $user1, PDO::PARAM_INT);
	$check->execute();
	
	if($check->rowCount() > 0) 
	{
		return true;
	}
}

function areUsersFriends($user1, $user2)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM friends WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2) AND valid = 1");
	$check->bindParam(":u", $user1, PDO::PARAM_INT);
	$check->bindParam(":u2", $user2, PDO::PARAM_INT);
	$check->bindParam(":ua", $user2, PDO::PARAM_INT);
	$check->bindParam(":ua2", $user1, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0) 
	{
		return true;
	}
	return false;
}

function friendsWith($user)
{ 
	if(isLoggedIn())
	{
		$localuser = $GLOBALS['user']->id;
		
		$check = $GLOBALS['pdo']->prepare("SELECT * FROM friends WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2) AND valid = 1");
		$check->bindParam(":u", $localuser, PDO::PARAM_INT);
		$check->bindParam(":u2", $user, PDO::PARAM_INT);
		$check->bindParam(":ua", $user, PDO::PARAM_INT);
		$check->bindParam(":ua2", $localuser, PDO::PARAM_INT);
		$check->execute();
		if($check->rowCount() > 0) 
		{
			return true;
		}
	}
	return false;
}

function friendsPending($user) //checks if the logged in user has a friend request sent to the user
{ 
	if(isLoggedIn())
	{
		if (!friendsWith($user))
		{
			$localuser = $GLOBALS['user']->id;
			
			$check = $GLOBALS['pdo']->prepare("SELECT * FROM friend_requests WHERE (rid = :u and sid = :u2) AND valid = 1");
			$check->bindParam(":u", $user, PDO::PARAM_INT);
			$check->bindParam(":u2", $localuser, PDO::PARAM_INT);
			$check->execute();
			if($check->rowCount() > 0) 
			{
				return true;
			}
		}
	}
	return false;
}

function userSentFriendReq($user) //checks if the logged in user has a friend request incoming from the user (this function is mostly for security)
{ 
	if(isLoggedIn())
	{
		if (!friendsWith($user))
		{
			$localuser = $GLOBALS['user']->id;
			
			$check = $GLOBALS['pdo']->prepare("SELECT * FROM friend_requests WHERE (rid = :u and sid = :u2) AND valid = 1");
			$check->bindParam(":u", $localuser, PDO::PARAM_INT);
			$check->bindParam(":u2", $user, PDO::PARAM_INT);
			$check->execute();
			if($check->rowCount() > 0) 
			{
				return true;
			}
		}
	}
	return false;
}

function invalidateFriendRequest($user) // return 0 = error, 1 = success
{
	if(isLoggedIn())
	{
		$localuser = $GLOBALS['user']->id;
		
		$delfriendrequest = $GLOBALS['pdo']->prepare("DELETE FROM friend_requests WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2)");
		$delfriendrequest->bindParam(":u", $localuser, PDO::PARAM_INT);
		$delfriendrequest->bindParam(":u2", $user, PDO::PARAM_INT);
		$delfriendrequest->bindParam(":ua", $user, PDO::PARAM_INT);
		$delfriendrequest->bindParam(":ua2", $localuser, PDO::PARAM_INT);
		$delfriendrequest->execute();
			
		if($delfriendrequest->execute())
		{
			return 1; //success
		}
		
		/*
		if (!userSentFriendReq($user))
		{
			return 0; //invalid
		}
		else
		{
			$delfriendrequest = $GLOBALS['pdo']->prepare("DELETE FROM friend_requests WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2)");
			$delfriendrequest->bindParam(":u", $localuser, PDO::PARAM_INT);
			$delfriendrequest->bindParam(":u2", $user, PDO::PARAM_INT);
			$delfriendrequest->bindParam(":ua", $user, PDO::PARAM_INT);
			$delfriendrequest->bindParam(":ua2", $localuser, PDO::PARAM_INT);
			$delfriendrequest->execute();
			
			if($delfriendrequest->execute())
			{
				return 1; //success
			}
		}
		*/
	}
	return 0; //error
}

function acceptFriendRequest($user) // return 0 = error, 1 = accepted, 2 = no friend request/invalid, 3 = already friends
{
	if(isLoggedIn())
	{
		$localuser = $GLOBALS['user']->id;

		if ($user == $localuser)
		{
			return 0;
		}
		elseif (friendsWith($user))
		{
			removeFriend($user);
			return 3; //already friends
		}
		elseif (!userSentFriendReq($user))
		{
			return 2; //no friend request/invalid
		}
		else
		{
			$send = $GLOBALS['pdo']->prepare("INSERT into friends(rid, sid, valid, whenAccepted) VALUES(:u, :u2, 1, UNIX_TIMESTAMP())");
			$send->bindParam(":u", $localuser, PDO::PARAM_INT);
			$send->bindParam(":u2", $user, PDO::PARAM_INT);
			if($send->execute())
			{
				invalidateFriendRequest($user);
				return 1; //accepted
			}
		}
	}
	return 0; //error
}

function CreateFriend($firstuserid, $seconduserid) //used on the game server
{
	if (!areUsersFriends($firstuserid, $seconduserid)) //gotta not be friends
	{
		if (userExists($firstuserid) && userExists($seconduserid))
		{
			$check = $GLOBALS['pdo']->prepare("SELECT * FROM friend_requests WHERE (rid = :u and sid = :u2) OR (rid = :u4 and sid = :u3) AND valid = 1");
			$check->bindParam(":u", $firstuserid, PDO::PARAM_INT);
			$check->bindParam(":u2", $seconduserid, PDO::PARAM_INT);
			$check->bindParam(":u3", $firstuserid, PDO::PARAM_INT);
			$check->bindParam(":u4", $seconduserid, PDO::PARAM_INT);
			$check->execute();
			if($check->rowCount() > 0) 
			{
				$delfriendrequest = $GLOBALS['pdo']->prepare("DELETE FROM friend_requests WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2)");
				$delfriendrequest->bindParam(":u", $firstuserid, PDO::PARAM_INT);
				$delfriendrequest->bindParam(":u2", $seconduserid, PDO::PARAM_INT);
				$delfriendrequest->bindParam(":ua", $seconduserid, PDO::PARAM_INT);
				$delfriendrequest->bindParam(":ua2", $firstuserid, PDO::PARAM_INT);
				$delfriendrequest->execute();
			}
			
			$newfriend = $GLOBALS['pdo']->prepare("INSERT into friends(rid, sid, valid, whenAccepted) VALUES(:u, :u2, 1, UNIX_TIMESTAMP())");
			$newfriend->bindParam(":u", $firstuserid, PDO::PARAM_INT);
			$newfriend->bindParam(":u2", $seconduserid, PDO::PARAM_INT);
			$newfriend->execute();
		}
	}
}

function BreakFriend($firstuserid, $seconduserid) //used on the game server
{
	$remove = $GLOBALS['pdo']->prepare("DELETE FROM friends WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2)");
	$remove->bindParam(":u", $firstuserid, PDO::PARAM_INT);
	$remove->bindParam(":u2", $seconduserid, PDO::PARAM_INT);
	$remove->bindParam(":ua", $seconduserid, PDO::PARAM_INT);
	$remove->bindParam(":ua2", $firstuserid, PDO::PARAM_INT);
	$remove->execute();
}

function sendFriendRequest($user) // return 0 = error, 1 = sent, 2 = already pending, 3 = already friends
{
	if(isLoggedIn())
	{
		$localuser = $GLOBALS['user']->id;

		if ($user == $localuser)
		{
			return 0;
		}
		elseif (friendsWith($user))
		{
			return 3; //already friends
		}
		elseif (friendsPending($user))
		{
			return 2; //already sent
		}
		else
		{
			$send = $GLOBALS['pdo']->prepare("INSERT into friend_requests(rid, sid, whenSent, valid) VALUES(:u2, :u, UNIX_TIMESTAMP(), 1)");
			$send->bindParam(":u", $localuser, PDO::PARAM_INT);
			$send->bindParam(":u2", $user, PDO::PARAM_INT);
			if($send->execute())
			{
				return 1; //sent
			}
		}
	}
	return 0; //error
}

function removeFriend($user) //return 0 = error, 1 = removed friend, 2 = not friends
{
	if(isLoggedIn())
	{
		$localuser = $GLOBALS['user']->id;
		
		if ($user == $localuser)
		{
			return 0;
		}
		elseif (!friendsWith($user))
		{
			return 2; //not friends
		}
		else
		{
			$send = $GLOBALS['pdo']->prepare("DELETE FROM friends WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2)");
			$send->bindParam(":u", $localuser, PDO::PARAM_INT);
			$send->bindParam(":u2", $user, PDO::PARAM_INT);
			$send->bindParam(":ua", $user, PDO::PARAM_INT);
			$send->bindParam(":ua2", $localuser, PDO::PARAM_INT);
			if($send->execute())
			{
				return 1; //removed friend
			}
			
			/*
			$send = $GLOBALS['pdo']->prepare("UPDATE friends SET valid = 0 WHERE (rid = :u and sid = :u2 OR rid = :ua and sid = :ua2) AND valid = 1");
			$send->bindParam(":u", $localuser, PDO::PARAM_INT);
			$send->bindParam(":u2", $user, PDO::PARAM_INT);
			$send->bindParam(":ua", $user, PDO::PARAM_INT);
			$send->bindParam(":ua2", $localuser, PDO::PARAM_INT);
			if($send->execute())
			{
				return 1; //removed friend
			}
			*/
		}
	}
	return 0; //error
}

//end friends portion }

//shouts {

function shoutCooldown()
{
	$localplayer = $GLOBALS['user']->id;
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM user_shouts WHERE uid = :u ORDER BY whenShouted DESC LIMIT 1");
	$check->bindParam(":u", $localplayer, PDO::PARAM_INT);
	$check->execute();
	if ($check->rowCount() > 0) {
		$interval = (int)$check->fetch(PDO::FETCH_OBJ)->whenShouted;
		if($interval + 60 > time()) {
			return true;
		}
	}
	return false;
}

function setShout($newshout)
{
	$localplayer = $GLOBALS['user']->id;

	if (shoutCooldown()) //ratelimit
	{
		return "Please wait before posting again";
	}
	if (empty($newshout) || ctype_space($newshout)) //we no want empty shouts or just spaces
	{
		return "Invalid shout, can't be empty";
	}
	elseif (strlen($newshout)>128) //limit 128 characters
	{
		return "Invalid shout, must be below 128 Characters";
	}
	elseif (strlen($newshout)<5) //must be over 5 characters
	{
		return "Invalid shout, must be above 5 Characters";
	}
	else
	{
		$data = ['shout' => cleanInput($newshout), 'uid' => $localplayer];
		$shout = $GLOBALS['pdo']->prepare("INSERT into user_shouts (shout, uid, whenShouted) VALUES(:shout, :uid, UNIX_TIMESTAMP())");
		if ($shout->execute($data))
		{
			return true;
		}
	}
	return "Error Occurred";
}

function userShout($uid)
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM `user_shouts` WHERE uid = :u ORDER BY whenShouted DESC LIMIT 1");
	$check->bindParam(":u", $uid, PDO::PARAM_INT);
	$check->execute();
	
	if ($check->rowCount() > 0)
	{
		$check = $check->fetch(PDO::FETCH_OBJ);
		
		if (!empty($check->shout) or !ctype_space($check->shout)) //detect empty shout or shout with just spaces
		{
			return cleanOutput($check->shout);
		}
	}
	return "";
}
	
//end of shouts }

//games portion {

function setPBSGame($placeid)
{
	$set = $GLOBALS['pdo']->prepare("UPDATE assets SET isPersonalServer = 1 WHERE id = :i");
	$set->bindParam(":i", $placeid, PDO::PARAM_INT);
	$set->execute();
	if ($set->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function setRegularGame($placeid)
{
	$set = $GLOBALS['pdo']->prepare("UPDATE assets SET isPersonalServer = 0 WHERE id = :i");
	$set->bindParam(":i", $placeid, PDO::PARAM_INT);
	$set->execute();
	if ($set->rowCount() > 0)
	{
		return true;
	}
	return false;
}
	
function createPlace($userid, $name, $description, $maxplayers)
{
	//file locations
	$defaultplacedir = $GLOBALS['defaultPlacesPath'] . 'default.rbxl';
	$assetcdn = $GLOBALS['assetCDNPath'];
						
	//grab a new hash for the game asset
	$gamehash = genAssetHash(16);
	
	//copy the default place to the assets cdn
	copy($defaultplacedir, $assetcdn . $gamehash);
	
	//one of the random thumbs
	$thumb = rand(4, 6); 
	
	return CreateAsset(
		9, //AssetTypeId
		$thumb, //IconImageAssetId
		NULL, //TargetId(not used atm)
		NULL, //ProductType(idk what to do with this atm)
		cleanInput($name), //Name
		cleanInput($description), //Description
		time(), //Created
		time(), //Updated
		$userid, //CreatorId
		0, //PriceInAlphabux
		0, //Sales
		false, //isPersonalServer
		false, //IsNew
		false, //IsForSale
		false, //IsPublicDomain
		false, //IsLimited
		false, //IsLimitedUnique
		true, //IsCommentsEnabled
		true, //IsApproved
		false, //IsModerated
		0, //Remaining
		0, //MinimumMembershipLevel
		0, //ContentRatingTypeId
		0, //Favorited
		0, //Visited
		$maxplayers, //MaxPlayers
		0, //UpVotes
		0, //DownVotes
		$gamehash, //Hash
		NULL //ThumbHash
	);
}

function createPBSPlace($userid, $name, $description, $maxplayers, $path)
{
	//file locations
	$assetcdn = $GLOBALS['assetCDNPath'];
						
	//grab a new hash for the game asset
	$gamehash = genAssetHash(16);
	
	//copy the default place to the assets cdn
	copy($path, $assetcdn . $gamehash);
	
	//one of the random thumbs (until rendered)
	$thumb = rand(4, 6); 
	
	$newpbs = CreateAsset(
		9, //AssetTypeId
		$thumb, //IconImageAssetId
		NULL, //TargetId(not used atm)
		NULL, //ProductType(idk what to do with this atm)
		cleanInput($name), //Name
		cleanInput($description), //Description
		time(), //Created
		time(), //Updated
		$userid, //CreatorId
		0, //PriceInAlphabux
		0, //Sales
		true, //isPersonalServer
		false, //IsNew
		false, //IsForSale
		false, //IsPublicDomain
		false, //IsLimited
		false, //IsLimitedUnique
		true, //IsCommentsEnabled
		true, //IsApproved
		false, //IsModerated
		0, //Remaining
		0, //MinimumMembershipLevel
		0, //ContentRatingTypeId
		0, //Favorited
		0, //Visited
		$maxplayers, //MaxPlayers
		0, //UpVotes
		0, //DownVotes
		$gamehash, //Hash
		NULL //ThumbHash
	);
	handleRenderPlace($newpbs);
	return $newpbs;
}
	
function getAllGames($uid) {
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE AssetTypeId = 9 AND CreatorId = :i");
	$check->bindParam(":i", $uid, PDO::PARAM_INT);
	$check->execute();
	return $check;
}

function userPlaceVisits($userid)
{
	$games = $GLOBALS['pdo']->prepare("SELECT SUM(Visited) FROM `assets` WHERE `AssetTypeId` = 9 AND `CreatorId` = :userid");
	$games->bindParam(":userid", $userid, PDO::PARAM_INT);
	$games->execute();
	return $games->fetch(PDO::FETCH_NUM)[0];
	
}

function getAllSiteGames()
{
	$check = $GLOBALS['pdo']->query("SELECT * FROM assets WHERE AssetTypeId = 9 ORDER BY Visited DESC");
	return $check;
}

function getRecentlyPlayed()
{
	$localuser = $GLOBALS['user']->id;
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM game_recents WHERE uid = :u ORDER by whenPlayed DESC");
	$check->bindParam(":u", $localuser, PDO::PARAM_INT);
	$check->execute();
	return $check;
}

function gamePlayerCount($id)
{
	$sQ = $GLOBALS['pdo']->prepare("SELECT * FROM open_servers WHERE status != 2 AND gameID = :i");
	$sQ->bindParam(":i", $id, PDO::PARAM_INT);
	$sQ->execute();
	
	if($sQ->rowCount() > 0) 
	{
		$servers = $sQ->fetchAll(PDO::FETCH_ASSOC);
	
		foreach($servers as $server) // TODO: re-work this when i implement job-id based presence
		{
			$p = $GLOBALS['pdo']->prepare("SELECT * FROM game_presence WHERE placeid = :p AND (lastPing + 50) > UNIX_TIMESTAMP()");
			$p->bindParam(":p", $id, PDO::PARAM_INT);
			$p->execute();
			
			return $p->rowCount();
		}
	}
	else
	{
		return 0;
	}
}

function jobPlayerCount($placeid, $jobid)
{
	$p = $GLOBALS['pdo']->prepare("SELECT * FROM game_presence WHERE placeid = :p AND jobid = :j AND (lastPing + 50) > UNIX_TIMESTAMP()");
	$p->bindParam(":p", $placeid, PDO::PARAM_INT);
	$p->bindParam(":j", $jobid, PDO::PARAM_STR);
	$p->execute();
	return $p->rowCount();
}
//end games portion }

//utility {
	
function enableMaintenance($custom)
{
	if (!empty($custom)) {
		$setmaintenance = $GLOBALS['pdo']->prepare("UPDATE websettings SET maintenance = 1, maintenance_text = :t");
		$setmaintenance->bindParam(":t", $custom, PDO::PARAM_STR);
		$setmaintenance->execute();
	} else {
		$setmaintenance = $GLOBALS['pdo']->prepare("UPDATE websettings SET maintenance = 1");
		$setmaintenance->execute();
	}

	soapCloseAllJobs($GLOBALS['gamesArbiter']);
}

function disableMaintenance()
{
	$setmaintenance = $GLOBALS['pdo']->prepare("UPDATE websettings SET maintenance = 0, maintenance_text = ''");
	$setmaintenance->execute();
}

function setUserRank($rank, $userid)
{
	$updaterank = $GLOBALS['pdo']->prepare("UPDATE users SET rank = :r WHERE id = :i");
	$updaterank->bindParam(":r", $rank, PDO::PARAM_INT);
	$updaterank->bindParam(":i", $userid, PDO::PARAM_INT);
	$updaterank->execute();
}

function isAdmin() { //todo: make these use userids
	if($GLOBALS['user']->rank == 2) {
		return true;
	}
	return false;
}

function isStaff() {
	if($GLOBALS['user']->rank == 1 || $GLOBALS['user']->rank == 2) {
		return true;
	}
	return false;
}

function onlineUsersCount() 
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM users"); 
	$check->execute();
	
	$count = 0;
	foreach ($check as $user)
	{
		$info = userInfo($user['id']); // add true as a second param if u wanna use usernames instead
		if (($info->lastseen + 120) > time())
		{
			$count = $count + 1;
		}
	}
	return $count;
}

function getUsername($id) {
	$get = $GLOBALS['pdo']->prepare("SELECT username FROM users WHERE id = :u");
	$get->bindParam(":u", $id, PDO::PARAM_STR);
	$get->execute();
	if($get->rowCount() > 0) {
		$id = $get->fetch(PDO::FETCH_OBJ);
		return $id->username;
	}
	return false; //user not found
}

function userExists($id)
{
	$get = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :i");
	$get->bindParam(":i", $id, PDO::PARAM_INT);
	$get->execute();
	if($get->rowCount() > 0) 
	{
		return true;
	}
	return false;
}

function userInfo($id, $useID = true) {
	if($useID) {
		$get = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :u");
	} else {
		$get = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE username = :u");
	}
	$get->bindParam(":u", $id, PDO::PARAM_STR);
	$get->execute();
	if($get->rowCount() > 0) {
		$id = $get->fetch(PDO::FETCH_OBJ);
		return $id;
	}
	return false; //user not found
}

function isLoggedIn()
{
	if($GLOBALS['user']->logged_in)
	{
		return true;
	}
	return false;
}

function getID($username) {
	$get = $GLOBALS['pdo']->prepare("SELECT id FROM users WHERE username = :u");
	$get->bindParam(":u", $username, PDO::PARAM_STR);
	$get->execute();
	if($get->rowCount() > 0) {
		$id = $get->fetch(PDO::FETCH_OBJ);
		return $id->id;
	}
	return false; //user not found
}

function passwordCorrect($userID, $password) {
	$check = $GLOBALS['pdo']->prepare("SELECT pwd FROM users WHERE id = :i");
	$check->bindParam(":i", $userID, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0) {
		$passwordb = $check->fetch(PDO::FETCH_OBJ);
		if(password_verify($password, $passwordb->pwd)) {
			return true; //correct
		}
		return false; //incorrect password
	}
	return false; // user not found
}

function createSession($userID) {
    $token = genSessionHash(128); //generate the auth token
	$ip = getIP();
	$user_agent = $_SERVER['HTTP_USER_AGENT'];
	   
	$session = $GLOBALS['pdo']->prepare("INSERT INTO sessions(token, uid, ip, whenCreated, user_agent)
										 VALUES(:t,:u,:i,UNIX_TIMESTAMP(),:ua)");
	$session->bindParam(":t", $token, PDO::PARAM_STR);
	$session->bindParam(":u", $userID, PDO::PARAM_INT);
	$session->bindParam(":i", $ip, PDO::PARAM_STR);
	$session->bindParam(":ua", $user_agent, PDO::PARAM_STR);
	if($session->execute()) {
		setcookie("token", $token, time() + (86400 * 30), "/", ".alphaland.cc"); //30 day expiration on token for (hopefully) all alphaland paths 
		$GLOBALS['user']->checkIfTokenValid($token);
		return true;
	} else {
		return false;
	}
}

function updateLastSeen($userID) {
	$updateLastSeen = $GLOBALS['pdo']->prepare("UPDATE users SET lastseen = UNIX_TIMESTAMP() WHERE id = :id");
	$updateLastSeen->bindParam(":id", $userID, PDO::PARAM_INT);
	if ($updateLastSeen->execute()) {
		return true;
	}
	return false;
}

function logoutAllSessions($userID) {
	$sessions = $GLOBALS['pdo']->prepare("UPDATE sessions SET valid = 0 WHERE uid = :uid");
	$sessions->bindParam(":uid", $userID, PDO::PARAM_INT);
	$sessions->execute();
}

function isValidPasswordResetToken($token)
{
	$passreset = $GLOBALS['pdo']->prepare("SELECT * FROM password_reset_keys WHERE token = :token");
	$passreset->bindParam(":token", $token, PDO::PARAM_STR);
	$passreset->execute();
	if ($passreset->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function getIP() {
	return (isset($_SERVER["HTTP_CF_CONNECTING_IP"])?$_SERVER["HTTP_CF_CONNECTING_IP"]:$_SERVER['REMOTE_ADDR']);
}

function pageHandler() {
	require 'pageHandler.php';
	$GLOBALS['ph'] = new page_handler();
}

function usernameExists($u) {
	$check = $GLOBALS['pdo']->prepare("SELECT COUNT(*) FROM users WHERE username = :u");
	$check->bindParam(":u", $u, PDO::PARAM_STR);
	$check->execute();
	if($check->fetchColumn(0) > 0) {
		return true;
	}
	return false;
}

function cleanInput($t) {
	//$t = trim(preg_replace('/\s+/', ' ', $t)); //new line
	return trim((string)$t);
}

function cleanOutput($t, $linebreaks=true) {
	$t = htmlentities($t);
	if ($linebreaks) {
		$t = nl2br($t);
	}
	$t = strip_tags($t, '<br>');
	return filterText($t);
}

function cleanOutputNoFilter($t, $linebreaks=true) {
	$t = htmlentities($t);
	if ($linebreaks) {
		$t = nl2br($t);
	}
	return strip_tags($t, '<br>');
}

//theme stuff
function setTheme($theme) //sets the users theme
{
	$localplayer = $GLOBALS['user']->id;
	$numberofthemes = 1;

	if ($theme > $numberofthemes)
	{
		return false;
	}
	else
	{
		$check = $GLOBALS['pdo']->prepare("UPDATE users SET theme = :t WHERE id = :i");
		$check->bindParam(":t", $theme, PDO::PARAM_INT);
		$check->bindParam(":i", $localplayer, PDO::PARAM_INT);
		if ($check->execute())
		{
			return true;
		}
	}
}

function getCurrentTheme() //returns the theme set (integer)
{
	$localplayer = $GLOBALS['user']->id;
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :i");
	$check->bindParam(":i", $localplayer, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0) 
	{
		$currenttheme = (int)$check->fetch(PDO::FETCH_OBJ)->theme;
		
		return $currenttheme;
	}
}

function getCurrentThemeLogo() //grabs the alphaland logo for the users selected theme
{
	$currenttheme = getCurrentTheme();

	return $GLOBALS['url'] . "/alphaland/cdn/imgs/alpha-christmas/alphalandchristmas.png"; //force christmas logo
	
	if ($currenttheme == 0) //light theme dark logo
	{
		return $GLOBALS['url'] . "/alphaland/cdn/imgs/alphaland-logo.svg";
	}
	elseif ($currenttheme == 1) //dark theme light logo
	{
		return $GLOBALS['url'] . "/alphaland/cdn/imgs/alphaland-white-1024.png";
	}
}

function getCurrentThemeAlphabuxLogo() //grabs the alphaland alphabux logo for the users selected theme
{
	$currenttheme = getCurrentTheme();
	
	if ($currenttheme == 0) //light theme dark logo
	{
		return $GLOBALS['url'] . "/alphaland/cdn/imgs/alphabux-black-1024.png";
	}
	elseif ($currenttheme == 1) //dark theme light logo
	{
		return $GLOBALS['url'] . "/alphaland/cdn/imgs/alphabux-grey-1024.png";
	}
}

function getCurrentThemeAnimatedLogo() //grabs the alphaland animated logo for the users selected theme
{
	$currenttheme = getCurrentTheme();
	
	if ($currenttheme == 0) //light theme dark logo
	{
		return $GLOBALS['url'] . "/alphaland/cdn/imgs/loading-dark.gif";
	}
	elseif ($currenttheme == 1) //dark theme light logo
	{
		return $GLOBALS['url'] . "/alphaland/cdn/imgs/loading-light.gif";
	}
}

function getCurrentThemeStyle() //grabs the style sheet for the users selected theme
{
	$localplayer = $GLOBALS['user']->id;
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :i");
	$check->bindParam(":i", $localplayer, PDO::PARAM_INT);
	$check->execute();
	if($check->rowCount() > 0) 
	{
		$currenttheme = (int)$check->fetch(PDO::FETCH_OBJ)->theme;
		
		if ($currenttheme == 1) //dark theme
		{
			return '<link rel="stylesheet" type="text/css" href="https://www.alphaland.cc/alphaland/css/stylesheet-dark.css?version='.$GLOBALS['cssversion'].'">';
		}
	}
}

function getCSS($studio=false) 
{
	$theme = "";
	if (!$studio)
	{
		$theme = getCurrentThemeStyle($studio);
	}

	return '
		<link rel="stylesheet" type="text/css" href="https://www.alphaland.cc/alphaland/css/stylesheet.css?version='.$GLOBALS['cssversion'].'">
		<link rel="stylesheet" type="text/css" href="https://www.alphaland.cc/alphaland/css/bootstrap.min.css?version='.$GLOBALS['cssversion'].'">
		'.$theme.'
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
		<script src="https://use.fontawesome.com/releases/v5.10.0/js/all.js" data-search-pseudo-elements></script>
		<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
		<script type="text/javascript" src="https://www.alphaland.cc/alphaland/js/bootstrap.min.js?version='.$GLOBALS['jsversion'].'"></script>
		<script type="text/javascript" src="https://www.alphaland.cc/alphaland/js/utilities.js?version='.$GLOBALS['jsversion'].'"></script>
		<script src="https://www.alphaland.cc/alphaland/js/snowfall/canvas-snow.js?version='.$GLOBALS['jsversion'].'"></script>';
		
}
//end theme stuff

//utilities

function getFooter() 
{
	$usercount = (int)onlineUsersCount();
	$year = date("Y");
	
	return '<div class="container mt-3">
	<div class="footer">
		<div class="container">
			<div class="row">
				<div class="col">
					<div class="container p-3">
						<div class="row">
							<div class="col-sm">
								<strong>Alphaland</strong> | <a style="color:grey;">'.$year.'</a>
							</div>
						</div>
						<div class="row">
							<div class="col-sm">
								<a style="color:green;"><i class="fas fa-globe"></i> '.$usercount.'</a>
							</div>
						</div>
						<div class="row border-bottom pb-1">
							<div class="col-sm">
								<a>Alphaland is not affiliated with Lego, ROBLOX, MegaBloks, Bionicle, Pokemon, Nintendo, Lincoln Logs, Yu Gi Oh, K\'nex, Tinkertoys, Erector Set, or the Pirates of the Caribbean. ARrrr!</a>
							</div>
						</div>
						<div class="row">
							<div class="col-sm">
								<a style="font-size: 1.6rem;color:red;" href="https://www.youtube.com/channel/UC5o1iJC9wonCWPvTvtORklg"><i class="fab fa-youtube"></i></a>
								<a style="font-size: 1.6rem;color:#1DA1F2;" href="https://twitter.com/_Alphaland"><i class="fab fa-twitter"></i></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
';
}

function getNav() 
{
	$logo = getCurrentThemeLogo();
	$friendreqcount = getFriendRequests();
	$announcement = fetchAnnouncement();
	$thumbnailerstatus = "";
	$gameserverstatus = "";

	$modbutton = "";
	$pendingassetscount = 0;
	if ($GLOBALS['user']->isStaff())
	{
		$pendingassetscount = fetchPendingAssets()->rowCount();

		$modbuttonbadge = "";
		if ($pendingassetscount > 0)
		{
			$modbuttonbadge = '
				<span class="badge badge-danger">'.$pendingassetscount.'</span>
			';
		}

		$modbutton = '
		<li class="nav-item">
			<a class="nav-link" style="font-size: 1rem;" href="/MCP/">
				Mod
				'.$modbuttonbadge.'
			</a>
		</li>
		';
	}

	if (WebContextManager::IsUnderMaintenance(true))
	{
		$maintenancestatus = "<div style='margin:0 auto;Overflow:hidden;text-align: center' class='alert alert-danger' role='alert'>MAINTENANCE MODE IS ENABLED</div>";
	}
	
	if(!isThumbnailerAlive())
	{
		$thumbnailerstatus = "<div style='margin:0 auto;Overflow:hidden;text-align: center' class='alert alert-danger' role='alert'>WARNING: Thumbnailer is offline, no Avatar changes will be applied</div>";
	}
	
	if (!isGameServerAlive())
	{
		$gameserverstatus = "<div style='margin:0 auto;Overflow:hidden;text-align: center' class='alert alert-danger' role='alert'>WARNING: Gameserver is offline, games will not launch</div>";
	}
	
	if(isLoggedIn())
	{
		$friendreqbadge = '';
		if ($friendreqcount->rowCount() > 0)
		{
			$friendreqbadge = '<span class="badge badge-danger"> '.$friendreqcount->rowCount().'';
		}
		
		$user = $GLOBALS['user'];
		return '
			<header>
				<nav class="navbar navbar-expand-lg navbar-light bg-light" style="padding-right:10%;padding-left:10%;">
					<a class="navbar-brand" href="/">
						<img src="'.$logo.'" width="40" height="40" class="d-inline-block align-top" alt="" loading="lazy">
					</a>
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
						<span class="navbar-toggler-icon"></span>
					</button>
					<div class="collapse navbar-collapse" id="navbarNav">
						<ul class="navbar-nav">
							<li class="nav-item">
								<a class="nav-link" href="/">Home</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/profile/">Profile</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/avatar">Avatar</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/catalog/">Catalog</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/games/">Games</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/users/">Users</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/groups/">Groups</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/create/">Create</a>
							</li>
							'.$modbutton.'
							'.(($user->isAdmin())? '
							<li class="nav-item">
								<a class="nav-link" href="https://crackpot.alphaland.cc">Admin</a>
							</li>':'').'
						</ul>
						<ul class="navbar-nav ml-auto">
							<li class="nav-item">
								<a class="nav-link mr-3" style="font-size: 1rem;" href="/friends/friend-requests">
									<i class="fas fa-user-friends"></i>
										'.$friendreqbadge.'
									</span>
								</a>
							</li>
							<li class="nav-item">
								<a class="nav-link mr-3" href=""><img style="width:1rem;" src="/alphaland/cdn/imgs/alphabux-grey-1024.png"> '.$user->currency.'</a>
							</li>
							<li class="nav-item dropdown">
								<a class="nav-link dropdown-toggle" href="" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">'.$user->name.'</a>
								<div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
									<a class="dropdown-item" href="/settings/">Settings</a>
									<div class="dropdown-divider"></div>
									<a class="dropdown-item" href="/logout">Log Out</a>
								</div>	
							</li>
						</ul>
					</div>
				</nav>
				'.$maintenancestatus.'
				'.$announcement.'
				'.$thumbnailerstatus.'
				'.$gameserverstatus.'
			</header>
			<script>
				setInterval(function(){ getJSONCDS("https://api.alphaland.cc/sitepresence/ping"); }, 60000); //ping every minute
			</script>
			<br/>';
	}
	return '
		<header>
			<nav class="navbar navbar-expand-lg navbar-light bg-light" style="padding-right:10%;padding-left:10%;">
				<a class="navbar-brand" href="/">
					<img src="/alphaland/cdn/imgs/alphaland-logo.svg" width="40" height="40" class="d-inline-block align-top" alt="" loading="lazy">
				</a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarNav">
					<ul class="navbar-nav">
						<li class="nav-item">
							<a class="nav-link" href="/">Home</a>
						</li>
					</ul>
				<ul class="navbar-nav ml-auto">
					<li class="nav-item">
						<a href="/login"><button class="btn btn-danger">Log In</button></a>
					</li>
				</ul>
				</div>
			</nav>
		</header><br />';
}

function fetchAnnouncement() 
{
	$announcementquery = $GLOBALS['pdo']->prepare("SELECT * FROM websettings");
	$announcementquery->execute();
	$announcementquery = $announcementquery->fetch(PDO::FETCH_OBJ);
	$announcement = cleanOutput($announcementquery->announcement); //clean output
	if (empty($announcementquery->announcement))
	{
		return "";
	}
	else
	{
		$html = "";
		if ($announcementquery->announcement_color == "red")
		{
			$html = "<div style='margin:0 auto;Overflow:hidden;text-align: center' class='alert alert-danger' role='alert'>{$announcement}</div>";
		}
		elseif ($announcementquery->announcement_color == "blue")
		{
			$html = "<div style='margin:0 auto;Overflow:hidden;text-align: center' class='alert alert-primary' role='alert'>{$announcement}</div>";
		}
		elseif ($announcementquery->announcement_color == "green")
		{
			$html = "<div style='margin:0 auto;Overflow:hidden;text-align: center' class='alert alert-success' role='alert'>{$announcement}</div>";
		}
		return $html;
	}
}

function canRegister()
{
	$check = $GLOBALS['pdo']->prepare("SELECT * FROM websettings WHERE registration = 1");
	$check->execute();
	
	if($check->rowCount() > 0)
	{
		return true;
	}
	return false;
}

function adminPanelStats() {
	$maintenancestatus = "ON";
	if (!WebContextManager::IsUnderMaintenance(true))
	{
		$maintenancestatus = "OFF";
	}

	$thumbnailerstatus = "OK";
	if(!isThumbnailerAlive())
	{
		$thumbnailerstatus = "DOWN";
	}

	$gameserverstatus = "OK";
	if (!isGameServerAlive())
	{
		$gameserverstatus = "DOWN";
	}

	$faults = $GLOBALS['pdo']->prepare("SELECT * FROM soap_faults");
	$faults->execute();
	$faults = $faults->rowCount();

	echo '
	<div>Account: '.getUsername($GLOBALS["user"]->id).'</div>
	<div>Server Date (EST): '.date("m/d/Y", time()).'</div>
	<div>Server Time (EST): '.shell_exec('TIME /T').'</div>
	<div>Server OS Version: Microsoft Windows Server 2012 R2 Standard</div>
	<div>NGINX Version: '.$_SERVER['SERVER_SOFTWARE'].'</div>
	<div>PHP Version: '.phpversion().'</div>
	<div>MySQL Version: '.shell_exec('mysql --version').'</div>
	<div>SOAP Faults: '.$faults.'</div>
	<div>Maintenance Status:  '.$maintenancestatus.'</div>
	<div>Gameserver Status: '.$gameserverstatus.'</div>
	<div>Thumbnail Server Status: '.$thumbnailerstatus.'</div>
	<hr>
	';
}

//end utility }
