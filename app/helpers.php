<?php
/**
 * Created by PhpStorm.
 * User: Siebe
 * Date: 30/10/2016
 * Time: 1:56
 */

/**
 * Flash a toastr message
 *
 * @param $style
 * @param $title
 * @param $content
 */
function flashToastr($style, $title, $content)
{
	$newContent = $content;
	if (is_array($content))
	{
		$newContent = "";
		foreach ($content as $contentMessage)
		{
			$newContent .= "<p>" . $contentMessage . "</p>";
		}
	}
	$message = [
		"style"   => $style,
		"title"   => $title,
		"content" => $newContent
	];
	session()->flash('messageToastr', $message);
}