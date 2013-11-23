<?php
  $script = explode("&",$_SERVER['REQUEST_URI']);

	$pagetitle = gettransname($reftrans,'true')." | Szentírás"; 
    $content .= showtrans($reftrans,listtrans($reftrans));

?>