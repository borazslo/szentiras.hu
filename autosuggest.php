<?php
//$_REQUEST['queryString']
include 'bibleconf.php';
	$query = "select texttosearch, resultcount from ".DBPREF."stats_search 
		where 
			searchtype <> 'quote'
			AND reftrans = ".$_REQUEST['reftrans']." 
			AND resultcount > 0
			AND resultcount < 500
			AND texttosearch LIKE '%".$_REQUEST['queryString']."%' 
			group by texttosearch 
			order by resultcount DESC LIMIT 7";
	global $db;
    $stmt = $db->prepare($query);
	$stmt->execute();
    $rs = $stmt->fetchAll(PDO::FETCH_CLASS);
echo '<ul>';
foreach($rs as $r) {
	        echo '<li onClick="fill(\''.addslashes($r->texttosearch).'\');">'.$r->texttosearch.' ('.$r->resultcount.')</li>';
	}
						
	         		
	echo '</ul>';

?>