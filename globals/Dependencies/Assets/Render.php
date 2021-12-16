<?php

/*
    Alphaland 2021
*/

namespace Alphaland\Assets {

    use Alphaland\Common\HashingUtiltity;
    use Alphaland\Grid\RccServiceHelper;
    use Alphaland\UI\ImageHelper;
    use PDO;

    class Render
    {
        public static function RenderHat(int $assetid, bool $fork=false)
        {
            if ($fork) {
                $job = popen("cd C:/Webserver/nginx/Alphaland/WebserviceTools/RenderTools && start /B php backgroundRenderJob.php ".$assetid." hat", "r"); //throwaway background process
                if ($job !== FALSE); {
                    pclose($job);
                    return true;
                }
                return false;
            } else {
                $thumbnailScript = file_get_contents($GLOBALS['hatthumbnailscript']);
                $soap = new RccServiceHelper($GLOBALS['thumbnailArbiter']);
                $soap = $soap->BatchJobEx(
                    $soap->ConstructGenericJob(gen_uuid(), 25, 0, 3, "Render Hat ".$assetid, $thumbnailScript, array(
                        $assetid,
                        "https://www.alphaland.cc/asset/?id=".$assetid,
                        "https://www.alphaland.cc/",
                        "png",
                        "750",
                        "750"
                    ))
                );

                if (!is_soap_fault($soap)) {
                    Render::Update($assetid, $soap);
                    return true;
                }
                logSoapFault($soap, "Render Hat ".$assetid." Job", $thumbnailScript);
                return false;
            }
        }

        public static function RenderTShirt(int $assetid, bool $fork=false)
        {
            if ($fork) {
                $job = popen("cd C:/Webserver/nginx/Alphaland/WebserviceTools/RenderTools && start /B php backgroundRenderJob.php ".$assetid." tshirt", "r"); //throwaway background process
                if ($job !== FALSE); {
                    pclose($job);
                    return true;
                }
                return false;
            } else {
                $thumbnailScript = file_get_contents($GLOBALS['tshirtthumbnailscript']);
                $soap = new RccServiceHelper($GLOBALS['thumbnailArbiter']);
                $soap = $soap->BatchJobEx(
                    $soap->ConstructGenericJob(gen_uuid(), 25, 0, 3, "Render TShirt ".$assetid, $thumbnailScript, array(
                        $assetid,
                        "https://www.alphaland.cc/asset/?id=".$assetid,
                        "https://www.alphaland.cc/asset/?id=38",
                        "https://www.alphaland.cc/",
                        "png",
                        "750",
                        "750"
                    ))
                );

                if (!is_soap_fault($soap)) {
                    Render::Update($assetid, $soap);
                    return true;
                }
                logSoapFault($soap, "Render TShirt ".$assetid." Job", $thumbnailScript);
                return false;
            }
        }

        public static function RenderShirt(int $assetid, bool $fork=false)
        {
            if ($fork) {
                $job = popen("cd C:/Webserver/nginx/Alphaland/WebserviceTools/RenderTools && start /B php backgroundRenderJob.php ".$assetid." shirt", "r"); //throwaway background process
                if ($job !== FALSE); {
                    pclose($job);
                    return true;
                }
                return false;
            } else {
                $thumbnailScript = file_get_contents($GLOBALS['shirtthumbnailscript']);
                $soap = new RccServiceHelper($GLOBALS['thumbnailArbiter']);
                $soap = $soap->BatchJobEx(
                    $soap->ConstructGenericJob(gen_uuid(), 25, 0, 3, "Render Shirt ".$assetid, $thumbnailScript, array(
                        $assetid,
                        "https://www.alphaland.cc/asset/?id=".$assetid,
                        "https://www.alphaland.cc/asset/?id=38",
                        "https://www.alphaland.cc/",
                        "png",
                        "750",
                        "750"
                    ))
                );

                if (!is_soap_fault($soap)) {
                    Render::Update($assetid, $soap);
                    return true;
                }
                logSoapFault($soap, "Render Shirt ".$assetid." Job", $thumbnailScript);
                return false;
            }
        }

