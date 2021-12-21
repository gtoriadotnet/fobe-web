<?php

use Alphaland\Moderation\Filter;

header('Content-Type: application/json');

$text = cleanInput($_POST['text']);
$userid = $_POST['userId'];

if (!$text || !$userid)
{
	die(json_encode(array("success" => false)));
}

if (Filter::IsTextFiltered($text))
{
	logChatMessage($userid, $text, true);

	if (chatFilterInfractionLimit($userid, 3, 120)) //3 infraction within 2 minutes
	{
		die(kickUserIfInGame($userid, "'".$text."' is not appropriate on Alphaland, continued infractions will lead to a ban."));
	}

	$text = Filter::FilterText($text);
	//$text = "[ Content Deleted ]";
}

$return = json_encode(array(
	"success" => true,
	"data" => array(
		"AgeUnder13" => $text,
		"Age13OrOver" => $text
	)
), JSON_UNESCAPED_SLASHES);

echo $return;