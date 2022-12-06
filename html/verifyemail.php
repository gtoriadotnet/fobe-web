<?php

use Fobe\Web\WebContextManager;

$token = (int)$_GET['token'];
verifyEmail($token);
WebContextManager::Redirect("/settings");