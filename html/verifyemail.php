<?php

$token = (int)$_GET['token'];
verifyEmail($token);
redirect("/settings");