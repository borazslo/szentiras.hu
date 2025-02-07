<?php

namespace SzentirasHu\Console\Commands;

use Illuminate\Console\Command;
use OpenAI\Exceptions\ErrorException;
use OpenAI\Laravel\Facades\OpenAI as OpenAI;
use Pgvector\Laravel\Distance;
use Pgvector\Laravel\Vector;
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
            {translation=KNB : A fordítás rövidítése, amihez a vektorokat generáljuk.} 
            {--b|book= : A könyv(ek) rövidítése, ha nem az összeshez szeretnénk vektorokat, pl. Ter,2Kor.}
            {--u|update : Kérje el újra a vektorokat, akkor is, ha már létezik.}
            {--deleteAll : Töröljük az adott fordítás összes vektorját, és kérjük le újra.}
            {--scope= : A generált vektorok hatóköre: verse, chapter, range. Ha nincs megadva, mindegyiket generálja.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private string $model;
    private int $dimensions;
    private int $tokenCount = 0;
    private int $maxRetry = 3;
    private int $windowSize = 10;
    private int $stepSize = 5;

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
        $translationAbbrev = $this->argument('translation');
        $translation = $this->translationService->getByAbbreviation($translationAbbrev);
        if ($this->option("deleteAll")) {
            EmbeddedExcerpt::where("translation_id", $translation->id)->delete();
            $this->info("Deleted vectors for translation {$translation->abbrev}");
        }
        $books = $this->bookService->getBooksForTranslation($translation);
        if ($this->option("book")) {
            $bookAbbrevs = array_map("trim", explode(",", $this->option("book")));
            $books = $books->filter(fn($book) => in_array($book->abbrev, $bookAbbrevs));
        }
        $this->info("Generating vectors for {$books->count()} book(s).");
        $tokenCount = 0;
        foreach ($books as $book) {
            $chapterCount = $this->bookService->getChapterCount($book, $translation);

            if (!$this->option("scope") || $this->option("scope")=="range") {
                $chapterLengths = [];
                for ($chapter = 1; $chapter <= $chapterCount; $chapter++) {
                    $chapterLengths[] = $this->bookService->getVerseCount($book, $chapter, $translation);
                }

                $slidingWindows = $this->generateSlidingWindows($chapterLengths, $this->windowSize, $this->stepSize);
                foreach ($slidingWindows as $window) {
                    $fromChapter = $window[0];
                    $fromVerse = $window[1];
                    $toChapter = $window[2];
                    $toVerse = $window[3];
                    $reference = "{$book->abbrev} {$fromChapter},{$fromVerse}-{$toChapter},{$toVerse}";
                    $canonicalReference = CanonicalReference::fromString($reference, $translation->id);
                    $text = $this->textService->getPureText($canonicalReference, $translation);
                    $this->embedExcerpt($canonicalReference, $text, EmbeddedExcerptScope::Range, $translation, $book, $fromChapter, $fromVerse, $toChapter, $toVerse);
                }
            }

            for ($chapter = 1; $chapter <= $chapterCount; $chapter++) {
                $chapterReference = "{$book->abbrev} $chapter";
                $canonicalReference = CanonicalReference::fromString($chapterReference, $translation->id);
                $text = $this->textService->getPureText($canonicalReference, $translation);
                if (!$this->option("scope") || $this->option("scope")=="chapter") {                    
                    $this->embedExcerpt($canonicalReference, $text, EmbeddedExcerptScope::Chapter, $translation, $book, $chapter);
                }
                if (!$this->option("scope") || $this->option("scope")=="verse") {                    
                    $verse = 1;
                    do {
                        $reference = "{$book->abbrev} $chapter,$verse";
                        $canonicalReference = CanonicalReference::fromString($reference, $translation->id);
                        $verseContainers = $this->textService->getTranslatedVerses($canonicalReference, $translation->id);
                        $gepi = array_pop($verseContainers[0]->rawVerses)[0]->gepi ?? null;
                        $text = $this->textService->getPureText($canonicalReference, $translation);
                        $this->embedExcerpt($canonicalReference, $text, EmbeddedExcerptScope::Verse, $translation, $book, $chapter, $verse, null, null, $gepi);
                        $verse++;
                    } while (!empty($text));
                }
            }
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

    private function existingEmbedding(CanonicalReference $reference, Translation $translation) {
        return EmbeddedExcerpt::whereBelongsTo($translation)->where("reference", $reference->toString())->first();
    }

    private function embedExcerpt(CanonicalReference $reference, string $text, EmbeddedExcerptScope $scope, Translation $translation, Book $book, int $chapter, int $verse = null, int $toChapter = null, int $toVerse = null, $gepi = null)
    {
        $this->info($reference->toString());
        if (empty($this->existingEmbedding($reference, $translation)) || $this->option("update")) {
            if (!empty($text)) {
                $retries = 0;
                $success = false;
                while ($retries < $this->maxRetry && !$success) {
                    try {
                        $response = $this->semanticSearchService->generateVector($text, $this->model, $this->dimensions);
                        $success = true;
                    } catch (ErrorException $e) {
                        $retries ++;
                        $this->info($e->getMessage());
                        $this->info("OpenAI error occurred, might have reached the rate limit. Retrying {$retries}/{$this->maxRetry}. Waiting 15 seconds.");
                        sleep(15);
                    }
                }
                if (!empty($response)) {
                    $this->tokenCount += $response->totalTokens;
                        $this->info("$text");
                        $this->saveEmbeddingExcerpt($reference, $response->vector, $scope, $translation, $book, $chapter, $verse, $toChapter, $toVerse, $gepi);
                    }
                }
            }
    }

    private function saveEmbeddingExcerpt(CanonicalReference $reference, array $embedding, EmbeddedExcerptScope $scope, Translation $translation, Book $book, int $chapter, int $verse = null, int $toChapter = null, int $toVerse = null, int $gepi = null) {
        $existingEmbedding = $this->existingEmbedding($reference, $translation);
        if (!empty($existingEmbedding)) {
            $existingEmbedding->delete();
        }
        $embeddedExcerpt = new EmbeddedExcerpt();
        $embeddedExcerpt->embedding = $embedding;
        $embeddedExcerpt->model = $this->model;
        $embeddedExcerpt->reference = $reference->toString();
        $embeddedExcerpt->chapter = $chapter;
        $embeddedExcerpt->verse = $verse;
        $embeddedExcerpt->to_chapter = $toChapter;
        $embeddedExcerpt->to_verse = $toVerse;
        $embeddedExcerpt->book()->associate($book);
        $embeddedExcerpt->translation()->associate($translation);
        $embeddedExcerpt->scope = $scope;
        $embeddedExcerpt->gepi = $gepi;
        $embeddedExcerpt->save();
    }
}
