<?php


/*
Alphaland 2021 
*/

use Alphaland\Users\Render;

if (Render::PendingRender($user->id))
{
	echo 'pending';
}