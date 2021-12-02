<?php

use Alphaland\Web\WebContextManager;

$token = (int)$_GET['token'];
verifyEmail($token);
WebContextManager::Redirect("/settings");