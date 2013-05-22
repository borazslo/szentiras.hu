<?php
  $script = explode("&",$_SERVER['REQUEST_URI']);

  if(!(empty($reftrans) or empty($abbook))) {
	$pagetitle = $abbook." (".gettransname($db,$reftrans,'true').") | Szentírás"; 
	$content .= showbook($db, $reftrans, $abbook, listbook($db, $reftrans, $abbook));
 }

?>