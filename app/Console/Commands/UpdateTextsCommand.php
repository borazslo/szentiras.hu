<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use SzentirasHu\Data\Repository\BookRepository;
use SzentirasHu\Data\Repository\TranslationRepository;
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;


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
     * @var SzentirasHu\Data\Repository\TranslationRepository
     */
    private $translationRepository;
    /**
     * @var SzentirasHu\Data\Repository\BookRepository
     */
    private $bookRepository;

    private $hunspellEnabled = false;

    private $newStems = 0;
    private $sourceDirectory;


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
        $this->sourceDirectory = Config::get('settings.sourceDirectory');
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

        if ($this->option('file') AND $this->option('url')) {
            App::abort(500, "A forrást vagy url-el VAGY file névvel lehet megadni. Mindkettővel nem.");
        } elseif (!$this->option('file') AND !$this->option('url')) {
            App::abort(500, "A forrást meg kell adni vagy url-el VAGY file névvel.");
        }

        $filePath = $this->getFilePath($abbrev);

        $books_abbrev2id = $this->getAbbrev2Id($translation);

        ini_set('memory_limit', '1024M');

        if ($this->option('url')) {
            try {
                $this->info("A fájl letöltése a megadott címről...");
                $raw = file_get_contents($this->option('url'));
            } catch (Exception $ex) {
                App::abort(500, "Nem sikerült fáljt letölteni a megadott url-ről.");
            }
            try {

                file_put_contents($filePath, $raw);

                unset($raw);
            } catch (Exception $ex) {
                App::abort(500, "Nem sikerült a letöltött fájlt elmenteni.");
            }
        }

        $columns = [
            'SZIT' => ['gepi' => 0, 'rov' => 5],
            'KNB' => ['gepi' => 0, 'rov' => 4],
            'UF' => ['gepi' => 0, 'rov' => 4],
            'KG' => ['gepi' => 0, 'rov' => 4],
            'BD' => ['gepi' => 0, 'rov' => 1],
            'RUF' => ['gepi' => 0, 'rov' => 5],
        ];
        $this->verifyBookColumns($columns, $abbrev);

        $this->info("A $filePath fájl betöltése...");
        $reader = ReaderFactory::create(Type::XLSX);
        $reader->open($filePath);
        $this->info("A $filePath fájl megnyitva...");

        $sheets = $this->getSheets($reader);
        $bookRowIterator = $sheets['Könyvek']->getRowIterator();
        $linesRead = 0;
        foreach ($bookRowIterator as $row) {
            // skip first line
            if ($linesRead == 0) {
                $linesRead++;
                continue;
            }
            // break on first empty line
            if (empty($row[0])) {
                break;
            }
            $gepi = $row[$columns[$abbrev]['gepi']];
            $rov = $row[$columns[$abbrev]['rov']];
            if (!isset($books_abbrev2id[$rov]) AND ($rov != '-' AND $rov != '')) {
                $badAbbrevs[] = $rov;
            } else if ($rov != '-' AND $rov != '') {
                $books_gepi2id[$gepi] = $books_abbrev2id[$rov];
            }
        }
        if (isset($badAbbrevs)) $this->verifyBadAbbrev($badAbbrevs, $books_abbrev2id);

        $verseRowIterator = $sheets[$abbrev]->getRowIterator();
        $headers = $this->getHeaders($verseRowIterator);

        $fields = ['did' => '*Ssz', 'gepi' => 'DCB_hiv', 'tip' => 'jelstatusz', 'verse' => 'jel', 'ido' => 'ido'];
        $this->verifyColumns($fields, $headers);

        $pipes = [];
        if ($this->hunspellEnabled) {
            $descriptorspec = [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "r"]
            ];
            $hunspellProcess = proc_open('hunspell -d hu_HU -i UTF-8', $descriptorspec, $pipes, null, null);
            $this->info("Hunspell-hu indul...");
            if (!$this->option('verbose')) echo "\n";
            if (!$this->option('verbose')) fgets($pipes[1], 4096);
            else echo fgets($pipes[1], 4096);
        }

        $inserts = $this->readLines($verseRowIterator, $headers, $fields, $translation, $books_gepi2id, $abbrev, $pipes);

        if ($this->hunspellEnabled) {
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($hunspellProcess);

        }
        $reader->close();

        if (count($inserts) > 0) {
            $this->saveToDb($abbrev, $translation, $inserts);
        } else {
            $this->info("Nincs mit feltölteni.");
        }
        $sphinxConfig = Config::get('settings.sphinxConfig');
        $indexerProcess = new Process("indexer --config {$sphinxConfig} --all --rotate");
        try {
            $indexerProcess->mustRun();
            echo $indexerProcess->getOutput();
        } catch (ProcessFailedException $e) {
            echo $e->getMessage();
        }


    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['abbrev', InputArgument::REQUIRED, 'A fordítás rövidítése'],
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
            ['file', null, InputOption::VALUE_OPTIONAL, 'Importálandó fájl neve (alapértelmezés: /tmp/[abbrev].xls)', null],
            ['url', null, InputOption::VALUE_OPTIONAL, 'Importálandó fájl URL', null],
            ['nohunspell', null, InputOption::VALUE_NONE, 'Szótöveket ne állítsa elő'],
            ['filter', null, InputOption::VALUE_OPTIONAL, 'Szűrés `gepi` (regex) szerint', null],
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
        $this->hunspellEnabled = true;
    }

    /**
     * @param $path
     * @return \Box\Spout\Reader\XLSX\Sheet[]
     */
    private function getSheets($reader)
    {
        $sheets = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            $sheets[$sheet->getName()] = $sheet;
        }
        if (empty($sheets)) {
            App::abort(500, 'Sikertelen betöltés.');
        }
        return $sheets;
    }

    private function stem($verse, $pipes)
    {
        $processedVerse = strtolower(strip_tags($verse));
        $processedVerse = preg_replace("/(,|:|\?|!|;|\.|„|”|»|«|\")/i", ' ', $processedVerse);
        $processedVerse = preg_replace(['/Í/i', '/Ú/i', '/Ő/i', '/Ó/i', '/Ü/i'], ['í', 'ú', 'ő', 'ó', 'ü'], $processedVerse);

        $verseroot = '';
        $words = preg_split('/\s+/', $processedVerse);
        $s = 0;
        $t = 0;
        foreach ($words as $k => $word) {
            if (Cache::has("hunspell_{$word}")) {
                $verseroot .= " " . Cache::get("hunspell_{$word}");
                $s++;
            } else {
                fwrite($pipes[0], $word . "\n"); // send start
                $return = fgets($pipes[1], 4096); //get answer
                if (trim($return) != '') {
                    fgets($pipes[1], 4096);
                    if ($return{0} == "+") {
                        $t++;
                        $stem = trim(substr($return, 2));
                    } else {
                        $stem = $word;
                    }
                    $verseroot .= " " . $stem;
                    $this->newStems++;
                    Cache::put("hunspell_{$word}", $stem, 120);
                }
            }
        }
        $processedVerse = trim($processedVerse);
        return $processedVerse;
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
        exec('mysqldump -u ' . $conn['username'] . ' --password=' . $conn['password'] . ' ' . $conn['database'] . ' ' . $conn['prefix'] . 'tdverse > ' . $this->sourceDirectory . '/' . $conn['database'] . '_' . $conn['prefix'] . 'tdverse_' . $abbrev . '_' . date('YmdHis') . '.sql');

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
            DB::table('tdverse')->insert($tmp);
        }
        Artisan::call('up');
    }

    /**
     * @param \Box\Spout\Reader\XLSX\RowIterator $verseRowIterator
     * @param $cols
     * @param $fields
     * @param $translation
     * @param $books_gepi2id
     * @param $abbrev
     * @param $inserts
     * @return mixed
     */
    private function readLines($verseRowIterator, $cols, $fields, $translation, $books_gepi2id, $abbrev, $pipes)
    {
        $this->info("Beolvasás sorról sorra...\n");
        $verseRowIterator->rewind();
        $verseRowIterator->next();
        $verseRowIterator->next();
        $verseRowIterator->next();
        $rowNumber = 0;
        while ($verseRowIterator->valid()) {
            $verseRowIterator->next();
            $row = $verseRowIterator->current();
            if (empty($row[0])) {
                break;
            }
            $gepi = $row[$cols[$fields['gepi']]];
            if (!$this->option('filter') OR preg_match('/' . $this->option('filter') . '/i', $gepi)) {
                $values['trans'] = $translation->id;
                $values['gepi'] = $gepi;
                $values['book_number'] = (int)substr($gepi, 0, 3);
                $values['chapter'] = (int)substr($gepi, 3, 3);
                $values['numv'] = (int)substr($gepi, 6, 3);
                $values['tip'] = $row[$cols['jelstatusz']];
                $values['verse'] = $row[$cols['jel']];
                if ($this->hunspellEnabled && in_array($values['tip'], [60, 6, 901, 5, 10, 20, 30, 1, 2, 3, 401, 501, 601, 701, 703, 704])) {
                    $verseRoot = $this->stem($values['verse'], $pipes);
                } else {
                    $verseRoot = '??';
                }
                $values['verseroot'] = $verseRoot;
                if (isset($cols['ido']) && !empty($row[$cols['ido']])) {
                    $values['ido'] = gmdate('Y-m-d H:i:s', PHPExcel_Shared_Date::ExcelToPHP($row[$cols['ido']]));
                }
                if (isset($books_gepi2id[$values['book_number']])) {
                    $values['book_id'] = $books_gepi2id[$values['book_number']];
                } else {
                    $this->error("A " . (int)substr($gepi, 0, 3) . "-hez nincs `book_id`");
                    App::abort(500, 'Valami gond van a books id/gepi párossal!');
                }
                if ($rowNumber % 100 == 0) {
                    echo "$rowNumber - {$abbrev}{$values['gepi']} - új szavak: {$this->newStems}\n";
                    $this->newStems = 0;
                }
                $inserts[$rowNumber] = $values;
            }
            $verseRowIterator->next();
            $rowNumber++;
        }
        return $inserts;
    }

    /**
     * @param $abbrev
     * @return array|string
     */
    private function getFilePath($abbrev)
    {
        if ($this->option('file')) {
            return $this->option('file');
        } else {
            $filePath = "{$this->sourceDir}/{$abbrev}.xls";
            return $filePath;
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
     * @param \Box\Spout\Reader\XLSX\RowIterator $verseRowIterator
     * @return array
     */
    private function getHeaders($verseRowIterator)
    {
        $this->info("A fejlécek megszerzése...");
        foreach ($verseRowIterator as $row) {
            $cols = [];
            $i = 0;
            foreach ($row as $cell) {
                $cols[$cell] = $i;
                $i++;
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
        $this->info("Oszlopok ellenőrzése...");
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
                if (preg_match('/ssz$/i', $col)) $fields['did'] = $col;
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
