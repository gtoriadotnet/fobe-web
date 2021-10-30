<?php

$action = $_GET['Action'];
$userid = $_GET['UserID'];
$passid = $_GET['PassID'];

if ($action == "HasPass")
{
    header('Content-Type: text/xml');
    echo '<Value Type="boolean">false</Value>';	
}
