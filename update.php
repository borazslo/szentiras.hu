<?php
include_once 'quote.php';

/* KNB csv bedolgozása *
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
		
		$update[$fields[1]]['s']['ref'] = $fields[1];
		
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

/* KG csv bedolgozása *
$result = db_query("SELECT abbrev, oldtest FROM tdbook WHERE reftrans = 4  AND oldtest = 1 ORDER BY oldtest DESC, bookorder");
foreach($result as $key => $res) $books[1][($key+1)] = $res['abbrev'];
$result = db_query("SELECT abbrev, oldtest FROM tdbook WHERE reftrans = 4  AND oldtest = 0 ORDER BY oldtest DESC, bookorder");
foreach($result as $key => $res) $books[2][($key+1)] = $res['abbrev'];


$file = file_get_contents("import/KG_szovegforras.csv");
$file = iconv('ISO-8859-2','UTF-8',$file);
$file = preg_replace("/(([\$]){1}\n|[\$]{1})/","",$file);
$sorok = explode("\n", $file);

unset($sorok[0]);
unset($sorok[1]);


foreach($sorok as $key=>$sor) {
	//if($key>100) break;
	set_time_limit(60);
	if($sor != '') {
		$fields = explode('	',$sor);
		//echo"<pre>".print_R($fields,1)."</pre>";
		$code = $fields[1];
		
		preg_match('/[{]{1}(.*?)[}]{1}/',$fields[6],$match);
		if(count($match)>1) $update[$fields[1]]['w']['refs'] = $match[1];
		
		$update[$fields[1]]['w']['reftrans'] = 4;
		$update[$fields[1]]['w']['gepi'] = $code;
		$update[$fields[1]]['w']['abbook'] = $books[$code[0]][(int)($code[1].$code[2])];
		$update[$fields[1]]['w']['numch'] = (int)($code[3].$code[4].$code[5]);
		$update[$fields[1]]['w']['numv'] = (int)($code[6].$code[7].$code[8]);
		
		//$update[$fields[1]]['s']['ref'] = $fields[1];
		
		if($fields[9]==6) $update[$fields[1]]['s']['verse'] = preg_replace('/(^"|"$)/','',$fields[10]);
		elseif($fields[9] < 4) {
			//$update[$fields[1]]['s']['title'] .= "<h".$fields[5].">".$fields[6]."</h".$fields[5].">";
			if(isset($update[$fields[1]]['s']['title'])) $update[$fields[1]]['s']['title'] .= "<br>".$fields[10];
			else $update[$fields[1]]['s']['title'] = $fields[10];
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
	$query = "SELECT chapter FROM tdverse WHERE trans = '".$book['trans']."' AND book = '".$book['id']."' ORDER BY chapter DESC LIMIT 1;";
	$numch = db_query($query);
	if(is_array($numch)) {
		$query = "UPDATE tdbook SET countch = ".$numch[0]['chapter']." WHERE trans = '".$book['trans']."' AND id = '".$book['id']."' LIMIT 1";
		//echo $query."<br>";
		db_query($query);
		
		for($i=1;$i<=$numch[0]['chapter'];$i++) {
			$query = "SELECT numv FROM tdverse WHERE trans = '".$book['trans']."' AND book = '".$book['id']."' AND chapter = ".$i." ORDER BY ABS(numv) DESC LIMIT 1";
			//echo $query."<br>\n";
			$numv = db_query($query);
			if(is_array($numv)) {
			//TAKARÍTANI KELLENE ELŐBB!!
				$query= "INSERT INTO tdchapter (trans, book, chapter, lastv) VALUES (".$book['trans'].",'".$book['id']."',".$i.",".$numv[0]['numv'].");";
				//echo $query."<br>";
				db_query($query);

			}
		}	
	}
}


// dicsőség
/* */

/* gépi kódok nagyon primitív előállítása *
$results = db_query("SELECT * FROM tdverse WHERE gepi IS NULL ORDER BY gepi;");
foreach($results as $result) {
	set_time_limit(60);
	 $gepi = $result['book'].sprintf("%03d", $result['chapter']).sprintf("%03d", $result['numv']).'00';
	$query = "UPDATE tdverse SET gepi = ".$gepi." WHERE did = ".$result['did']." LIMIT 1 ";
	echo $query."<br>\n";
	db_query($query);
}
/**/



?>