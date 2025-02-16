<?php

namespace SzentirasHu\Console\Commands;

use Illuminate\Console\Command;
use League\Flysystem\FilesystemException;
use OpenAI\Exceptions\ErrorException;
use OpenAI\Laravel\Facades\OpenAI as OpenAI;
use Pgvector\Laravel\Distance;
use Pgvector\Laravel\Vector;
use Storage;
use SzentirasHu\Data\Entity\Book;
use SzentirasHu\Data\Entity\EmbeddedExcerpt;
use SzentirasHu\Data\Entity\EmbeddedExcerptScope;
use SzentirasHu\Data\Entity\Translation;
use SzentirasHu\Service\Reference\CanonicalReference;
use SzentirasHu\Service\Reference\ReferenceService;
use SzentirasHu\Service\Search\SemanticSearchService;
use SzentirasHu\Service\Text\BookService;
use SzentirasHu\Service\Text\TextService;
use SzentirasHu\Service\Text\TranslationService;

use function PHPUnit\Framework\isEmpty;

class CreateEmbeddingVectors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '
        szentiras:create-embedding-vectors 
            {translation=KNB : The abbreviation of the translation for which the vectors are being generated.} 
            {--b|book= : The abbreviation of the book(s) if we don\'t want to generate vectors for all of them, e.g., Gen, 2Cor.}
            {--u|update : Request the vectors again, even if we\'ve already retrieved them for the given verse. However, we check the hash and only request again if the text has changed.}
            {--forceUpdate : Request the vectors again, even if we\'ve already retrieved them for the given verse. We don\'t check the hash; always request again.}
            {--target=db : Possible values: db, filesystem, s3. Where to work. If not specified, it saves to the database.}
            {--deleteAll : Delete all vectors for the given translation and request them again. Takes the target into account!}            
            {--vectorFiles= : Read vectors from file: filesystem or s3. If not specified, it generates them. Reading from S3 requires the proper configuration. If this parameter is specified, the target can only be the database.}
            {--scope= : The scope of the generated vectors: verse, chapter, range. If not specified, it generates all.}
            {--compress=true : If the vectors are saved or read from files, compression is on.}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create embedding vectors, either to DB, filesystem or S3.';

    private string $model;
    private int $dimensions;
    private int $tokenCount = 0;
    private int $maxRetry = 3;
    private int $windowSize = 10;
    private int $stepSize = 5;
    private string $currentBookFile;
    private array $currentBookFileData;
    private $progressBar;

    public function __construct(
        protected TextService $textService,
        protected BookService $bookService,
        protected TranslationService $translationService,
        protected ReferenceService $referenceService,
        protected SemanticSearchService $semanticSearchService
    ) {
        parent::__construct();

        $this->model = \Config::get("settings.ai.embeddingModel");
        $this->dimensions = \Config::get("settings.ai.embeddingDimensions");
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option("vectorFiles") && $this->option("target") != "db") {
            $this->error("Reading vectors from file is only supported when the target is the database.");
            return;
        }
        $translationAbbrev = $this->argument('translation');
        $translation = $this->translationService->getByAbbreviation($translationAbbrev);
        if ($this->option("deleteAll")) {
            if ($this->option("target") == "db") {
                EmbeddedExcerpt::where("translation_abbrev", $translation->abbrev)->delete();
                $this->info("Deleted all vectors for translation {$translation->abbrev}");
            } else if ($this->isFileTarget() || $this->isS3Target()) {
                $storage = $this->selectTargetStorage();
                $files = $storage->files();
                $files = array_filter($files, fn($file) => str_starts_with($file, "vector_" . $translation->abbrev));
                $storage->delete($files);
                $this->info("Deleted all vector files for translation {$translation->abbrev} in target {$this->option('target')}");
            }
        }
        $books = $this->bookService->getBooksForTranslation($translation);
        if ($this->option("book")) {
            $bookAbbrevs = array_map("trim", explode(",", $this->option("book")));
            $books = $books->filter(fn($book) => in_array($book->abbrev, $bookAbbrevs));
        }
        $this->info("Generating vectors for {$books->count()} book(s).");        
        foreach ($books as $book) {
            $this->processBook($book);
        }
    }

    private function processBook(Book $book) {
        $chapterCount = $this->bookService->getChapterCount($book, $book->translation);
        $this->progressBar = $this->output->createProgressBar($chapterCount+1);
        $this->progressBar->setFormat("[%bar%] %message%");
        $this->progressBar->setMessage("Starting book {$book->abbrev}");
        $this->progressBar->start();

        $this->currentBookFile = "vector_" . $book->translation->abbrev . "_" . $book->number  . ".dat";                                            
        if ($this->isFileTarget() || $this->isS3Target()) {
            $storage = $this->selectTargetStorage();
            try {
                if ($storage->exists($this->currentBookFile)) {
                    $file = $storage->get($this->currentBookFile);
                    $this->currentBookFileData = $this->unserialize($file);
                } else {
                    $this->currentBookFileData  = [];
                }                            

            } catch (FilesystemException $e) {
                $this->error("Error reading file: {$e->getMessage()}");
                return;
            }
        } else {
            // the target is the database, load the file if needed/exists
            if ($this->option("vectorFiles")) {
                $storage = $this->selectInputStorage();
                if ($storage->exists($this->currentBookFile)) {
                    $file = $storage->get($this->currentBookFile);
                    $this->currentBookFileData = $this->unserialize($file);
                } else {
                    $this->currentBookFileData = [];
                    $this->output->newline();
                    $this->error("Vector file not found: '{$this->currentBookFile}'. To load the vectors to the database, remove the --vectorFiles option or try an other method.");
                    return false;
                }                
            }
        }

        if (!$this->option("scope") || $this->option("scope")=="range") {
            $chapterLengths = [];
            for ($chapter = 1; $chapter <= $chapterCount; $chapter++) {
                $chapterLengths[] = $this->bookService->getVerseCount($book, $chapter, $book->translation);
            }

            $slidingWindows = $this->generateSlidingWindows($chapterLengths, $this->windowSize, $this->stepSize);
            $this->progressBar->setProgress(0);
            $this->progressBar->setMaxSteps(count($slidingWindows));
            foreach ($slidingWindows as $window) {
                $fromChapter = $window[0];
                $fromVerse = $window[1];
                $toChapter = $window[2];
                $toVerse = $window[3];
                $reference = "{$book->abbrev} {$fromChapter},{$fromVerse}-{$toChapter},{$toVerse}";
                $canonicalReference = CanonicalReference::fromString($reference, $book->translation->id);
                $text = $this->textService->getPureText($canonicalReference, $book->translation);
                $this->embedExcerpt($canonicalReference, $text, EmbeddedExcerptScope::Range, $book->translation, $book, $fromChapter, $fromVerse, $toChapter, $toVerse);
            }
            $this->progressBar->advance();
        }

        for ($chapter = 1; $chapter <= $chapterCount; $chapter++) {
            $verseCount = $this->bookService->getVerseCount($book, $chapter, $book->translation);
            $this->progressBar->setProgress(0);
            $this->progressBar->setMaxSteps($verseCount);
            $chapterReference = "{$book->abbrev} $chapter";
            $canonicalReference = CanonicalReference::fromString($chapterReference, $book->translation->id);
            $text = $this->textService->getPureText($canonicalReference, $book->translation);
            if (!$this->option("scope") || $this->option("scope")=="chapter") {                    
                $this->embedExcerpt($canonicalReference, $text, EmbeddedExcerptScope::Chapter, $book->translation, $book, $chapter);
            }
            if (!$this->option("scope") || $this->option("scope")=="verse") {                    
                $verse = 1;
                do {
                    $reference = "{$book->abbrev} $chapter,$verse";
                    $canonicalReference = CanonicalReference::fromString($reference, $book->translation->id);
                    $verseContainers = $this->textService->getTranslatedVerses($canonicalReference, $book->translation->id);
                    $gepi = array_pop($verseContainers[0]->rawVerses)[0]->gepi ?? null;
                    $text = $this->textService->getPureText($canonicalReference, $book->translation);
                    $this->embedExcerpt($canonicalReference, $text, EmbeddedExcerptScope::Verse, $book->translation, $book, $chapter, $verse, null, null, $gepi);
                    $verse++;
                } while (!empty($text));
            }
            $this->progressBar->advance();
        }
        if ($this->isFileTarget() || $this->isS3Target()) {
            $this->writeCurrentFileData();
        }
        $this->progressBar->finish();
        $this->output->newline();
    }


    private function selectTargetStorage() {
        if ($this->isFileTarget()) {
            return Storage::disk("local");
         } else if ($this->isS3Target()) {
            return Storage::disk("s3");
         }  else { 
            throw new \Exception("Invalid target storage.");
         }         
    }

    private function selectInputStorage() {
        if ($this->option("vectorFiles") == "s3") {
            return Storage::disk("s3");
        } else if ($this->option("vectorFiles") == "filesystem") {
            return Storage::disk("local");
        } else {
            throw new \Exception("Invalid input storage.");
        }
    }

    function generateSlidingWindows($chapterLengths, $windowSize = 10, $stepSize = 5) {
        $windows = [];
        $chapters = [];
    
        // Build the book structure
        foreach ($chapterLengths as $chapterIndex => $sections) {
            for ($section = 1; $section <= $sections; $section++) {
                $chapters[] = ['chapter' => $chapterIndex + 1, 'section' => $section];
            }
        }
    
        $totalSections = count($chapters);
    
        // Slide the window over the sections
        for ($start = 0; $start < $totalSections; $start += $stepSize) {
            $end = $start + $windowSize - 1;
            if ($end >= $totalSections) {
                $end = $totalSections - 1;
            }
    
            $from = $chapters[$start];
            $to = $chapters[$end];
    
            $windows[] = [
                $from['chapter'],
                $from['section'],
                $to['chapter'],
                $to['section']
            ];
    
            // Break if we've reached the end
            if ($end == $totalSections - 1) {
                break;
            }
        }
    
        return $windows;
    }

    private function getExistingVector(string $text, $reference, $translation, bool $checkHash = false) {
        $hash = md5($text);
        if ($this->isFileTarget() || $this->isS3Target()) {
            if (array_has($this->currentBookFileData, $this->getFileDataKey($reference, $translation))) {
                $currentData = $this->currentBookFileData[$this->getFileDataKey($reference, $translation)];
                if (!$checkHash || $currentData->hash == $hash) {
                    return $currentData->embedding;
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            $query = EmbeddedExcerpt::where("translation_abbrev", $translation->abbrev)->where("reference", $reference->toString());
            if (!$checkHash) {
                $query= $query->where("hash", $hash);
            }
            $existingDbRecord = $query->first();
            if ($existingDbRecord) {
                $existingDbRecord->embedding;
            } else {
                return null;
            }
        }
    }

    private function getFileDataKey($reference, $translation) {
        return "{$translation->abbrev}_{$reference->toString()}";
    }

    private function embedExcerpt(CanonicalReference $reference, string $text, EmbeddedExcerptScope $scope, Translation $translation, Book $book, int $chapter, int $verse = null, int $toChapter = null, int $toVerse = null, $gepi = null)
    {
        $this->progressBar->setMessage("{$reference->toString()}");
        $this->progressBar->advance();
        $existingVector = $this->getExistingVector($text, $reference, $translation, $this->option("update"));
        $update = empty($existingVector);
        $vectorUpdate = empty($existingVector) || $this->option("forceUpdate");
        if ($update || $vectorUpdate) {
            if ($vectorUpdate && !empty($text)) {
                // retrieve vectors - either from text or from OpenAI
                if ($this->option("vectorFiles")) {
                    if (array_has($this->currentBookFileData, md5($text))) {
                        $response = $this->currentBookFileData[md5($text)];
                        $this->saveEmbeddingExcerpt($text, $reference, $response->embedding, $scope, $translation, $book, $chapter, $verse, $toChapter, $toVerse, $gepi);
                    } else {
                        $this->progressBar->clear();
                        $this->warn("{$reference->toString()} vector not found in file data for the text. Change --vectorFile input or remove the option to regenerate using AI.");
                        $this->progressBar->display();
                        $response = null;
                    }
                } else {
                    $retries = 0;
                    $success = false;
                    while ($retries < $this->maxRetry && !$success) {
                        try {
                            $response = $this->semanticSearchService->generateVector($text, $this->model, $this->dimensions);
                            $success = true;
                        } catch (ErrorException $e) {
                            $retries++;
                            $this->progressBar->clear();
                            $this->info($e->getMessage());
                            $this->info("OpenAI error occurred, might have reached the rate limit. Retrying {$retries}/{$this->maxRetry}. Waiting 15 seconds.");
                            $this->progressBar->display();
                            sleep(15);
                        }
                    }
                    if (!empty($response)) {
                        $this->tokenCount += $response->totalTokens;
                        $this->saveEmbeddingExcerpt($text, $reference, $response->vector, $scope, $translation, $book, $chapter, $verse, $toChapter, $toVerse, $gepi);
                    }
                }
            }
        } else if ($update && !empty($text)) {
            $this->saveEmbeddingExcerpt($text, $reference, $existingVector, $scope, $translation, $book, $chapter, $verse, $toChapter, $toVerse, $gepi);
        }
    }

    private function saveEmbeddingExcerpt(string $text, CanonicalReference $reference, $embedding, EmbeddedExcerptScope $scope, Translation $translation, Book $book, int $chapter, int $verse = null, int $toChapter = null, int $toVerse = null, int $gepi = null) {
        $embeddedExcerpt = new EmbeddedExcerpt();
        $embeddedExcerpt->hash = md5($text);
        $embeddedExcerpt->embedding = $embedding;
        $embeddedExcerpt->model = $this->model;
        $embeddedExcerpt->reference = $reference->toString();
        $embeddedExcerpt->chapter = $chapter;
        $embeddedExcerpt->verse = $verse;
        $embeddedExcerpt->to_chapter = $toChapter;
        $embeddedExcerpt->to_verse = $toVerse;
        $embeddedExcerpt->usx_code = $book->number;
        $embeddedExcerpt->translation_abbrev = $translation->abbrev;
        $embeddedExcerpt->scope = $scope;
        $embeddedExcerpt->gepi = $gepi;
        if ($this->isFileTarget() || $this->isS3Target()) {
            $serializedObject = new SerializedEmbeddedExcerpt();
            $serializedObject->reference = $embeddedExcerpt->reference;
            $serializedObject->embedding = $embeddedExcerpt->embedding->toArray();
            $serializedObject->model = $embeddedExcerpt->model;
            $serializedObject->chapter = $embeddedExcerpt->chapter;
            $serializedObject->verse = $embeddedExcerpt->verse;
            $serializedObject->to_chapter = $embeddedExcerpt->to_chapter;
            $serializedObject->to_verse = $embeddedExcerpt->to_verse;
            $serializedObject->gepi = $embeddedExcerpt->gepi;
            $serializedObject->scope = $embeddedExcerpt->scope->value;
            $serializedObject->translationAbbrev = $translation->abbrev;
            $serializedObject->bookUsxCode = $book->number;
            $this->createFileData($serializedObject, md5($text));
        } else {
            $existingEmbedding = EmbeddedExcerpt::where("translation_abbrev", $translation->abbrev)->where("reference", $reference->toString())->first();
            if (!empty($existingEmbedding)) {
                $existingEmbedding->delete();
            }    
            $embeddedExcerpt->save();
        }        
    }

    private function createFileData(SerializedEmbeddedExcerpt $object, string $textHash) {
        $this->currentBookFileData[$textHash] = $object;
        // write only if file target, as on S3 might be expensive to write after each line
        if ($this->isFileTarget()) {
            $this->writeCurrentFileData();
        }
    }

    private function writeCurrentFileData() {
        $storage = $this->selectTargetStorage();
        $storage->put($this->currentBookFile, $this->serialize($this->currentBookFileData));        
    }

    private function isFileTarget() {
        return $this->option("target") == "filesystem";
    }

    private function isS3Target() {
        return $this->option("target") == "s3";
    }

    private function serialize($object) {
        if ($this->option("compress")) {
            return gzcompress(serialize($object));
        } else {
            return serialize($object);
        }
    }

    private function unserialize($file) {
        if ($this->option("compress")) {
            return unserialize(gzuncompress($file));
        } else {
            return unserialize($file);
        }
    }
    
}

class SerializedEmbeddedExcerpt {
    public string $reference;
    public array $embedding;
    public string $model;
    public int $chapter;
    public ?int $verse;
    public ?int $to_chapter;
    public ?int $to_verse;
    public ?int $gepi;
    public string $scope;
    public string $translationAbbrev;
    public string $bookUsxCode;
}
