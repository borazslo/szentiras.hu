<?php
//$_REQUEST['queryString']
include 'bibleconf.php';
include 'biblefunc.php';
include 'func.php';

if(mb_strlen($_REQUEST['queryString']) >= getvar('autosuggest_minlength')) {
	$query = "select texttosearch, resultcount from ".DBPREF."stats_search 
		where 
			texttosearch LIKE '%".$_REQUEST['queryString']."%' 
			AND reftrans = ".$_REQUEST['reftrans']." 
			AND searchtype <> 'quote'			
			AND resultcount > 0
			AND resultcount < 500			
			group by texttosearch 
			order by resultcount DESC LIMIT 7";
	global $db;
    $stmt = $db->prepare($query);
	$stmt->execute();
    $rs = $stmt->fetchAll(PDO::FETCH_CLASS);
echo '<ul>';
if(is_array($rs)) foreach($rs as $r) {
	        echo '<li onClick="fill(\''.addslashes($r->texttosearch).'\');">'.$r->texttosearch.' ('.$r->resultcount.')</li>';
	}
						
	         		
	echo '</ul>';

	} else {
	/*
		$min = getvar('autosuggest_minlength');
		echo 'Minimum '.$min.' karakter. Tehát még : '.($min - strlen($_REQUEST['queryString'])).' <i>'.rand(1,100).'</i>';
	*/
	}

?>