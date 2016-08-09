<?php

namespace SzentirasHu\Console\Commands;

use App;
use Artisan;
use Cache;
use Config;
use DB;
use Exception;
use Illuminate\Console\Command;
use PHPExcel_Shared_Date;
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
     * @var TranslationRepository
     */
    private $translationRepository;
    /**
     * @var BookRepository
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

        $abbrev = $this->choice('Melyik fordítást töltsük be?', ['BD', 'KG', 'KNB', 'RUF', 'UF', 'SZIT']);
        $this->verifyAbbrev($abbrev);

        $translation = $this->translationRepository->getByAbbrev($abbrev);
        $this->info("Fordítás: {$translation->name} (id: {$translation->id})");

        ini_set('memory_limit', '1024M');

        if ($this->option('file')) {
            $filePath = $this->option('file');
            $this->info("A fájl betöltése innen: " . $this->option('file'));
        } else {
            $filePath = $this->sourceDirectory . "/{$abbrev}";
            $url = Config::get("translations.{$abbrev}.textSource");
            if (empty($url)) {
                App::abort(500, "Nincs megadva a TEXT_SOURCE_{$abbrev} konfiguráció.");
            }
            try {
                $this->info("A fájl letöltése a $url címről...");
                $fp = fopen ($filePath, 'w+');
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

                curl_exec($ch);
                curl_close($ch);
                fclose($fp);
            } catch (Exception $ex) {
                App::abort(500, "Nem sikerült fáljt letölteni a megadott url-ről.");
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
        $this->info("Könyvek lap ellenőrzése");
        $bookRowIterator = $sheets['Könyvek']->getRowIterator();
        $linesRead = 0;
        foreach ($bookRowIterator as $row) {
            // skip first line
            if ($linesRead == 0) {
                $this->info("Első sor átugrása...");
                $linesRead++;
                continue;
            }
            // break on first empty line
            if (empty($row[0])) {
                $this->info("$linesRead sor beolvasva, kész.");
                break;
            }
            $bookAbbrev2Id = $this->getAbbrev2Id($translation);
            $bookNumber = $row[$columns[$abbrev]['gepi']];
            $rov = $row[$columns[$abbrev]['rov']];
            if (!isset($bookAbbrev2Id[$rov]) AND ($rov != '-' AND $rov != '')) {
                $badAbbrevs[] = $rov;
            } else if ($rov != '-' AND $rov != '') {
                $bookNumber2Id[$bookNumber] = $bookAbbrev2Id[$rov];
            }
        }
        if (isset($badAbbrevs)) {
            App::abort(500, "A következő rövidítések csak a szövegforrásban találhatóak meg, az adatbázisban nem!\n" . implode(', ', $badAbbrevs) . print_r($bookAbbrev2Id, 1));
        }

        $verseRowIterator = $sheets[$abbrev]->getRowIterator();
        $headers = $this->getHeaders($verseRowIterator);

        $fields = ['did' => '*Ssz', 'gepi' => 'DCB_hiv', 'tip' => 'jelstatusz', 'verse' => 'jel', 'ido' => 'ido'];
        $this->verifyColumns($fields, $headers);

        $pipes = [];
        if ($this->hunspellEnabled) {
            $descriptorspec = [
                0 => ["pipe", "r"], // stdin
                1 => ["pipe", "w"], // stdout
                2 => ["pipe", "w"]  // stderr
            ];
            $hunspellProcess = proc_open('stdbuf -oL hunspell -m -d hu_HU -i UTF-8', $descriptorspec, $pipes, null, null);
            $this->info("Hunspell-hu indul...");
        }

        $inserts = $this->readLines($verseRowIterator, $headers, $fields, $translation, $bookNumber2Id, $abbrev, $pipes);

        if ($this->hunspellEnabled) {
            fclose($pipes[0]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            if (isset($hunspellProcess)) {
                proc_close($hunspellProcess);
            }
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
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['file', null, InputOption::VALUE_OPTIONAL, 'Ha fájlból szeretnéd betölteni, nem dropboxból, az importálandó fájl elérési útja', null],
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
        $returnVal = shell_exec("echo medve | hunspell -d hu_HU -i UTF-8 -m  2>&1");
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
        $processedVerse = strip_tags($verse);
        $processedVerse = preg_replace("/(,|:|\?|!|;|\.|„|”|»|«|\")/i", ' ', $processedVerse);
        $processedVerse = preg_replace(['/Í/i', '/Ú/i', '/Ő/i', '/Ó/i', '/Ü/i'], ['í', 'ú', 'ő', 'ó', 'ü'], $processedVerse);

        $verseroots = collect();
        preg_match_all('/(\p{L}+)/u', $processedVerse, $words);
        foreach ($words[1] as $word) {
            if (Cache::has("hunspell_{$word}")) {
                $cachedStems = Cache::get("hunspell_{$word}");
                $verseroots = $verseroots->merge($cachedStems);
//                print ("\nAlready cached {$word}\n");
            } else {
//                print("\nnot yet cached {$word}, sending to hunspell\n");
                fwrite($pipes[0], "{$word}\n"); // send start
                $stems = collect();
                while ($line = fgets($pipes[1])) {
                    if (trim($line) !== '') {
                        if (preg_match_all("/st:(\p{L}+)/u", $line, $matches)) {
                            $stems = $stems->merge($matches[1]);
                        } else {
                            $stems->push($word);
                        }
                    } else {
                        $cachedStems = $stems->unique();
                        Cache::put("hunspell_{$word}", $cachedStems, 525948);
                        $verseroots = $verseroots->merge($stems);
                        $this->newStems++;
                        break;
                    }
                } //get answer
            }
        }
        return join(' ', $verseroots->unique()->toArray());
    }


    /**
     * @param $abbrev
     * @param $translation
     * @param $inserts
     */
    private function saveToDb($abbrev, $translation, $inserts)
    {
        $this->info("\nMysql adatbázis lementése...");
        $progressBar = $this->output->createProgressBar(count($inserts));
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
            $slice = array_slice($inserts, $rowNumber, 100);
            DB::table('tdverse')->insert($slice);
            $progressBar->advance(100);
        }
        $progressBar->finish();
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
        $progressBar = $this->output->createProgressBar();
        $progressBar->setRedrawFrequency(25);
        $progressBar->setBarWidth(24);
        $progressBar->setFormat("[%bar%] %message%");
        $verseRowIterator->rewind();
        $verseRowIterator->next();
        $verseRowIterator->next();
        $rowNumber = 0;
        $verseRowIterator->next();
        while ($verseRowIterator->valid()) {
            $row = $verseRowIterator->current();
            if (empty($row[$cols[$fields['gepi']]])) {
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
                    $verseRoot = null;
                }
                $values['verseroot'] = $verseRoot;
                if (isset($cols['ido']) && !empty($row[$cols['ido']])) {
                    $values['ido'] = $row[$cols['ido']];
                }
                if (isset($books_gepi2id[$values['book_number']])) {
                    $values['book_id'] = $books_gepi2id[$values['book_number']];
                } else {
                    $this->error("A " . (int)substr($gepi, 0, 3) . "-hez nincs `book_id`");
                    App::abort(500, 'Valami gond van a books id/gepi párossal!');
                }
                $inserts[$rowNumber] = $values;
            }
            $verseRowIterator->next();
            $rowNumber++;
            $progressBar->setMessage("$rowNumber - {$values['gepi']} - új szavak: {$this->newStems}");
            $progressBar->advance();
        }
        $progressBar->finish();
        return $inserts;
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
