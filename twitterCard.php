<?php
require('functions.php');
$config = include('config.php');

// Get thread ID
$id = htmlspecialchars($_GET['id']);

// Do we already have that in the cache? If so, just show us that!
if(file_exists('cache/thread-' . $id . '.png')) {
	header("Content-type: image/png");
	readfile('cache/thread-' . $id . '.png');
	exit;
}

// Create a stream
$opts = array(
  'http'=>array(
    'method'=>"GET",
    'header'=>"XF-Api-Key: " . $config['api_key'] . "\r\n",
    'ssl' => array('verify_peer'=>false, 'verify_peer_name'=>false)
  )
);

// Connect to API & get thread JSON
$context = stream_context_create($opts);
$file = file_get_contents('https://webwide.io/api/threads/' . $id . '/', false, $context);
$threadData = json_decode($file);

// Thread data
$title = truncate(clean($threadData->thread->title), 80);
$username = $threadData->thread->username;
$forum = $threadData->thread->Forum->title;
$subtitle = $username . ' posted a thread in ' . $forum . '...';
$avatar = $threadData->thread->User->{'avatar_urls'}->l;

// If user has no avatar, show a default one instead
if($avatar == '') {
	$avatar = 'assets/default-avatar.jpg';
}

// Card settings
$card_width = 800; // pixels
$card_height = 418 + 0; // pixels
$background = array(255, 255, 255);
$font = 34.5;
$line_height = 52;
$padding = 32;
$angle = 0;

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

// Break title in to lines
$title_options = text_to_lines($title, $card_width);

// Add title line by line
foreach($title_options['lines'] as $line){
    imagettftext($image, $font, $angle, $padding, $i, $dark, 'assets/OpenSans-ExtraBold.ttf', trim($line));
    $i += $line_height;
}

$i += $padding;

// Create round image from profile picture
$src = imagecreatefromjpeg($avatar);
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

// Cache it
imagepng($image, 'cache/thread-' . $id . '.png');

// Print image to browser
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);

exit;