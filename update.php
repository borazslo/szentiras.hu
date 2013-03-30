<?php
include_once 'quote.php';

/* csv bedolgozása *
$result = db_query("SELECT abbrev, oldtest FROM tdbook WHERE reftrans = 3  AND oldtest = 1 ORDER BY oldtest DESC, bookorder");
foreach($result as $key => $res) $books[1][($key+1)] = $res['abbrev'];
$result = db_query("SELECT abbrev, oldtest FROM tdbook WHERE reftrans = 3  AND oldtest = 0 ORDER BY oldtest DESC, bookorder");
foreach($result as $key => $res) $books[2][($key+1)] = $res['abbrev'];


$file = file_get_contents("import/KNB_szovegforras.csv");
$file = preg_replace("/(([\$]){1}\n|[\$]{1})/","",$file);
$sorok = explode("\n", $file);

unset($sorok[0]);
unset($sorok[1]);


foreach($sorok as $key=>$sor) {
	//if($key>100) break;
	set_time_limit(60);
	if($sor != '') {
		$fields = explode('	',$sor);
		
		$code = $fields[1];
		
		preg_match('/[{]{1}(.*?)[}]{1}/',$fields[6],$match);
		if(count($match)>1) $update[$fields[1]]['w']['refs'] = $match[1];
		
		$update[$fields[1]]['w']['reftrans'] = 3;
		$update[$fields[1]]['w']['abbook'] = $books[$code[0]][(int)($code[1].$code[2])];
		$update[$fields[1]]['w']['numch'] = (int)($code[3].$code[4].$code[5]);
		$update[$fields[1]]['w']['numv'] = (int)($code[6].$code[7].$code[8]);
		
		if($fields[5]==6) $update[$fields[1]]['s']['verse'] = $fields[6];
		elseif($fields[5] < 4) {
			//$update[$fields[1]]['s']['title'] .= "<h".$fields[5].">".$fields[6]."</h".$fields[5].">";
			if(isset($update[$fields[1]]['s']['title'])) $update[$fields[1]]['s']['title'] .= "<br>".$fields[6];
			else $update[$fields[1]]['s']['title'] = $fields[6];
		}
		
	}
}

foreach ($update as $up) {
	$set = array(); $where = array(); $insert = array();
	foreach($up['s'] as $n=>$v) { $set[] = $n.' = "'.$v.'"'; $insert['name'][] = $n; $insert['value'][] = $v; }
	foreach($up['w'] as $n=>$v) { $where[] = $n.' = "'.$v.'"'; $insert['name'][] = $n; $insert['value'][] = $v; }
	
	$query = "SELECT * FROM tdverse WHERE  ".implode(' AND ',$where)."  LIMIT 1";
	$result = db_query($query);
	if(is_Array($result)) {
		$query = "UPDATE tdverse SET ".implode(', ',$set)." WHERE ".implode(' AND ',$where)." LIMIT 1";
		db_query($query);
		$content .= $query."<br>\n";
	} else {
		$query = "INSERT INTO tdverse (".implode(',',$insert['name']).") VALUES ('".implode("','",$insert['value'])."');";
		db_query($query);
		$content .= $query."<br>\n";
	}

}
/**/

/* ékezetek kijavítása*
for($i=1;$i<110;$i++) {
$quer = 'SELECT * FROM tdverse LIMIT '.(($i-1)*1000).','.($i*1000);
$rows = db_query($quer);


if(is_array($rows)) {
foreach($rows as $key=>$row) {
	//if($key>100) break;
	set_time_limit(60);
	//echo $row['did']."<br>";
	$newverse = $row['verse'];
	$newtitle = $row['title'];
	$ekezet=array("á" => "á", "é" => "é", "í" => "í", "ó" => "ó", "ú" => "ú", "ö" => "ö", "ő" => "ő", "ô" => "ő", "ô" => "ő", "õ" => "ő", "ü" => "ü", "ű" => "ű", "ũ" => "ű", "û" => "ű", "Á" => "Á", "É" => "É", "Í" => "Í", "Ó" => "Ó", "Ú" => "Ú", "Ö" => "Ö", "Ő" => "Ő", "Ô" => "Ő", "Õ" => "Ő", "Ô" => "Ő", "Ü" => "Ü", "Ű" => "Ű", "Ũ" => "Ű", "Û" => "Ű"); 
	foreach($ekezet as $k => $v) { 
		$newverse=str_replace($k,$v,$newverse); 
		$newtitle=str_replace($k,$v,$newtitle); 
	}
	$newverse = preg_replace("/[']{1}/","\'",$newverse);
	

	if($newverse != $row['verse']) {
			$query = "UPDATE tdverse SET verse = '".$newverse."' WHERE did = ".$row['did']." LIMIT 1";
			echo $query."<br>\n";
			db_query($query);
	}
	if($newtitle != $row['title']) {
			$query = "UPDATE tdverse SET title = '".$newtitle."' WHERE did = ".$row['did']." LIMIT 1";
			echo $query."<br>\n";
			db_query($query);
	}
	
} } }
/* */ 

/* fejezetek ÉS VERSEK mennyisége */
$books = db_query('SELECT * FROM tdbook');
foreach($books as $book) {
	$numch = db_query("SELECT numch FROM tdverse WHERE reftrans = '".$book['reftrans']."' AND abbook = '".$book['abbrev']."' ORDER BY numch DESC LIMIT 1;");
	if(is_array($numch)) {
		$query = "UPDATE tdbook SET countch = ".$numch[0]['numch']." WHERE reftrans = '".$book['reftrans']."' AND abbrev = '".$book['abbrev']."' LIMIT 1";
		db_query($query);
	}
}
$chapters = db_query('SELECT * FROM tdbook');
foreach($books as $book) {
	$numch = db_query("SELECT numch FROM tdverse WHERE reftrans = '".$book['reftrans']."' AND abbook = '".$book['abbrev']."' ORDER BY numch DESC LIMIT 1;");
	if(is_array($numch)) {
		$query = "UPDATE tdbook SET countch = ".$numch[0]['numch']." WHERE reftrans = '".$book['reftrans']."' AND abbrev = '".$book['abbrev']."' LIMIT 1";
		db_query($query);
		
		for($i=1;$i<=$numch[0]['numch'];$i++) {
			$query = "SELECT numv FROM tdverse WHERE reftrans = '".$book['reftrans']."' AND abbook = '".$book['abbrev']."' AND numch = ".$i." ORDER BY ABS(numv) DESC LIMIT 1";
			//echo $query."<br>\n";
			$numv = db_query($query);
			if(is_array($numv)) {
			/*TAKARÍTANI KELLENE ELŐBB!!*/
				$query= "INSERT INTO tdchapter (reftrans, abbook, numch, lastv) VALUES (".$book['reftrans'].",'".$book['abbrev']."',".$i.",".$numv[0]['numv'].");";
				db_query($query);

			}
		}	
	}
}

?>