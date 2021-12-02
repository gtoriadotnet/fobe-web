<?php

use Alphaland\Web\WebContextManager;

$user->logout();
WebContextManager::Redirect("/");