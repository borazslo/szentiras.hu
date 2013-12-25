<?php
header('Content-type: text/html; charset=utf-8'); 

$starttime = time();

ini_set('memory_limit', '512M');
//echo phpinfo(); exit;
$fpath = '/var/www/szentiras.hu/';

require_once('bibleconf.php');
require_once("biblefunc.php");
require_once('func.php');
require_once('quote.php');

if (PHP_SAPI === 'cli') {$cli = true;} else $cli = false;

if(!$cli) include_once 'include/Dropbox-master/examples/bootstrap.php';
include_once 'include/excel_reader2.php';

$transs = array();
$result = db_query("SELECT abbrev, id FROM ".DBPREF."tdtrans ORDER BY id");
foreach($result as $key => $res) {	$transs[$res['id']] = $res['abbrev']; $transsk[$res['abbrev']] = $res['id'];}

if($cli == TRUE) {
	$_REQUEST['trans'] = $argv[1];
	$_REQUEST['gepi'] = $argv[2];
}
if( !isset($_REQUEST['trans']) OR !in_array($_REQUEST['trans'],$transs)) {
	foreach($transs as $t) {
	 echo "<a href='".$baseurl."index.php?q=update_szovegforras&trans=".$t."'>".$t."</a><br>\n";
	}
	exit;
} 
else $trans = $_REQUEST['trans'];

setvar('update_'.$trans.'_hossz','start'.(int) ((time()-$starttime)/60));
$tmp = "tmp/tmp_".$trans."_".date('YmdHis',$starttime).".xls";
if($cli) $tmp = $fpath.$tmp;

if ($cli != TRUE)  {
/* Dropbox  */
$path = DROPBOXF.'/'.$trans.'/'.$trans.'_szovegforras.xls';
$shares = $dropbox->shares($path, false);

// Set the output file
// If $outFile is set, the downloaded file will be written
// directly to disk rather than storing file data in memory
$outFile = false;
try {
    // Download the file
    $file = $dropbox->getFile($path, $outFile);
	
	if(getvar('update_'.$trans) < strtotime($file['meta']->modified) AND (!isset($_REQUEST['forced']) OR $_REQUEST['forced'] != true)) {
		setvar('update_'.$trans,time());
		echo "Nincs szükség frissítésre, mert a ".$path." nem rég lett frissítve. ".date('Y.m.d. H:i:s',strtotime($file['meta']->modified))." vs ".date('Y.m.d. H:i:s',getvar('update_'.$trans))."<br/>";
		//exit;
	} 
	if(file_exists($tmp)) unlink($tmp);
	file_put_contents($tmp,$file['data']);
} catch (\Dropbox\Exception\NotFoundException $e) {
    echo 'The file was not found at the specified path/revision: '.$path;
	exit;
}
/**/
echo 'file letöltve';
exit;
}

/* find the most recent file */
$path = $fpath."tmp";
$latest_ctime = 0;
$latest_filename = '';    
$d = dir($path);
while (false !== ($entry = $d->read())) {
  $filepath = "{$path}/{$entry}";
  // could do also other checks than just checking whether the entry is a file
  if ((is_file($filepath) && filectime($filepath) > $latest_ctime) AND preg_match('/tmp_'.$trans.'_([0-9]{14})\.xls/',$filepath)) {
    $latest_ctime = filectime($filepath);
    $latest_filename = $entry;
  }
}
if(!isset($latest_filename)) die ("Nincs forrás file!\n");
$tmp = $path."/".$latest_filename;

if($cli) echo "Használt file: ".$tmp."\n";

$books = array();
$result = db_query("SELECT ".DBPREF."tdbook.abbrev, ".DBPREF."tdbook.id, trans FROM ".DBPREF."tdbook, ".DBPREF."tdtrans WHERE trans = ".DBPREF."tdtrans.id AND ".DBPREF."tdtrans.abbrev = '".$trans."' ORDER BY ".DBPREF."tdbook.id");
foreach($result as $key => $res) {	$books[$res['id']] = $res; }
/*
 * Excel
 */
echo "File betöltése...\n";
 /** Include PHPExcel_IOFactory */
require_once dirname(__FILE__) . '/include/PHPExcel-develop/Classes/PHPExcel/IOFactory.php';
$inputFileType = 'Excel5';
$inputFileName = $tmp;

/**  Create a new Reader of the type defined in $inputFileType  **/
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
/**  Advise the Reader that we only want to load cell data  **/
//$objReader->setReadDataOnly(true);
/**  Advise the Reader of which WorkSheets we want to load  **/ 
$objReader->setLoadSheetsOnly($trans); 
/**  Load $inputFileName to a PHPExcel Object  **/
$objPHPExcel = $objReader->load($inputFileName);

//echo $objPHPExcel->getSheetCount(),' worksheet',(($objPHPExcel->getSheetCount() == 1) ? '' : 's'),' loaded<br /><br />';
//$loadedSheetNames = $objPHPExcel->getSheetNames();
//foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) { echo $sheetIndex,' -> ',$loadedSheetName,'<br />';}

$objWorksheet = $objPHPExcel->getActiveSheet();

