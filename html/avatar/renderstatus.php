<?php

if (checkUserPendingRender($user->id))
{
	echo 'pending';
}