<?php

/*
    Alphaland 2021
*/

namespace Alphaland\Games {

    use Alphaland\Common\Signing;

    class Ticket
    {
        public static function ClientTicket(array $arguments)
        {
            //is this a bad method of doing this?
            if (sizeof($arguments) == 5) {
                $userid = $arguments[0];
                $accountage = $arguments[1];
                $username = $arguments[2];
                $characterappearance = $arguments[3];
                $jobid = $arguments[4];

                $ticket = "";

                if ($userid && 
                userExists($userid) && 
                $accountage && 
                $username && 
                getUsername($userid) == $username && 
                $characterappearance && 
                $jobid) {
                    $timestamp = date("m/d/Y h:m:s A", time());
                    $sig1 = Signing::SignData($userid . "\n" . $accountage . "\n" . $username . "\n" . $characterappearance . "\n" . $jobid . "\n" . $timestamp, false);
                    $sig2 = Signing::SignData($userid . "\n" . $jobid . "\n" . $timestamp, false);
                    $ticket = $timestamp.";".$sig1.";".$sig2;
                }
            }
            return $ticket;
        }
    }
}
