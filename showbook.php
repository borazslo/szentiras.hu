<?php
  $script = explode("&",$_SERVER['REQUEST_URI']);

  if(!(empty($reftrans) or empty($abbook))) {
	$pagetitle = $abbook." (".gettransname($reftrans,'true').") | Szentírás"; 
	$content .= showbook($reftrans,$abbook,listbook($reftrans, $abbook));
 }

?>