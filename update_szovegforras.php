<?php
header('Content-type: text/html; charset=utf-8'); 

//echo phpinfo();

include_once 'quote.php';
include_once 'include/Dropbox-master/examples/bootstrap.php';
//include_once 'include/Dropbox-master/examples/bootstrap.php';
echo 'laci';
exit;
if(isset($_REQUEST['trans'])) $trans = $_REQUEST['trans'];
else $trans = "KG";

/*
 * Dropbox
 */
 
// Set the file path
// You will need to modify $path or run putFile.php first
$path = 'Biblia_sajat_cucc/'.$trans.'/'.$trans.'_szovegforras.xls';
$shares = $dropbox->shares($path, false);

setvar('update_KG',time());
// Set the output file
// If $outFile is set, the downloaded file will be written
// directly to disk rather than storing file data in memory
$outFile = false;
try {
	$tmp = "tmp_".$trans.".xls";
    // Download the file
    $file = $dropbox->getFile($path, $outFile);
	
	if(getvar('update_'.$trans) < strtotime($file['meta']->modified)) {
		setvar('update_'.$trans,time());
		echo "Nincs szükség frissítésre.";
		exit;
	} 
	setvar('update_'.$trans,time());
	if(file_exists($tmp)) unlink($tmp);
	file_put_contents($tmp,$file['data']);
} catch (\Dropbox\Exception\NotFoundException $e) {
    echo 'The file was not found at the specified path/revision';
	exit;
}
/**/
$books = array();
$result = db_query("SELECT tdbook.abbrev, tdbook.bookorder, reftrans FROM tdbook, tdtrans WHERE reftrans = tdtrans.did AND tdtrans.abbrev = '".$trans."' ORDER BY bookorder");
foreach($result as $key => $res) {	$books[$res['bookorder']] = $res; }

/*
 * Excel
 */
include_once 'include/excel_reader2.php';

$data = new Spreadsheet_Excel_Reader("tmp_".$trans.".xls",false,'UTF-8//IGNORE');
$data->setUTFEncoder('iconv');

foreach($data->boundsheets as $key => $sheet) {
	if($sheet['name'] == $trans) {
		$sheetid = $key;
	}
} if(!isset($sheetid)) exit;

for($col = 1; $col <= $data->colcount($sheetid);$col++) {
	$cols[$data->val(1,$col,$sheetid)] = $col;
}

$max = 5; //$data->rowcount($sheetid);
for($row = 3; $row <= $max; $row++) {
	set_time_limit(60);
	$DCC_hiv = strtolower($data->val($row,$cols['DCC_hiv'],$sheetid));
	
	$jel = $data->val($row,$cols['jel'],$sheetid);
	$jelstatusz = $data->val($row,$cols['jelstatusz'],$sheetid);
	
	$update[$DCC_hiv]['w']['gepi'] = $DCC_hiv;	
	$update[$DCC_hiv]['w']['reftrans'] = $books[(int)($DCC_hiv[0].$DCC_hiv[1].$DCC_hiv[2])]['reftrans'];
	
	$update[$DCC_hiv]['s']['refbook'] = $books[(int)($DCC_hiv[0].$DCC_hiv[1].$DCC_hiv[2])]['bookorder'];
	$update[$DCC_hiv]['s']['refchapter'] = (int)($DCC_hiv[3].$DCC_hiv[4].$DCC_hiv[5]);
	
	$update[$DCC_hiv]['s']['abbook'] = $books[(int)($DCC_hiv[0].$DCC_hiv[1].$DCC_hiv[2])]['abbrev'];
	$update[$DCC_hiv]['s']['numch'] = (int)($DCC_hiv[3].$DCC_hiv[4].$DCC_hiv[5]);
	$update[$DCC_hiv]['s']['numv'] = (int)($DCC_hiv[6].$DCC_hiv[7].$DCC_hiv[8]);
		
	preg_match('/[{]{1}(.*?)[}]{1}/',$jel,$match);
	if(count($match)>1) $update[$DCC_hiv]['s']['refs'] = $match[1];
		
	if(!@iconv("UTF-8", "UTF-8", $jel))  {
		$jel = iconv('windows-1250', 'UTF-8',$jel); 
	}
	
	if($jelstatusz==6) $update[$DCC_hiv]['s']['verse'] = $jel;
	elseif($jelstatusz < 4) {
		if(isset($update[$DCC_hiv]['s']['title'])) $update[$DCC_hiv]['s']['title'] .= "<br>".$jel;
		else $update[$DCC_hiv]['s']['title'] = $jel;
	}
}
echo"<pre>".print_R($update,1);

exit;
 
 /*
  * mySQL
  */
 $tmpbook = '';
 foreach ($update as $up) {
	set_time_limit(60);
	if($up['s']['abbook'] != $tmpbook) {
		$query = "DELETE FROM tdverse WHERE gepi LIKE '".(int) ($up['w']['gepi']{0}.$up['w']['gepi']{1}.$up['w']['gepi']{2}) ."%' AND reftrans = ".$up['w']['reftrans']." ";
		echo $query."<br>";
		$tmpbook = $up['s']['abbook'];
	}
	$set = array(); $where = array(); $insert = array();
	foreach($up['s'] as $n=>$v) { $set[] = $n.' = "'.$v.'"'; $insert['name'][] = $n; $insert['value'][] = $v; }
	foreach($up['w'] as $n=>$v) { $where[] = $n.' = "'.$v.'"'; $insert['name'][] = $n; $insert['value'][] = $v; }
	/*
	$query = "SELECT * FROM tdverse WHERE  ".implode(' AND ',$where)."  LIMIT 1";
	echo $query."<br>\n";
	$result = db_query($query);
	if(is_Array($result)) {
		$query = "UPDATE tdverse SET ".implode(', ',$set)." WHERE ".implode(' AND ',$where)." LIMIT 1";
		db_query($query);
		$content .= $query."<br>\n";
	} else {
		$query = "INSERT INTO tdverse (".implode(',',$insert['name']).") VALUES ('".implode("','",$insert['value'])."');";
		db_query($query);
		$content .= $query."<br>\n";
	}*/
	$query = "INSERT INTO tdverse (".implode(',',$insert['name']).") VALUES ('".implode("','",$insert['value'])."');";
	db_query($query);
	$content .= $query;
	
		echo '<span style="white-space: nowrap;">'.htmlentities($content).'</span><br>';
		$content = '';
//	exit;
}
 
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

/* fejezetek ÉS VERSEK mennyisége *
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
			//TAKARÍTANI KELLENE ELŐBB!!
				$query= "INSERT INTO tdchapter (reftrans, abbook, numch, lastv) VALUES (".$book['reftrans'].",'".$book['abbrev']."',".$i.",".$numv[0]['numv'].");";
				db_query($query);

			}
		}	
	}
}


// dicsőség
/* */

/* gépi kódok nagyon primitív előállítása *
$results = db_query("SELECT * FROM tdverse WHERE gepi IS NOT NULL ORDER BY gepi;");
foreach($results as $result) {
	set_time_limit(60);
	$query = "UPDATE tdverse SET gepi = ".$result['gepi']." WHERE abbook = '".$result['abbook']."' AND numch = ".$result['numch']." AND numv = ".$result['numv']." LIMIT 5 ";
	echo $query."<br>\n";
	db_query($query);
}
/**/
exit;
?>