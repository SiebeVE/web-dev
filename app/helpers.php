<?php
/**
 * Created by PhpStorm.
 * User: Siebe
 * Date: 30/10/2016
 * Time: 1:56
 */
use Hashids\Hashids;

/**
 * Flash a toastr message
 *
 * @param $style
 * @param $title
 * @param $content
 */
function flashToastr($style, $title, $content)
{
	$stringyContent = $content;
	if (is_array($content))
	{
		// If the content is an array, convert it to a string
		$stringyContent = "";
		foreach ($content as $contentMessage)
		{
			// Add some paragraphs for nice reading
			$stringyContent .= "<p>" . $contentMessage . "</p>";
		}
	}
	// Set the settings
	$message = [
		"style"   => $style,
		"title"   => $title,
		"content" => $stringyContent
	];
	// Put it in the session for blade to pick it up
	session()->flash('messageToastr', $message);
}

/**
 * Decode the hash for the id
 *
 * @param $hash
 *
 * @param string $extra
 *
 * @return array
 */
function decodeHash($hash, $extra = "")
{
	$decodeHash = new Hashids(env("HASH_SECRET", "MySecretKey").$extra, 15);
	return $decodeHash->decode($hash);
}

/**
 * Hash the id
 *
 * @param $id
 *
 * @param string $extra
 *
 * @return Hashids
 */
function hashId($id, $extra = "")
{
	$hashedId = new Hashids(env("HASH_SECRET", "MySecretKey").$extra, 15);
	$hashedId = $hashedId->encode($id);

	return $hashedId;
}