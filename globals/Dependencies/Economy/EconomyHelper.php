<?php

/*
    Fobe 2021
*/

namespace Fobe\Economy {

    use Fobe\Assets\Asset;
    use PDO;
    use Fobe\Users\User;
    use Exception;

class EconomyHelper
    {
        const tax = 0.30;

        public static function LogTransaction(int $amount, int $userid, string $description)
        {
            $log = $GLOBALS['pdo']->prepare("INSERT INTO transaction_logs (info, amount, userid, whenTransaction) VALUES (:info, :amount, :userid, UNIX_TIMESTAMP())");
            $log->bindParam(":info", $description, PDO::PARAM_STR);
            $log->bindParam(":amount", $amount, PDO::PARAM_INT);
            $log->bindParam(":userid", $userid, PDO::PARAM_INT);
            if ($log->execute()) {
                return true;
            }
            return false;
        }

        public static function HasEnoughAlphabux(int $amount, int $userid)
        {
            if (User::GetUserInfo($userid)->currency >= $amount) {
                return true;
            }
            return false;
        }

        public static function GiveAlphabux(int $amount, int $userid, $description="")
        {
            if (EconomyHelper::LogTransaction($amount, $userid, $description)) {
                $check = $GLOBALS['pdo']->prepare("UPDATE users SET currency = (currency + :u) WHERE id = :i");
                $check->bindParam(":i", $userid, PDO::PARAM_INT);
                $check->bindParam(":u", $amount, PDO::PARAM_INT);
                $check->execute();
                if ($check->rowCount() > 0) {
                    return true;
                }
            }
            return false;
        }

        public static function RemoveAlphabux(int $amount, int $userid, string $description="")
        {
            if (EconomyHelper::HasEnoughAlphabux($amount, $userid)) {
                if (EconomyHelper::LogTransaction($amount, $userid, $description)) {
                    $check = $GLOBALS['pdo']->prepare("UPDATE users SET currency = (currency - :u) WHERE id = :i");
                    $check->bindParam(":i", $userid, PDO::PARAM_INT);
                    $check->bindParam(":u", $amount, PDO::PARAM_INT);
                    $check->execute();
                    if ($check->rowCount() > 0) {
                        return true;
                    }
                }
            }
            return false;
        }

        public static function PurchaseItem(int $userid, int $assetid)
        {
            $assetInfo = Asset::GetAssetInfo($assetid);
            if (!$assetInfo || 
            !$assetInfo->IsForSale || 
            User::OwnsAsset($userid, $assetid) || 
            Asset::IsModerated($assetid)) {
                throw new Exception('Error occurred');
            } else if (!EconomyHelper::HasEnoughAlphabux($assetInfo->PriceInAlphabux, $userid)) {
                throw new Exception('You do not have enough Alphabux to purchase this item');
            } else {
                $creatorid = $assetInfo->CreatorId;
                $price = $assetInfo->PriceInAlphabux;

                if (!EconomyHelper::RemoveAlphabux($price, $userid, "Giving item ".$assetid)) {
                    throw new Exception('');
                }

                //tax calc
                if ($creatorid != 1) {
                    $price = $price - EconomyHelper::tax * $price;
                }
                
                if (!EconomyHelper::GiveAlphabux($price, $creatorid, "Giving item purchase ".$assetid." Alphabux to creatorid ".$creatorid)) {
                    throw new Exception('');
                } else if (!Asset::GiveAsset($assetid, $userid, $creatorid)) {
                    throw new Exception('');
                }
                
                if (Asset::AddSale($assetid)) {
                    return true;
                }
                return false;
            }
        }
    }
}
