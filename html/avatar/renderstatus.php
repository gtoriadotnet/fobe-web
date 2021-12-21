<?php

use Alphaland\Users\Render;

if (Render::PendingRender($user->id))
{
	echo 'pending';
}