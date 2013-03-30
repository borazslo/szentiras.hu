<?php
//header("Content-type: image/png");

$q     = $_REQUEST['q'];
$font  = "Impact.ttf";
$size  = 30;
$bbox   = imageftbbox($size, 0, $font, $q);

$width  = $bbox[2] - $bbox[6];
$height = $bbox[3] - $bbox[7];

$im    = imagecreatetruecolor($width, $height);
$green = imagecolorallocate($im, 60, 240, 60);

imagefttext($im, $size, 0, -$bbox[6], -$bbox[7], $green, $font, $q);
imagepng($im);
imagedestroy($im);
?>