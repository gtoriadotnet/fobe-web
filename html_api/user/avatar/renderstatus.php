<?php


/*
Finobe 2021 
*/

use Finobe\Users\Render;

if (Render::PendingRender($user->id))
{
	echo 'pending';
}