<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use SzentirasHu\Models\Repositories\BookRepository;
use SzentirasHu\Models\Repositories\TranslationRepository;

class UpdateTextsCommand extends Command
{

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
     * @var SzentirasHu\Models\Repositories\TranslationRepository
     */
    private $translationRepository;
    /**
     * @var SzentirasHu\Models\Repositories\BookRepository
     */
    private $bookRepository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TranslationRepository $translationRepository, BookRepository $bookRepository)
    {
        parent::__construct();
        $this->translationRepository = $translationRepository;
        $this->bookRepository = $bookRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        if (!$this->option('nohunspell')) {
            $this->testHunspell();
        }

        $abbrev = $this->argument('abbrev');
        $this->verifyAbbrev($abbrev);

        $translation = $this->translationRepository->getByAbbrev($abbrev);
        $fileName = $this->getFileName($abbrev);
        $books_abbrev2id = $this->getAbbrev2Id($translation);
        $sourceDir = Config::get('settings.sourceDirectory');
        $filePath = "{$sourceDir}/{$fileName}";
        $bookWorksheet = $this->getWorksheet($filePath, 'Könyvek');

        $columns = [
            'SZIT' => ['gepi'=>0, 'rov'=>5],
            'KNB' => ['gepi'=>0, 'rov'=>4],
            'UF' => ['gepi'=>0, 'rov'=>4],
            'KG' => ['gepi'=>0, 'rov'=>4],
            'BD' => ['gepi'=>0, 'rov'=>1],
        ];
        $this->verifyBookColumns($columns, $abbrev);

        $maxRowNumber = $bookWorksheet->getHighestRow();

        for ($rowNumber = 2; $rowNumber <= $maxRowNumber; $rowNumber++) {
            $gepi = $bookWorksheet->getCellByColumnAndRow($columns[$abbrev]['gepi'], $rowNumber)->getValue();
            $rov = $bookWorksheet->getCellByColumnAndRow($columns[$abbrev]['rov'], $rowNumber)->getValue();
            if (!isset($books_abbrev2id[$rov]) AND $rov != '-') {
                $badAbbrevs[] = $rov;
            } else if ($rov != '-') {
                $books_gepi2id[$gepi] = $books_abbrev2id[$rov];
            }
        }
        if(isset($badAbbrevs)) $this->verifyBadAbbrev($badAbbrevs, $books_abbrev2id);

        $abbrevWorksheet = $this->getWorksheet($filePath, $abbrev);
        $headers = $this->getHeaders($abbrevWorksheet);

        $fields = ['did' => '*Ssz', 'gepi' => 'DCB_hiv','tip' => 'jelstatusz', 'verse' => 'jel', 'ido' => 'ido'];
        $this->verifyColumns($fields, $headers);

        $inserts = $this->readLines($abbrevWorksheet, $headers, $fields, $translation, $books_gepi2id, $abbrev);

        if (!$this->option('nohunspell')) {
            $inserts = $this->processHunspell($inserts);
        }

        if (count($inserts) > 0) {
            $this->saveToDb($abbrev, $translation, $inserts);
        } else {
            $this->info("Nincs mit feltölteni.");
        }
        $this->info("Ne feledd az újra indexelést az environmentnek megfelelően. Pl.: deploy/staging/sphinx/reindex.sh");
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['abbrev', InputArgument::REQUIRED, 'Abbreviation of the translation'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['file', null, InputOption::VALUE_OPTIONAL, 'File to use for the import', '{abbrev}.xls'],
            ['nohunspell', null, InputOption::VALUE_NONE, 'Generate versesimple with hunspell'],
            ['filter', null, InputOption::VALUE_OPTIONAL, 'Filter the import by `gepi`', null],
        ];
    }

    private function testHunspell()
    {
        $returnVal = shell_exec("which hunspell");
        if (empty($returnVal)) {
            App::abort(500, 'Hunspell-hu is not installed. Please install it or use \'--nohunspell\' instead.');
        }
        //test hunspell dictionary??
        $returnVal = shell_exec("echo medve | hunspell -d hu_HU -s -i UTF-8  2>&1");
        if (preg_match('/Can\'t open affix or dictionary files for dictionary/i', $returnVal)) {
            App::abort(500, 'Can\'t open the hu_HU dictionary. Try to install hunspell-hu or use \'--nohunspell\' instead.');
        }
    }

    /**
     * @param $path
     * @return PHPExcel_Worksheet
     */
    private function getWorksheet($path, $sheet)
    {
        try {
            $this->info("A {$sheet} lap betöltése...");
            $filetype = PHPExcel_IOFactory::identify($path);
            $objReader = PHPExcel_IOFactory::createReader($filetype);
            $objReader->setReadDataOnly(true);
            $objReader->setLoadSheetsOnly($sheet);
            $objPHPExcel = $objReader->load($path);
            $objWorksheet = $objPHPExcel->getActiveSheet();
        } catch (Exception $e) {
            $this->error('nincs');
            App::abort(500, 'Nem sikerült a fájlt betölteni!');
        }
        return $objWorksheet;
    }

    /**
     * @param $inserts
     * @return mixed
     */
    private function processHunspell($inserts)
    {
        $this->info("Egyszerű szótövekből álló szöveg elkészítése...");
        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "r"]
        ];
        $process = proc_open('hunspell -d hu_HU -i UTF-8', $descriptorspec, $pipes, null, null);
        $this->info("Hunspell-hu indul...");
        if (!$this->option('verbose')) echo "\n";
        if (!$this->option('verbose')) fgets($pipes[1], 4096);
        else echo fgets($pipes[1], 4096);


        foreach ($inserts as $key => $item) {
            $item['verse'] = strtolower(strip_tags($item['verse']));
            $item['verse'] = preg_replace("/(,|:|\?|!|;|\.|„|”|»|«|\")/i", '', $item['verse']);
            $item['verse'] = preg_replace(['/Í/i', '/Ú/i', '/Ő/i', '/Ó/i', '/Ü/i'], ['í', 'ú', 'ő', 'ó', 'ü'], $item['verse']);

            $verseroot = '';
            $worlds = explode(' ', $item['verse']);
            $s = 0;
            $t = 0;
            foreach ($worlds as $k => $world) {
                if (Cache::has('hunspell_' . $world)) {
                    $verseroot .= " " . Cache::get('hunspell_' . $world);
                    $s++;
                } else {
                    fwrite($pipes[0], $world . "\n"); // send start
                    $return = fgets($pipes[1], 4096); //get answer
                    if (trim($return) != '') {
                        fgets($pipes[1], 4096);
                        if ($return{0} == "+") {
                            $t++;
                            $tocache = trim(substr($return, 2));
                        } else $tocache = $world;
                        $verseroot .= " " . $tocache;
                        Cache::put('hunspell_' . $world, $tocache, time() + 3600);
                    }
                }
            }
            $inserts[$key]['verseroot'] = trim($verseroot);
            if (!$this->option('verbose')) echo "\e[1A";
            echo sprintf('%02d', $s) . " " . sprintf('%02d', $t) . " " . $item['gepi'] . " " . $item['tip'];
            if ($this->option('verbose')) echo " " . str_pad(substr(trim($verseroot), 0, 140), 140);
            echo "\n";
        }
        echo "\e[1A";

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process); //stop test_gen.php
        $this->info("\nHunspell-hu vége.");
        return $inserts;
    }

    /**
     * @param $abbrev
     * @param $translation
     * @param $inserts
     */
    private function saveToDb($abbrev, $translation, $inserts)
    {
        $this->info("Mysql adatbázis lementése...");
        //TODO: larevelesíteni (http://bundles.laravel.com/bundle/mysqldump-php ?)
        $connections = Config::get('database.connections');
        $conn = $connections[Config::get('database.default')];
        exec('mysqldump -u ' . $conn['username'] . ' --password=' . $conn['password'] . ' ' . $conn['database'] . ' ' . $conn['prefix'] . 'tdverse > ' . Config::get('settings.sourceDirectory') . '/' . $conn['database'] . '_' . $conn['prefix'] . 'tdverse_' . $abbrev . '_' . date('YmdHis') . '.sql');

        Artisan::call('down');
        $this->info("Mysql tábla ürítése...");
        if (!$this->option('filter')) {
            DB::table('tdverse')->where('trans', '=', $translation->id)->delete();
        } else {
            DB::table('tdverse')->where('trans', '=', $translation->id)->where('gepi', 'REGEXP', $this->option('filter'))->delete();
        }
        $this->info("Mysql tábla feltöltése " . count($inserts) . " sorral...");
        echo "\n";
        for ($rowNumber = 0; $rowNumber < count($inserts); $rowNumber += 100) {
            $tmp = array_slice($inserts, $rowNumber, 100);
            echo "\e[1A";
            DB::table('tdverse')->insert($tmp);
            $this->info($rowNumber + count($tmp) . " sor feltöltve.");
        }
        Artisan::call('up');
    }

    /**
     * @param $abbrevWorksheet
     * @param $cols
     * @param $fields
     * @param $translation
     * @param $books_gepi2id
     * @param $abbrev
     * @param $inserts
     * @return mixed
     */
    private function readLines($abbrevWorksheet, $cols, $fields, $translation, $books_gepi2id, $abbrev)
    {
        $this->info("Beolvasás sorról sorra...");
        echo "\n";
        $maxRowNumber = $abbrevWorksheet->getHighestRow();
        for ($rowNumber = 3; $rowNumber < $maxRowNumber; $rowNumber++) {
            $gepi = $abbrevWorksheet->getCellByColumnAndRow($cols[$fields['gepi']], $rowNumber)->getValue();
            if (!$this->option('filter') OR preg_match('/' . $this->option('filter') . '/i', $gepi)) {
                $values['trans'] = $translation->id;
                $values['gepi'] = $gepi;
                $values['book_number'] = (int)substr($gepi, 0, 3);
                $values['chapter'] = (int)substr($gepi, 3, 3);
                $values['numv'] = (int)substr($gepi, 6, 3);
                $values['tip'] = $abbrevWorksheet->getCellByColumnAndRow($cols['jelstatusz'], $rowNumber)->getValue();
                $values['verse'] = $abbrevWorksheet->getCellByColumnAndRow($cols['jel'], $rowNumber)->getCalculatedValue();
                $values['verseroot'] = '??';
                if (isset($cols['ido'])) $values['ido'] = gmdate('Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP($abbrevWorksheet->getCellByColumnAndRow($cols['ido'], $rowNumber)->getValue()));
                if (isset($books_gepi2id[(int)substr($gepi, 0, 3)])) {
                    $values['book_id'] = $books_gepi2id[(int)substr($gepi, 0, 3)];
                } else {
                    $this->error("A " . (int)substr($gepi, 0, 3) . "-hez nincs `book_id`");
                    App::abort(500, 'Valami gond van a books id/gepi párossal!');
                }

                //if((isset($_REQUEST['gepi']) AND preg_match('/'.$_REQUEST['gepi'].'/i',$gepi)) OR !isset($_REQUEST['gepi'])) {}
                if (!$this->option('verbose')) {
                    echo "\e[1A";
                }
                echo $abbrev . " " . $values['gepi'];
                if ($this->option('verbose')) {
                    echo ": " . substr($values['verse'], 0, 140);
                }
                echo "\n";
                $inserts[$rowNumber] = $values;
            }
        }
        if (!$this->option('verbose')) {
            echo "\e[1A";
            return $inserts;
        }
        return $inserts;
    }

    /**
     * @param $abbrev
     * @return array|string
     */
    private function getFileName($abbrev)
    {
        if ($this->option('file') && $this->option('file') != '{abbrev}.xls') {
            $file = $this->option('file');
            return $file;
        } else {
            $file = $abbrev . ".xls";
            return $file;
        }
    }

    private function getAbbrev2Id($translation)
    {
        $books = $this->bookRepository->getBooksByTranslation($translation->id);
        foreach ($books as $book) {
            $books_abbrev2id[$book->abbrev] = $book->id;
        }
        return $books_abbrev2id;
    }

    /**
     * @param $abbrev
     */
    private function verifyAbbrev($abbrev)
    {
        if (!preg_match("/^(" . Config::get('settings.translationAbbrevRegex') . ")$/", $abbrev)) {
            App::abort(500, 'Hibás fordítás rövidítés!');
        }
    }

    /**
     * @param $konyvoszlopok
     * @param $abbrev
     * @return mixed
     */
    private function verifyBookColumns($konyvoszlopok, $abbrev)
    {
        if (!isset($konyvoszlopok[$abbrev])) {
            App::abort(500, 'Ennél a szövegforrásnál (' . $abbrev . ') nem tudjuk, hogy hol vannak a könyvek rövidítéseit feloldó oszlopok.');
            return $konyvoszlopok;
        }
        return $konyvoszlopok;
    }

    /**
     * @param $hibasrov
     * @param $books_abbrev2id
     */
    private function verifyBadAbbrev($hibasrov, $books_abbrev2id)
    {
        if (isset($hibasrov)) {
            App::abort(500, "A következő rövidítések csak a szövegforrásban találhatóak meg, az adatbázisban nem!\n" . implode(', ', $hibasrov) . print_r($books_abbrev2id, 1));
        }
    }

    /**
     * @param $worksheet
     * @return array
     */
    private function getHeaders(PHPExcel_Worksheet $worksheet)
    {
        $this->info("A fejlécek megszerzése...");
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(FALSE);
            foreach ($cellIterator as $key => $cell) {
                $cols[$cell->getValue()] = $key;
            }
            break;
        }
        return $cols;
    }

    /**
     * @param $fields
     * @param $headers
     * @return array
     */
    private function verifyColumns(&$fields, $headers)
    {
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($headers[$field])) {
                $errors[] = $field;
            }
        }
        if (!empty($errors)) {
            foreach ($headers as $col => $val) {
                if (preg_match('/[A-Z]{3}_hiv/', $col)) $fields['gepi'] = $col;
                if (preg_match('/[A-Z]{3}_old/', $col)) $fields['old'] = $col;
            }
            if (!isset($headers['ido'])) {
                unset($fields['ido']);
            }
        }
        $errors = [];
        foreach ($fields as $field) {
            if (!isset($headers[$field])) {
                $errors[] = $field;
            }
        }
        if (!empty($errors)) {
            $this->error('A következő oszlopok hiányoznak az excel táblából: ' . implode(', ', $errors));
            $this->comment("Létező oszlopok: " . implode(', ', array_keys($headers)));
            App::abort(500, "Probléma az oszlopoknál!");
        }
    }

}
