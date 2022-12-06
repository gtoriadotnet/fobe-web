<?php


/*
Fobe 2021 
*/

use Fobe\Users\Render;

if (Render::PendingRender($user->id))
{
	echo 'pending';
}