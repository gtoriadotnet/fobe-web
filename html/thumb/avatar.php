<?php

$userID = (int)$_GET['userid'];
$height = (int)$_GET['height'];
$width = (int)$_GET['width'];

function show_image($image, $width, $height) 
{
    //resize image           
    $image = imagecreatefrompng ( $image );
	if (!$image)
	{
		return false;
	}
	else
	{
		$new_image = imagecreatetruecolor ( $width, $height ); // new wigth and height
		imagealphablending($new_image , false);
		imagesavealpha($new_image , true);
		imagecopyresampled ( $new_image, $image, 0, 0, 0, 0, $width, $height, imagesx ( $image ), imagesy ( $image ) );
		$image = $new_image;

		ob_end_clean();  // clean the output buffer ... if turned on.
		header('Content-Type: image/png');  
		imagepng($new_image); //you does not want to save.. just display
		imagedestroy($new_image); //but not needed, cause the script exit if function returns true
		return true;
	}
}

if ($userID | $height | $width)
{
	if ($height > 1920 | $width > 1920)
	{
		http_response_code(400);
		header('Content-Type: text/plain');
		exit('Invalid resolution.');
	}

	//grab the user's thumbnail hash to call it up from the CDN
	$userhash = $pdo->prepare("SELECT * FROM users WHERE id = :i");
	$userhash->bindParam(":i", $userID, PDO::PARAM_INT);
	$userhash->execute();
	$userhash = $userhash->fetch(PDO::FETCH_OBJ);
	$userhash = $userhash->HeadshotThumbHash;
	// ...
	
	//construct the path
	$path = $GLOBALS['renderCDNPath'] . $userhash;
	// ...
	
	show_image($path, $width, $height);
	//exit;
}