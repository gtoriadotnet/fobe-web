<?php

namespace Fobe\Administration {

    use Fobe\Grid\RccServiceHelper;
    use PDO;

    class Maintenance
    {
        public static function Enable($text = "Fobe is currently under maintenance, check back later.")
        {
            $setmaintenance = $GLOBALS['pdo']->prepare("UPDATE websettingsdeprecated SET maintenance = 1, maintenance_text = :t");
            $setmaintenance->bindParam(":t", $text, PDO::PARAM_STR);
            $setmaintenance->execute();

            $jobClose = new RccServiceHelper($GLOBALS['gamesArbiter']);
            $jobClose->CloseAllJobs();
        }

        public static function Disable()
        {
            $setmaintenance = $GLOBALS['pdo']->prepare("UPDATE websettingsdeprecated SET maintenance = 0, maintenance_text = ''");
            $setmaintenance->execute();
        }
    }
}
