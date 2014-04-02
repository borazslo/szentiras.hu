<?php
  $script = explode("&",$_SERVER['REQUEST_URI']);

	$pagetitle = gettransname($reftrans,'true')." | Szentírás"; 
	
	$query = "select name, abbrev, oldtest FROM ".DBPREF."tdbook where trans = $reftrans order by id";
	$stmt = $db->prepare($query);
	$stmt->execute();
	$rs = $stmt->fetchAll(PDO::FETCH_CLASS);
	
    $content .= showtrans($reftrans,$rs);


?>