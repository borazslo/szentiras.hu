<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UpdateTextsCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'szentiras:updateTexts';

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
   
    if(!$this->option('nohunspell')) {
        //test hunspell
        $returnVal = shell_exec("which hunspell");
        if(empty($returnVal)) {
            App::abort(500,'Hunspell-hu is not installed. Please install it or use \'--nohunspell\' instead.');
        }
        //test hunspell dictionary??
        $returnVal = shell_exec("echo medve | hunspell -d hu_HU -s -i UTF-8  2>&1");
        if(preg_match('/Can\'t open affix or dictionary files for dictionary/i',$returnVal)) {
           App::abort(500,'Can\'t open the hu_HU dictionary. Try to install hunspell-hu or use \'--nohunspell\' instead.');
        }
     }
     
        $abbrev = $this->argument('abbrev');
        if(!preg_match("/^(".Config::get('settings.translationAbbrevRegex').")$/",$abbrev))  App::abort(500,'Hibás fordítás rövidítés!');

        $translationRepository = \App::make('SzentirasHu\Models\Repositories\TranslationRepository');        
        $translation = $translationRepository->getByAbbrev($abbrev);    
     
        if($this->option('file') AND $this->option('file') != '{abbrev}.xls') $file = $this->option('file');
        else $file = $abbrev.".xls";       
     
        $bookRepository = \App::make('SzentirasHu\Models\Repositories\BookRepository');        
        $books = $bookRepository->getBooksByTranslation($translation->id);
        foreach($books as $book) {  
            $books_number2id[$book->number] = $book->id;
            $books_abbrev2id[$book->abbrev] = $book->id;           
        }
        
        $dir = Config::get('settings.sourceDirectory');
       
        $path = "{$dir}/{$file}";
        $this->info("A ".$path." betöltése...");
        
        /* Betöltjük a "Könyvek" lapot */
        try {
            $content = File::get($path);
            $filetype = PHPExcel_IOFactory::identify($path);
            $objReader = PHPExcel_IOFactory::createReader($filetype);
            $objReader->setReadDataOnly(true);        
            $objReader->setLoadSheetsOnly('Könyvek');             
            $objPHPExcel = $objReader->load($path);
            $objWorksheet = $objPHPExcel->getActiveSheet();
        } catch (Exception $e) {
            $this->error('nincs');
            App::abort(500,'Nem sikerült a fájlt betölteni!');
        }

        $this->info("A 'Könyvek' beolvasása...");
        $konyvoszlopok = array(
            'SZIT'=>array(0,5),
            'KNB'=>array(0,4),
            'UF'=>array(0,4),
            'KG'=>array(0,4)
        );
        if(!isset($konyvoszlopok[$abbrev])) {
            App::abort(500,'Ennél a szövegforrásnál ('.$abbrev.') nem tudjuk, hogy hol vannak a könyvek rövidítéseit feloldó oszlopok.');
        }
        
        $max = $objWorksheet->getHighestRow();
        $insert = array();
        
        for($i = 2;$i<=$max;$i++) {
            $row = $i;
            $gepi = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($konyvoszlopok[$abbrev][0], $i)->getValue();
            $rov = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($konyvoszlopok[$abbrev][1], $i)->getValue();
            if(!isset($books_abbrev2id[$rov]) AND  $rov != '-') $hibasrov[] = $rov;
            elseif($rov != '-') $books_gepi2id[$gepi] = $books_abbrev2id[$rov];                                
        }
        
        if(isset($hibasrov)) {
            App::abort(500,"A következő rövidítések csak a szövegforrásban találhatóak meg, az adatbázisban nem!\n".implode(', ',$hibasrov).print_r($books_abbrev2id,1));
        }
        
        /* Betöltjük a "$abbrev" lapot */
        $this->info("A(z) '".$abbrev."' lap betöltése...");
        try {
            $content = File::get($path);
            $filetype = PHPExcel_IOFactory::identify($path);
            $objReader = PHPExcel_IOFactory::createReader($filetype);
            $objReader->setReadDataOnly(true);
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
        $this->info("Beolvasás sorról sorra...");
        
        $max = $objWorksheet->getHighestRow();
        $insert = array();
        echo "\n";
        for($i = 3;$i<$max;$i++) {
            $row = $i;
            
            $gepi = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cols[$fields['gepi']], $i)->getValue();            
            if(!$this->option('filter') OR preg_match('/'.$this->option('filter').'/i',$gepi)) { 
                $values['trans'] = $translation->id;
                $values['gepi'] = $gepi;
                $values['book_number'] = (int) substr($gepi,0,3);
                $values['chapter'] = (int) substr($gepi,3,3);
                $values['numv'] = (int) substr($gepi,6,3); 
                $values['tip'] = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cols['jelstatusz'], $i)->getValue();
                $values['verse'] = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cols['jel'], $i)->getCalculatedValue();
                $values['verseroot'] = '??';
                $values['ido'] = gmdate ( 'Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP( $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($cols['ido'], $i)->getValue()));
                if(isset($books_gepi2id[(int) substr($gepi,0,3)])) $values['book_id'] = $books_gepi2id[(int) substr($gepi,0,3)]; 
                else {
                        $this->error("A ".(int) substr($gepi,0,3)."-hez nincs `book_id`");
                        App::abort(500,'Valami gond van a books id/gepi párossal!');
                   }
                        
                //if((isset($_REQUEST['gepi']) AND preg_match('/'.$_REQUEST['gepi'].'/i',$gepi)) OR !isset($_REQUEST['gepi'])) {}                     
                if(!$this->option('verbose')) echo "\e[1A"; 
                echo $abbrev." ".$values['gepi'];
                if($this->option('verbose')) echo ": ".substr($values['verse'],0,140);
                echo "\n"; 
                $inserts[$i] = $values;
            }
        }
        if(!$this->option('verbose')) echo "\e[1A";
    
        if(!$this->option('nohunspell')) {
            $this->info("Egyszerű szótövekből álló szöveg elkészítése...");
            echo "\n";
            foreach($inserts as $key => $item) {                
                $output = shell_exec('echo "'.$item['verse'].'" | hunspell -d hu_HU -s -i UTF-8');
                $lines = explode("\n",$output);     
                /* TODO: finomítandó, mert még mindig a legrosszabbat eszi meg (második ajánlat) */
                $return = "";
                foreach($lines as $line) {
                    if($line == '' and isset($szo)) {
                        $tmp = explode(' ',$szo);
                        if(isset($tmp[1])) $return .= ' '.$tmp[1];
                        else $return .= ' '.$tmp[0];          
                    }
                    $szo = $line;     
                }
                $return = strtolower(strip_tags($return));
                $inserts[$key]['verseroot'] = trim($return);
                if(!$this->option('filter')) echo "\e[1A"; 
                echo $item['gepi']." ".str_pad(substr(trim($return),0,140),140)."\n";
            }
            if(!$this->option('verbose')) echo "\e[1A";
      }
    
         $this->info("Mysql adatbázis lementése...");
         //TODO: larevelesíteni (http://bundles.laravel.com/bundle/mysqldump-php ?)
         $connections = Config::get('database.connections');
         $conn = $connections[Config::get('database.default')];
         exec('mysqldump -u '.$conn['username'].' --password='.$conn['password'].' '.$conn['database'].' '.$conn['prefix'].'tdverse > '.Config::get('settings.sourceDirectory').'/'.$conn['database'].'_'.$conn['prefix'].'tdverse_'.$abbrev.'_'.date('YmdHis').'.sql');

         $this->info("Mysql tábla ürítése..."); 
        if(!$this->option('filter')) {
            DB::table('tdverse')->where('trans', '=', $translation->id)->delete();
        } else {
            DB::table('tdverse')->where('trans', '=', $translation->id)->where('gepi', 'REGEXP', $this->option('filter'))->delete();
        }
         
         $this->info("Mysql tábla feltöltése...");         
         DB::table('tdverse')->insert($inserts);

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
            array('file', null, InputOption::VALUE_OPTIONAL, 'File to use for the import', '{abbrev}.xls'),
            array('nohunspell', null, InputOption::VALUE_NONE, 'Generate versesimple with hunspell'),
            array('filter', null, InputOption::VALUE_OPTIONAL, 'Filter the import by `gepi`', null),
        );
    }

}
