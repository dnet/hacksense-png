<?php

/*

 Copyright (c) 2009 András Veres-Szentkirályi

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.

*/

$ch = curl_init('http://vsza.hu/hacksense/status.csv');
if (file_exists('cache.id')) {
	$last_id = file_get_contents('cache.id');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("If-None-Match: $last_id"));
}
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$status = curl_exec($ch);

if (!empty($status)) {
	$status_data = explode(';', $status);

	$im = imagecreatetruecolor(240, 70);

	$red   = imagecolorallocate($im, 0xFF, 0x00, 0x00);
	$white = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
	$black = imagecolorallocate($im, 0x00, 0x00, 0x00);
	$green = imagecolorallocate($im, 0x90, 0xEE, 0x90);

	if ($ok) {
		$open = $status_data[2][0] == 1;
		imagefill($im, 0, 0, $open ? $green : $red);
		// Text
		$txt = $open ? $black : $white;
		imagestring($im, 5, 7, 7,  'H.A.C.K. is', $txt);
		imagestring($im, 5, 7, 27, 'currently ' . ($open ? 'OPEN' : 'CLOSED'), $txt);
		imagestring($im, 5, 7, 47, 'since ' . $status_data[1], $txt);
		// Lock icon -- source: Debian kde-icons-crystalproject package
		// /usr/share/icons/crystalproject/32x32/actions/(en|de)crypted.png
		$icon = imagecreatefrompng(($open ? 'en' : 'de') . 'crypted.png');
		imagecopy($im, $icon, 180, 10, 0, 0, 32, 32);
		imagedestroy($icon);
	} else {
		imagefill($im, 0, 0, $red);
		imagestring($im, 5, 7, 7, "Couldn't connect to HSAPI", $white);
	}

	imagecolordeallocate($im, $red);
	imagecolordeallocate($im, $white);
	imagecolordeallocate($im, $black);
	imagecolordeallocate($im, $green);

	imagepng($im, 'cache.png');
	imagedestroy($im);
	$last_id = $status_data[0];
	file_put_contents('cache.id', $last_id);
} elseif (isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
		$_SERVER['HTTP_IF_NONE_MATCH'] == $last_id) {
	header('HTTP/1.0 304 Not Modified');
	die();
}

header('ETag: ' . $last_id);
header('Content-type: image/png');
readfile('cache.png');

?>