        public static function RenderPants(int $assetid, bool $fork=false)
        {
            if ($fork) {
                $job = popen("cd C:/Webserver/nginx/Alphaland/WebserviceTools/RenderTools && start /B php backgroundRenderJob.php ".$assetid." pants", "r"); //throwaway background process
                if ($job !== FALSE); {
                    pclose($job);
                    return true;
                }
                return false;
            } else {
                $thumbnailScript = file_get_contents($GLOBALS['pantsthumbnailscript']);
                $soap = new RccServiceHelper($GLOBALS['thumbnailArbiter']);
                $soap = $soap->BatchJobEx(
                    $soap->ConstructGenericJob(gen_uuid(), 25, 0, 3, "Render Pants ".$assetid, $thumbnailScript, array(
                        $assetid,
                        "https://www.alphaland.cc/asset/?id=".$assetid,
                        "https://www.alphaland.cc/asset/?id=38",
                        "https://www.alphaland.cc/",
                        "png",
                        "750",
                        "750"
                    ))
                );

                if (!is_soap_fault($soap)) {
                    Render::Update($assetid, $soap);
                    return true;
                }
                logSoapFault($soap, "Render Pants ".$assetid." Job", $thumbnailScript);
                return false;
            }
        }

        public static function RenderFace(int $assetid, bool $fork=false)
        {
            if ($fork) {
                $job = popen("cd C:/Webserver/nginx/Alphaland/WebserviceTools/RenderTools && start /B php backgroundRenderJob.php ".$assetid." face", "r"); //throwaway background process
                if ($job !== FALSE); {
                    pclose($job);
                    return true;
                }
                return false;
            } else {
                $thumbnailScript = file_get_contents($GLOBALS['facethumbnailscript']);
                $soap = new RccServiceHelper($GLOBALS['thumbnailArbiter']);
                $soap = $soap->BatchJobEx(
                    $soap->ConstructGenericJob(gen_uuid(), 25, 0, 3, "Render Face ".$assetid, $thumbnailScript, array(
                        $assetid,
                        "https://www.alphaland.cc/asset/?id=".$assetid,
                        "https://www.alphaland.cc/",
                        "png",
                        "750",
                        "750"
                    ))
                );

                if (!is_soap_fault($soap)) {
                    Render::Update($assetid, $soap);
                    return true;
                }
                logSoapFault($soap, "Render Face ".$assetid." Job", $thumbnailScript);
                return false;
            }
        }

        public static function RenderHead(int $assetid, bool $fork=false)
        {
            if ($fork) {
                $job = popen("cd C:/Webserver/nginx/Alphaland/WebserviceTools/RenderTools && start /B php backgroundRenderJob.php ".$assetid." head", "r"); //throwaway background process
                if ($job !== FALSE); {
                    pclose($job);
                    return true;
                }
                return false;
            } else {
                $thumbnailScript = file_get_contents($GLOBALS['headthumbnailscript']);
                $soap = new RccServiceHelper($GLOBALS['thumbnailArbiter']);
                $soap = $soap->BatchJobEx(
                    $soap->ConstructGenericJob(gen_uuid(), 25, 0, 3, "Render Head ".$assetid, $thumbnailScript, array(
                        $assetid,
                        "https://www.alphaland.cc/asset/?id=".$assetid,
                        "https://www.alphaland.cc/asset/?id=38",
                        "https://www.alphaland.cc/",
                        "png",
                        "750",
                        "750"
                    ))
                );

                if (!is_soap_fault($soap)) {
                    Render::Update($assetid, $soap);
                    return true;
                }
                logSoapFault($soap, "Render Head ".$assetid." Job", $thumbnailScript);
                return false;
            }
        }

