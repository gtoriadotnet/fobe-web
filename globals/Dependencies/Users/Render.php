<?php

/*
    Alphaland 2021
*/

namespace Alphaland\Users {

    use Alphaland\Common\HashingUtiltity;
    use Alphaland\Grid\RccServiceHelper;
    use Alphaland\UI\ImageHelper;
    use PDO;

    class Render
    {
        public static function SetRenderCount(int $userid, int $count)
        {
            $update = $GLOBALS['pdo']->prepare("UPDATE `users` SET `renderCount` = :count WHERE `id` = :userid");
            $update->bindParam(":count", $count, PDO::PARAM_INT);
            $update->bindParam(":userid", $userid, PDO::PARAM_INT);
            $update->execute();
        }

        public static function RenderCount(int $userid)
        {
            $userinfo = userInfo($userid);
            if (($userinfo->lastRender + 15) < time()) {
                Render::SetRenderCount($userid, 0);
            }
            return $userinfo->renderCount;
        }

        public static function RenderCooldown(int $userid)
        {
            if (Render::RenderCount($userid) > 3) {
                return true;
            }
            return false;
        }

        public static function PendingRender(int $userid)
        {
            $pending = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :u");
            $pending->bindParam(":u", $userid, PDO::PARAM_INT);
            $pending->execute();
            $pending = $pending->fetch(PDO::FETCH_OBJ);
        
            if ($pending->pendingRender) { //render pending
                if (($pending->lastRender + 15) < time()) { //if the render is stalled after 15 seconds
                    $update = $GLOBALS['pdo']->prepare("UPDATE users SET pendingRender = 0 WHERE id = :u");
                    $update->bindParam(":u", $userid, PDO::PARAM_INT);
                    $update->execute();
                }
                return true;
            }
            if ($pending->pendingHeadshotRender) { //headshot render pending
                if (($pending->lastHeadshotRender + 15) < time()) { //if the render is stalled after 15 seconds
                    $update = $GLOBALS['pdo']->prepare("UPDATE users SET pendingHeadshotRender = 0 WHERE id = :u");
                    $update->bindParam(":u", $userid, PDO::PARAM_INT);
                    $update->execute();
                }
                return true;
            }
            return false;
        }

        public static function RenderPlayerCloseup(int $userid, bool $fork=false)
        {
            if ($fork)
            {
                $job = popen("cd C:/Webserver/nginx/Alphaland/WebserviceTools/RenderTools && start /B php backgroundRenderJob.php ".$userid." avatarcloseup", "r"); //throwaway background process
                if ($job !== FALSE);
                {
                    pclose($job);
                    return true;
                }
                return false;
            }
            else
            {
                $script = file_get_contents($GLOBALS['avatarcloseupthumbnailscript']);
                
                $angleright = userInfo($userid)->headshotAngleRight;
                $angleleft = userInfo($userid)->headshotAngleLeft;
                
                $soap = new RccServiceHelper($GLOBALS['thumbnailArbiter']);
                $soap = $soap->BatchJobEx(
                    $soap->ConstructGenericJob(gen_uuid(), 25, 0, 3, "Render Player Closeup ".$userid, $script, array(
                        $userid,
                        "https://www.alphaland.cc/",
                        "https://api.alphaland.cc/users/avatar-accoutrements?userId=".$userid,
                        "png",
                        "840",
                        "840",
                        (bool)$angleright, //angleRight
                        (bool)$angleleft //angleLeft
                    ))
                );
        
                if (!is_soap_fault($soap)) {
                    Render::Update($userid, $soap, true);
                    return true;
                } else {
                    die(print_r($soap));
                }
                return false;
            }
        }

        public static function RenderPlayer(int $userid, bool $fork=false)
        {
            if ($fork)
            {
                $job = popen("cd C:/Webserver/nginx/Alphaland/WebserviceTools/RenderTools && start /B php backgroundRenderJob.php ".$userid." avatar", "r"); //throwaway background process
                if ($job !== FALSE);
                {
                    pclose($job);
                    return true;
                }
                return false;
            }
            else
            {
                Render::RenderPlayerCloseup($userid, true); //run in the background so it will *hopefully* finish with this
                $script = file_get_contents($GLOBALS['avatarthumbnailscript']);
                
                $soap = new RccServiceHelper($GLOBALS['thumbnailArbiter']);
                $soap = $soap->BatchJobEx(
                    $soap->ConstructGenericJob(gen_uuid(), 25, 0, 3, "Render Player ".$userid, $script, array(
                        $userid,
                        "https://api.alphaland.cc/users/avatar-accoutrements?userId=".$userid,
                        "https://www.alphaland.cc/",
                        "png",
                        "840",
                        "840"
                    ))
                );
        
                if (!is_soap_fault($soap)) {
                    Render::Update($userid, $soap);
                    return true;
                }
                return false;
            }
        }

        public static function Update(int $userid, $soapobject, $headshot=false)
        {
            $path = $GLOBALS['renderCDNPath'];
            $render = base64_decode($soapobject->BatchJobExResult->LuaValue[0]->value); //returned by rcc
		
			if (ImageHelper::IsBase64PNGImage($render)) //PNG
			{
				$newhash = HashingUtiltity::VerifyMD5(md5($render));
				if (ImageHelper::ResizeImageFromString(352 , 352 , $path . $newhash, $render)) //scale down for a SLIGHT AA effect
				{
					$prevhash = $GLOBALS['pdo']->prepare("SELECT * FROM users WHERE id = :i");
					$prevhash->bindParam(":i", $userid, PDO::PARAM_INT);
					$prevhash->execute();
					$prevhash = $prevhash->fetch(PDO::FETCH_OBJ);

                    if ($headshot) {
                        $oldhash = $prevhash->HeadshotThumbHash;
                        if ($oldhash != $newhash && !Outfit::HeadshotThumbHashInOutfit($oldhash)) {
                            unlink($path . $oldhash);
                        }
                        $newthumbhash = $GLOBALS['pdo']->prepare("UPDATE users SET HeadshotThumbHash = :h, pendingHeadshotRender = 0, renderCount = renderCount-1 WHERE id = :i");
                        $newthumbhash->bindParam(":h", $newhash, PDO::PARAM_STR);
                        $newthumbhash->bindParam(":i", $userid, PDO::PARAM_INT);
                        $newthumbhash->execute();
                    } else {
                        $oldhash = $prevhash->ThumbHash;
                        if ($oldhash != $newhash && !Outfit::ThumbHashInOutfit($oldhash)) {
                            unlink($path . $oldhash);
                        }
                        $newthumbhash = $GLOBALS['pdo']->prepare("UPDATE users SET ThumbHash = :h, pendingRender = 0, renderCount = renderCount-1 WHERE id = :i");
                        $newthumbhash->bindParam(":h", $newhash, PDO::PARAM_STR);
                        $newthumbhash->bindParam(":i", $userid, PDO::PARAM_INT);
                        $newthumbhash->execute();
                    }
					return true;
				}
			}
        }
    }
}
