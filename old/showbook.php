<?php
  $script = explode("&",$_SERVER['REQUEST_URI']);

  if(!(empty($transid) or empty($bookid))) {
  
	$pagetitle = $GLOBALS['tdbook'][$transid][$bookid]['abbrev']." (".gettransname($transid,'true').") | Szentírás"; 
	$content .= showbook($transid,$GLOBALS['tdbook'][$transid][$bookid]['abbrev'],listbook($transid, $GLOBALS['tdbook'][$transid][$bookid]['abbrev']));
 }

?>