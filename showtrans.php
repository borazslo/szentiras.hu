<?php
  $script = explode("&",$_SERVER['REQUEST_URI']);

	$pagetitle = gettransname($db,$reftrans,'true')." | Szentírás"; 
	$content .= showtrans($db, $reftrans, listtrans($db, $reftrans));

?>