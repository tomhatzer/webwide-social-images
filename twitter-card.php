<?php
/**
    This code uses parts of this: https://stackoverflow.com/questions/50446492/how-to-add-line-breaks-br-in-imagestring-text-to-image-in-php
    and parts of this: https://stackoverflow.com/questions/37096710/merge-two-images-and-round-corner-in-php

    Thanks to the original authors!

    @TODO: Fetch the content (title + text) from the API using the https://webwide.io/api/threads/235 url or likewise
    @TODO: Fetch user profile image using api calls and returned urls
    @TOOD: Add a little more details to the card and maybe make it better looking
    @TODO: Refactor code so it doesn't look like it is currently looking...
    @TODO: Fix bug for empty lines. Padding is not added for those lines so the image height may be too low.
**/

/**
* PNG ALPHA CHANNEL SUPPORT for imagecopymerge();
* by Sina Salek
*
* Bugfix by Ralph Voigt (bug which causes it
* to work only for $src_x = $src_y = 0.
* Also, inverting opacity is not necessary.)
* 08-JAN-2011
*
**/
    function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct){
        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);

        // copying relevant section from background to the cut resource
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
       
        // copying relevant section from watermark to the cut resource
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
       
        // insert cut resource to destination image
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
    }

function truncate($text, $chars = 25) {
    if (strlen($text) <= $chars) {
        return $text;
    }
    $text = $text." ";
    $text = substr($text,0,$chars);
    $text = substr($text,0,strrpos($text,' '));
    $text = $text."...";
    return $text;
}

$title = truncate('Where would you send a customer with little? Where would you send a customer with little? Where would you send a customer with little?', 82);

$username = 'Adam';
$forum = 'New Member Introductions';

$subtitle = $username . ' posted a thread in ' . $forum . '...';

$card_width = 800; // pixels
$card_height = 418 + 0; // pixels

$font = 34.5;
$line_height = 52;
$padding = 32;
$angle = 0;

text_to_image($title, $text, $card_width, $card_height);

function text_to_lines($text, $card_width) {
    // Wrap text by word
    $wrapped_text = wordwrap($text, (($card_width - 40) / 34.5));
    $lines = explode("\n", $wrapped_text);

    return [
        'text' => $text,
        'wrapped_text' => $wrapped_text,
        'lines' => $lines,
        'line_count' => count($lines),
    ];
}

function text_to_image($title, $subtitle, $card_width, $card_height, $background = array(255, 255, 255))
{
    global $font, $line_height, $padding, $angle, $subtitle;

    $title_options = text_to_lines($title, $card_width);
    $text_options = text_to_lines($text, $card_width);

    // Create blank image to print onto
    $image = imagecreatefrompng('assets/twitter-template.png');
    $background = imagecolorallocate($image, $background[0], $background[1], $background[2]);
    $colour = imagecolorallocate($image,$colour[0],$colour[1],$colour[2]);
    imagefill($image, 0, 0, $background);
    $i = $padding + 75;

	$light = imagecolorallocate($image, 112, 124, 123);
	$dark = imagecolorallocate($image, 37, 44, 44);
	
	// Add subtitle
	imagettftext($image, 14.5, $angle, $padding, 47, $light, 'assets/OpenSans-ExtraBold.ttf', $subtitle);

	// Add title line by line
    foreach($title_options['lines'] as $line){
        imagettftext($image, $font, $angle, $padding, $i, $dark, 'assets/OpenSans-ExtraBold.ttf', trim($line));
        $i += $line_height;
    }
    


    $i += $padding;

    // Create round image from profile picture
    $src = imagecreatefromjpeg('profile.jpg');
    $src = imagescale($src, 100, 100);
    $src_width = imagesx($src);
    $src_height = imagesy($src);
    $dstX = $card_width - $src_width - $padding;
    $dstY = 33;
    $srcX = 0;
    $srcY = 0;
    $pct = 100;

    // Create image mask
    $mask = imagecreatefrompng('assets/circle-mask-100.png');
    imagecopymerge_alpha($src, $mask, 0, 0, 0, 0, $src_width, 100, 100);
    imagedestroy($mask);

    // merge the two images to create the result.
    imagecopymerge_alpha($image, $src, $dstX, $dstY, $srcX, $srcY, $src_width, $src_height, $pct);

    // Print image to browser
    header("Content-type: image/png");
    imagepng($image);
    imagedestroy($image);
    exit;
}