        public static function RenderGear(int $assetid, bool $fork=false)
        {
            if ($fork) {
                $job = popen("cd C:/Webserver/nginx/Alphaland/WebserviceTools/RenderTools && start /B php backgroundRenderJob.php ".$assetid." gear", "r"); //throwaway background process
                if ($job !== FALSE); {
                    pclose($job);
                    return true;
                }
                return false;
            } else {
                $thumbnailScript = file_get_contents($GLOBALS['gearthumbnailscript']);
                $soap = new RccServiceHelper($GLOBALS['thumbnailArbiter']);
                $soap = $soap->BatchJobEx(
                    $soap->ConstructGenericJob(gen_uuid(), 25, 0, 3, "Render Gear ".$assetid, $thumbnailScript, array(
                        $assetid,
                        "https://www.alphaland.cc/asset/?id=".$assetid,
                        "png",
                        "750",
                        "750",
                        "https://www.alphaland.cc/"
                    ))
                );

                if (!is_soap_fault($soap)) {
                    Render::Update($assetid, $soap);
                    return true;
                }
                logSoapFault($soap, "Render Gear ".$assetid." Job", $thumbnailScript);
                return false;
            }
        }

        public static function RenderPackage(int $assetid, bool $fork=false)
        {
            if ($fork) {
                $job = popen("cd C:/Webserver/nginx/Alphaland/WebserviceTools/RenderTools && start /B php backgroundRenderJob.php ".$assetid." package", "r"); //throwaway background process
                if ($job !== FALSE); {
                    pclose($job);
                    return true;
                }
                return false;
            } else {
                $thumbnailScript = file_get_contents($GLOBALS['packagescript']);
                $soap = new RccServiceHelper($GLOBALS['thumbnailArbiter']);
                $soap = $soap->BatchJobEx(
                    $soap->ConstructGenericJob(gen_uuid(), 25, 0, 3, "Render Package ".$assetid, $thumbnailScript, array(
                        $assetid,
                        "https://www.alphaland.cc/asset/?id=27112025;https://www.alphaland.cc/asset/?id=27112039;https://www.alphaland.cc/asset/?id=27112052",
                        "https://www.alphaland.cc/",
                        "https://www.alphaland.cc/asset/?id=38",
                        "https://www.alphaland.cc/",
                        "png",
                        "768",
                        "432"
                    ))
                );

                if (!is_soap_fault($soap)) {
                    Render::Update($assetid, $soap);
                    return true;
                }
                logSoapFault($soap, "Render Package ".$assetid." Job", $thumbnailScript);
                return false;
            }
        }

        public static function RenderModel(int $assetid, bool $fork=false)
        {
            if ($fork) {
                $job = popen("cd C:/Webserver/nginx/Alphaland/WebserviceTools/RenderTools && start /B php backgroundRenderJob.php ".$assetid." model", "r"); //throwaway background process
                if ($job !== FALSE); {
                    pclose($job);
                    return true;
                }
                return false;
            } else {
                $thumbnailScript = file_get_contents($GLOBALS['modelthumbnailscript']);
                $soap = new RccServiceHelper($GLOBALS['thumbnailArbiter']);
                $soap = $soap->BatchJobEx(
                    $soap->ConstructGenericJob(gen_uuid(), 25, 0, 3, "Render Model ".$assetid, $thumbnailScript, array(
                        $assetid,
                        "https://www.alphaland.cc/asset/?id=".$assetid,
                        "https://www.alphaland.cc/",
                        "png",
                        "768",
                        "432"
                    ))
                );

                if (!is_soap_fault($soap)) {
                    Render::Update($assetid, $soap);
                    return true;
                }
                logSoapFault($soap, "Render Model ".$assetid." Job", $thumbnailScript);
                return false;
            }
        }

        public static function RenderMesh(int $assetid, bool $fork=false)
        {
            if ($fork) {
                $job = popen("cd C:/Webserver/nginx/Alphaland/WebserviceTools/RenderTools && start /B php backgroundRenderJob.php ".$assetid." mesh", "r"); //throwaway background process
                if ($job !== FALSE); {
                    pclose($job);
                    return true;
                }
                return false;
            } else {
                $thumbnailScript = file_get_contents($GLOBALS['meshthumbnailscript']);
                $soap = new RccServiceHelper($GLOBALS['thumbnailArbiter']);
                $soap = $soap->BatchJobEx(
                    $soap->ConstructGenericJob(gen_uuid(), 25, 0, 3, "Render Mesh ".$assetid, $thumbnailScript, array(
                        $assetid,
                        "https://www.alphaland.cc/asset/?id=".$assetid,
                        "https://www.alphaland.cc/",
                        "png",
                        "768",
                        "432"
                    ))
                );

                if (!is_soap_fault($soap)) {
                    Render::Update($assetid, $soap);
                    return true;
                }
                logSoapFault($soap, "Render Mesh ".$assetid." Job", $thumbnailScript);
                return false;
            }
        }

