<?php
namespace SzentirasHu\Controllers;

use App;
use Config;
use Exception;
use Log;
use File;
use Response;
use View;
use PHPExcel_IOFactory;

class UpdateController extends \BaseController
{


    public function run($abbrev)
    {
        Log::debug("show", [$abbrev]);
		
        //$dir = Config::get('settings.audioDirectory');
		$dir = "../tmp";
		$file = $abbrev.".xls";
		$path = "{$dir}/{$file}";		
		try {
			$content = File::get($path);
			$filetype = PHPExcel_IOFactory::identify($path);
			$objReader = PHPExcel_IOFactory::createReader($filetype);
			$objReader->setReadDataOnly(true);  // set this if you don't need to write
			$objReader->setLoadSheetsOnly($abbrev); 			
			$objPHPExcel = $objReader->load($path);
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
			
			/* oszlopok ellenőrzése *
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
				exit;   
			}
			echo "Oszlopok feldologzva.\nKezdődjenek a sorok.\n";
			/**
			
			$max = 21; 
			//$max = $objWorksheet->getHighestRow();
			$insert = array();
			for($i = 2;$i<$max;$i++) {
				$row = $i;
				/*
				*  A számítgatott value miatt elszáll a jelensétípus megnevézse és a szép hivatkozás!
				*
				*
				*
	
				$gepi = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cols[$fields['gepi']], $i)->getCalculatedValue();
			//	$jel = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cols['jel'], $i)->getCalculatedValue();
			
				if((isset($_REQUEST['gepi']) AND preg_match('/'.$_REQUEST['gepi'].'/i',$gepi)) OR !isset($_REQUEST['gepi'])) {
				set_time_limit(60);
				foreach($fields as $mysql => $excel) {		
					$value = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cols[$excel], $i)->getValue(); //CalculatedValue(); //getValue();
					if($mysql == 'did') $value = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cols[$excel], $i)->getCalculatedValue(); //getValue();
		
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
			/**		
			* mySQL
			*
			echo "Mysql adatbázis lementése...\n";
			exec('mysqldump -u '.getenv('MYSQL_SZENTIRAS_USER').' --password='.getenv('MYSQL_SZENTIRAS_PWD').' bible '.DBPREF.'tdverse > tmp/bible_'.DBPREF.'tdverse_'.$trans.'_'.date('YmdHis').'.sql');
  
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
			exit;
			/**/
			return View::make('home');
            
			} catch (Exception $e) {
                Log::debug('exception', [$e]);
                App::abort(404, '404 :(');
            }
		
		
    }

}