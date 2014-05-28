<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UpdateText extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'UpdateText';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Update texts from external source (xls)';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$starttime = time();
		$abbrev = $this->argument('abbrev');
		//TODO: check if $abbrev is abbrev
		
		$dir = Config::get('settings.sourceDirectory');
		$file = $abbrev.".xls";
		$path = "{$dir}/{$file}";
		$this->info("A ".$path." betöltése...");
		try {
			$content = File::get($path);
			$filetype = PHPExcel_IOFactory::identify($path);
			$objReader = PHPExcel_IOFactory::createReader($filetype);
			$objReader->setReadDataOnly(true);  // set this if you don't need to write
			$objReader->setLoadSheetsOnly($abbrev); 			
			$objPHPExcel = $objReader->load($path);
			$objWorksheet = $objPHPExcel->getActiveSheet();
		} catch (Exception $e) {
			$this->error('nincs');
            App::abort(500,'Nem sikerült a fájlt betölteni!');
        }
				
		/* fejlécek megszerzése */
		$this->info("A fejlécek megszerzése...");
		foreach ($objWorksheet->getRowIterator() as $row) {
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(FALSE); 
			foreach ($cellIterator as $key => $cell) {
				$cols[$cell->getValue()] = $key;
			}
			break;
		}
		
		/* oszlopok ellenőrzése */
		$fields = array('did'=>'*Ssz','gepi'=>'DCB_hiv','hiv'=>'szephiv','old'=>'DCB_old','tip'=>'jelstatusz','verse'=>'jel','ido'=>'ido');
		$errors = array(); 
		foreach($fields as $field) if(!isset($cols[$field])) $errors[] = $field;
		if(isset($errors) AND $errors != array()) {
			foreach($cols as $col => $val) {
				if(preg_match('/[A-Z]{3}_hiv/',$col)) $fields['gepi'] = $col;
				if(preg_match('/[A-Z]{3}_old/',$col)) $fields['old'] = $col;            
			}
			if(!isset($cols['ido'])) unset($fields['ido']);
		}
		$errors = array();  foreach($fields as $field) if(!isset($cols[$field])) $errors[] = $field;
		if($errors != array()) {
			$this->error('A következő oszlopok hiányoznak az excel táblából: '.implode(', ',$errors));
			$this->comment("Létező oszlopok: ".implode(', ',array_keys($cols)));
			App::abort(500,"Probléma az oszlopoknál!");
		}
		
		/* sorról sorra */
		$this->info("Indulunk sorról sorra...");
		
		$max = 10; 
		//$max = $objWorksheet->getHighestRow();
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
				if($mysql == 'did') $value = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cols[$excel], $i)->getCalculatedValue(); //getValue();
	
				if($excel == 'jel') { 
					//echo "VALUE: ".$value."\n";
					//if(!@iconv("UTF-8", "UTF-8", $value)) $value = iconv('windows-1250', 'UTF-8',$value); 
					$insert[$row]['versesimple'] = ''; //simpleverse($value);
					$insert[$row]['verseroot'] = ''; //rootverse($value);
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
				//TODO: automatikusan kinyerno $abbrev-ből a $translationt!
				$transsk = array('SZIT'=>1,'KNB'=>3,'UF'=>2,'KG'=>4);
				$insert[$row]['trans'] = $transsk[$abbrev];
			}    
			
			#preg_match('/[{]{1}(.*?)[}]{1}/',$jel,$match);
			#if(count($match)>1) $update[$DCC_hiv]['s']['refs'] = $match[1];	
			echo "excel ".(time() - $starttime)." ".$abbrev." ".$insert[$row]['gepi'].": ".substr($insert[$row]['verse'],0,120)."\n";
		}
	
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
}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('abbrev', InputArgument::REQUIRED, 'Abbreavtion of the translation'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			//array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