        public static function RenderPlace(int $assetid, bool $fork=false)
        {
            if ($fork) {
                $job = popen("cd C:/Webserver/nginx/Alphaland/WebserviceTools/RenderTools && start /B php backgroundRenderJob.php ".$assetid." place", "r"); //throwaway background process
                if ($job !== FALSE); {
                    pclose($job);
                    return true;
                }
                return false;
            } else {
                $thumbnailScript = file_get_contents($GLOBALS['placethumbnailscript']);
                $soap = new RccServiceHelper($GLOBALS['thumbnailArbiter']);
                $soap = $soap->BatchJobEx(
                    $soap->ConstructGenericJob(gen_uuid(), 25, 0, 3, "Render Place ".$assetid, $thumbnailScript, array(
                        $assetid,
                        "https://www.alphaland.cc/asset/?id=".$assetid,
                        "https://www.alphaland.cc/",
                        "png",
                        "768",
                        "432"
                    ))
                );

                if (!is_soap_fault($soap)) {
                    Render::Update($assetid, $soap, true);
                    return true;
                }
                logSoapFault($soap, "Render Place ".$assetid." Job", $thumbnailScript);
                return false;
            }
        }

        public static function Update(int $assetid, $soapobject, $placerender=false)
        {
            $rendersPath = $GLOBALS['renderCDNPath'];
            $render = base64_decode($soapobject->BatchJobExResult->LuaValue[0]->value);

            if (ImageHelper::IsBase64PNGImage($render)) //PNG
            {
                $newhash = HashingUtiltity::VerifyMD5(md5($render));
                if (!file_get_contents($rendersPath . $newhash))
                {
                    if (file_put_contents($rendersPath . $newhash, $render))
                    {
                        if ($placerender) {
                            if (getAssetInfo($assetid)->isPersonalServer == 1) {
                                $render = imagecreatefrompng($rendersPath . $newhash);
                                $overlay = imagecreatefrompng($GLOBALS['pbsOverlayPath']);
                                ImageHelper::CopyMergeImageAlpha($render, $overlay, 0, 0, 0, 0, imagesx($render), imagesy($render), 100);
                                if (!imagepng($render, $rendersPath . $newhash)) {
                                    return false;
                                }
                            }
                        }

                        //delete old hash
                        $prevhash = $GLOBALS['pdo']->prepare("SELECT * FROM assets WHERE id = :i");
                        $prevhash->bindParam(":i", $assetid, PDO::PARAM_INT);
                        $prevhash->execute();
                        $prevhash = $prevhash->fetch(PDO::FETCH_OBJ);
                        $oldhash = $prevhash->ThumbHash;
                        unlink($rendersPath . $oldhash);

                        if ($placerender) {
                            //update place thumbhash n details
                            $c = $GLOBALS['pdo']->prepare("UPDATE assets SET isPlaceRendered = 1, IconImageAssetId = 0, ThumbHash = :n WHERE id = :i");
                            $c->bindParam(":n", $newhash, PDO::PARAM_INT); //item price
                            $c->bindParam(":i", $assetid, PDO::PARAM_INT); //catalog id
                            $c->execute();
                        } else {
                            //set new hash
                            $newthumbhash = $GLOBALS['pdo']->prepare("UPDATE assets SET ThumbHash = :h WHERE id = :i");
                            $newthumbhash->bindParam(":h", $newhash, PDO::PARAM_STR);
                            $newthumbhash->bindParam(":i", $assetid, PDO::PARAM_INT);
                            $newthumbhash->execute();
                        }
                        return true;
                    } 
                }
            }
            return false;
        }
    }
}
