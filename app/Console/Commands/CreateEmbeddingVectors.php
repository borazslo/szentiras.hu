<?php

namespace SzentirasHu\Console\Commands;

use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI as OpenAI;
use Pgvector\Laravel\Distance;
use Pgvector\Laravel\Vector;
use SzentirasHu\Data\Entity\Book;
use SzentirasHu\Data\Entity\EmbeddedExcerpt;
use SzentirasHu\Data\Entity\EmbeddedExcerptScope;
use SzentirasHu\Data\Entity\Translation;
use SzentirasHu\Service\Reference\CanonicalReference;
use SzentirasHu\Service\Reference\ReferenceService;
use SzentirasHu\Service\Text\BookService;
use SzentirasHu\Service\Text\TextService;
use SzentirasHu\Service\Text\TranslationService;

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
            {--deleteAll : Töröljük az adott fordítás összes vektorját, és kérjük le újra.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private string $model;
    private int $dimensions;
    private int $tokenCount = 0;

    public function __construct(
        protected TextService $textService, 
        protected BookService $bookService, 
        protected TranslationService $translationService, 
        protected ReferenceService $referenceService) {
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
            for ($chapter=1; $chapter <= $chapterCount; $chapter++) {                
                $chapterReference = "{$book->abbrev} $chapter";
                $this->embedExcerpt($chapterReference, EmbeddedExcerptScope::Chapter, $translation, $book);
                $verse = 1;
                do {
                    $reference = "{$book->abbrev} $chapter, $verse";
                    $text = $this->embedExcerpt($reference, EmbeddedExcerptScope::Verse, $translation, $book);
                    $verse++;
                } while (!empty($text));
            }
        }
        $response = OpenAI::embeddings()->create([
            'model' => $this->model,
            'input' => "A fáraó gyűlöli a zsidókat",
            'dimensions' => $this->dimensions
        ]);
        $vector = $response->embeddings[0]->embedding;
        $neighbors = EmbeddedExcerpt::query()
            ->nearestNeighbors("embedding", $vector, Distance::L2)->take(5)->get();
        foreach ($neighbors as $neighbor) {
            $this->info("{$neighbor->content} Distance: {$neighbor->neighbor_distance}");
        }
        $tokenCount += $response->usage->totalTokens;
        $this->info("Tokens used: $tokenCount");

    }
    

    private function embedExcerpt(string $reference, EmbeddedExcerptScope $scope, Translation $translation, Book $book) {
        $text = null;
        if ($scope == EmbeddedExcerptScope::Chapter) {
            $canonicalChapterReference = CanonicalReference::fromString($reference, $translation->id);
            $text = $this->textService->getPureText($canonicalChapterReference, $translation);
            $gepi = null;
        } else if ($scope == EmbeddedExcerptScope::Verse) {
            $canonicalReference = CanonicalReference::fromString($reference, $translation->id);
            $verseContainers = $this->textService->getTranslatedVerses($canonicalReference, $translation->id);
            $gepi = array_pop($verseContainers[0]->rawVerses)[0]->gepi ?? null;
            $text = $this->textService->getPureText($canonicalReference, $translation);            
        }
        $existingEmbedding = EmbeddedExcerpt::whereBelongsTo($translation)->where("reference", $reference)->first();
        if (!empty($text) && (empty($existingEmbedding) || $this->option("update")) ) {
            $response = OpenAI::embeddings()->create([
                    'model' => $this->model,
                    'input' => $text,
                    'dimensions' => $this->dimensions
            ]);
            if (!empty($response)) {
                $this->tokenCount += $response->usage->totalTokens;
                if (count($response->embeddings) > 0) {
                    $embedding = $response->embeddings[0]->embedding;
                    $this->info("$reference: $text");
                    if (!empty($existingEmbedding)) {
                        $existingEmbedding->delete();
                    }
                    $embeddedExcerpt = new EmbeddedExcerpt();                        
                    $embeddedExcerpt->embedding = $embedding;
                    $embeddedExcerpt->model = $this->model;
                    $embeddedExcerpt->content = $text;
                    $embeddedExcerpt->reference = $reference;
                    $embeddedExcerpt->book()->associate($book);
                    $embeddedExcerpt->translation()->associate($translation);
                    $embeddedExcerpt->scope = $scope;
                    $embeddedExcerpt->gepi = $gepi;
                    $embeddedExcerpt->save();
                }
            }
        }
        return $text;
    }
}