/* fejlécek megszerzése */
foreach ($objWorksheet->getRowIterator() as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(FALSE); 
    foreach ($cellIterator as $key => $cell) {
                 $cols[$cell->getValue()] = $key;
    }
	break;
}
/* oszlopok ellenőrzése */
//$fields = array('did'=>'*Ssz','gepi'=>'DCB_hiv','hiv'=>'szephiv','old'=>'DCB_old','jelenseg'=>'jeltip','tip'=>'jelstatusz','verse'=>'jel','ido'=>'ido');
$fields = array('did'=>'*Ssz','gepi'=>'DCB_hiv','hiv'=>'szephiv','old'=>'DCB_old','tip'=>'jelstatusz','verse'=>'jel','ido'=>'ido');
    unset($errors); foreach($fields as $field) if(!isset($cols[$field])) $errors[] = $field;
    if(isset($errors)) {
        foreach($cols as $col => $val) {
            if(preg_match('/[A-Z]{3}_hiv/',$col)) $fields['gepi'] = $col;
            if(preg_match('/[A-Z]{3}_old/',$col)) $fields['old'] = $col;            
        }
        if(!isset($cols['ido'])) unset($fields['ido']);
    }
    unset($errors); foreach($fields as $field) if(!isset($cols[$field])) $errors[] = $field;
    if(isset($errors)) {
        echo 'A következő oszlopok hiányoznak az excel táblából: '.implode(', ',$errors)."<br/>\n";
        echo "Létező oszlopok: ".print_r($cols,1);
        //echo "Használt oszlopok: ".print_r($fields,1);        
        exit;    }
echo "Oszlopok feldologzva.\nKezdődjenek a sorok.\n";
//print_r($cols);print_r($fields);exit;
/**/		
$max = 21; 		
$max = $objWorksheet->getHighestRow();
$insert = array();
for($i = 2;$i<$max;$i++) {
	$row = $i;
	/*
	*  A számítgatott value miatt elszáll a jelensétípus megnevézse és a szép hivatkozás!
	*
	*
	*/
	
	$gepi = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cols[$fields['gepi']], $i)->getCalculatedValue();
//	$jel = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cols['jel'], $i)->getCalculatedValue();

	if((isset($_REQUEST['gepi']) AND preg_match('/'.$_REQUEST['gepi'].'/i',$gepi)) OR !isset($_REQUEST['gepi'])) {
	set_time_limit(60);
    foreach($fields as $mysql => $excel) {		
		$value = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cols[$excel], $i)->getValue(); //CalculatedValue(); //getValue();
        if($excel == 'jel') { 
			//echo "VALUE: ".$value."\n";
            //if(!@iconv("UTF-8", "UTF-8", $value)) $value = iconv('windows-1250', 'UTF-8',$value); 
            $insert[$row]['versesimple'] = simpleverse($value);
            $insert[$row]['verseroot'] = rootverse($value);
        } elseif ($excel == 'jeltip') {
			$value = 'N/A';
            //if(!@iconv("UTF-8", "UTF-8", $value)) $value = iconv('windows-1250', 'UTF-8',$value); 
        } elseif ($excel == 'szephiv') {
			$value = 'N/A';
            //if(!@iconv("UTF-8", "UTF-8", $value)) $value = iconv('windows-1250', 'UTF-8',$value); 
        } elseif ($excel == 'ido') {
            $value = date('Y-m-d H:i:s',strtotime($value));
        } elseif ($mysql == 'gepi') {        
            $insert[$row]['book'] = (int) substr($value,0,3);
            $insert[$row]['chapter'] = (int) substr($value,3,3);
            $insert[$row]['numv'] = (int) substr($value,6,3);        
        }        
        $insert[$row][$mysql] = $value;
        $insert[$row]['trans'] = $transsk[$trans];
	}    
	
	#preg_match('/[{]{1}(.*?)[}]{1}/',$jel,$match);
	#if(count($match)>1) $update[$DCC_hiv]['s']['refs'] = $match[1];	
    if($cli) echo "excel ".(time() - $starttime)." ".$trans." ".$insert[$row]['gepi'].": ".substr($insert[$row]['verse'],0,120)."\n";
	} else echo "\rexcel ".(time() - $starttime)." ".$trans." ".$gepi."";
    setvar('update_'.$trans.'_hossz','excel_'.$row.'_'.(int) ((time()-$starttime)/ 60));
}


 /*
  * mySQL
  */
  echo "Mysql adatbázis lementése...\n";
  exec('mysqldump -u szentiras --password=saritnezs11 bible '.DBPREF.'tdverse > tmp/bible_'.DBPREF.'tdverse_'.$trans.'_'.date('YmdHis').'.sql');
  
  setvar('update_'.$trans.'_hossz','mysql_'.(int) ((time()-$starttime)/60));
  setvar('frissitunk_'.$trans,'true');
  echo "Mysql tábla ürítése: ";
 $query = "DELETE FROM ".DBPREF."tdverse WHERE  trans = ".$transsk[$trans];
 if(isset($_REQUEST['gepi'])) $query .= " AND gepi REGEXP '".$_REQUEST['gepi']."'";
 $query .= "\n";
 db_query($query);
 if($cli) echo $query;
 $content .= "<pre>". $query."<br>"; 
 echo "Mysql INSERT sorok elküldése...\n";
 foreach ($insert as $ins) {
	set_time_limit(60);
    $fields = array(); $values = array();
	foreach($ins as $k=>$v) {
        $fields[] = $k;
        $values[] = $v;
    }
    $query = "INSERT INTO ".DBPREF."tdverse (".implode(',',$fields).") VALUES ('".implode("','",$values)."');";
	db_query($query);
	$content .= $query."<br>";
    if($cli) echo "mysql ".(time() - $starttime)." ".$trans." ".$ins['gepi'].": ".substr($query,99,130)."\n";
	}
$content .= '</pre>';
setvar('update_'.$trans,time());
setvar('update_'.$trans.'_hossz',(int) ((time()-$starttime)/60));
setvar('frissitunk_'.$trans,'false');

?>