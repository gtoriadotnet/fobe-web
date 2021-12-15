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
                logSoapFault($soap, "Render Player Closeup ".$userid." Job", $script);
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
                logSoapFault($soap, "Render Player ".$userid." Job", $script);
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
                        if ($oldhash != $newhash && !isHeadshotThumbHashInOutfit($oldhash)) {
                            unlink($path . $oldhash);
                        }
                        $newthumbhash = $GLOBALS['pdo']->prepare("UPDATE users SET HeadshotThumbHash = :h, pendingHeadshotRender = 0, renderCount = renderCount-1 WHERE id = :i");
                        $newthumbhash->bindParam(":h", $newhash, PDO::PARAM_STR);
                        $newthumbhash->bindParam(":i", $userid, PDO::PARAM_INT);
                        $newthumbhash->execute();
                    } else {
                        $oldhash = $prevhash->ThumbHash;
                        if ($oldhash != $newhash && !isThumbHashInOutfit($oldhash)) {
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
