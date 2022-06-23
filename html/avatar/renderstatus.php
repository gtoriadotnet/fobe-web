<?php

use Finobe\Users\Render;

if (Render::PendingRender($user->id))
{
	echo 'pending';
}