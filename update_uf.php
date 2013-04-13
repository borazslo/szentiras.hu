<?php

	$url = "http://www.parokia.hu/bible/body.php?book=1&chapter=2";
	$file = file_get_contents($url);
	print_R($file);

echo 'ok';
exit;

?>