<?php

namespace SzentirasHu\Console\Commands;

use Illuminate\Console\Command;
use Log;
use OpenAI\Laravel\Facades\OpenAI as OpenAI;
use Pgvector\Laravel\Distance;
use Pgvector\Laravel\Vector;
use SzentirasHu\EmbeddedVerse;
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
            {--B|book= : A könyvek rövidítése, ha nem az összeshez szeretnénk vektorokat.}
            {--deleteVectors : Töröljük az adott fordítás összes vektorját, és kérjük le újra.} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';


    public function __construct(protected TextService $textService, protected BookService $bookService, protected TranslationService $translationService) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $translationAbbrev = $this->argument('translation');
        $translation = $this->translationService->getByAbbreviation($translationAbbrev);
        if ($this->option("deleteVectors")) {
            EmbeddedVerse::where("translation_id", $translation->id)->delete();
            $this->info("Deleted vectors for translation {$translation->abbrev}");
        }
        $books = $this->bookService->getBooksForTranslation($translation);
        if ($this->option("book")) {
            $bookAbbrevs = array_map("trim", explode(",", $this->option("book")));
            $books = $books->filter(fn($book) => in_array($book->abbrev, $bookAbbrevs));
        }
        $this->info("Generating vectors for {$books->count()} book(s).");
        $model = \Config::get("settings.ai.embeddingModel");
        $dimensions = \Config::get("settings.ai.embeddingDimensions");
        
        $tokenCount = 0;
        foreach ($books as $book) {
            $chapterCount = $this->bookService->getChapterCount($book, $translation);
            $this->info($chapterCount);
            for ($chapter=1; $chapter <= $chapterCount; $chapter++) {
                $verse = 1;
                do {
                    $reference = "{$book->abbrev} $chapter, $verse";
                    $text = $this->textService->getPureTextFromNumbers($book->number, $chapter, $verse, $translation);
                    $alreadyStored = !is_null(EmbeddedVerse::where("reference", $reference)->first());
                    if (!$alreadyStored && !empty($text) ) {

                        $response = OpenAI::embeddings()->create([
                            'model' => $model,
                            'input' => $text,
                            'dimensions' => $dimensions
                        ]);
                        if (!empty($response)) {
                            $tokenCount += $response->usage->totalTokens;
                            if (count($response->embeddings) > 0) {
                                $embedding = $response->embeddings[0]->embedding;
                                $this->info("$reference: $text");
                                $embeddedVerse = new EmbeddedVerse();                        
                                $embeddedVerse->embedding = $embedding;
                                $embeddedVerse->model = $model;
                                $embeddedVerse->content = $text;
                                $embeddedVerse->reference = $reference;
                                $embeddedVerse->translation()->associate($translation);
                                $embeddedVerse->save();            
                            }
                        }
                    }
                    $verse++;                    
                } while (!empty($text));
            }
        }
        $response = OpenAI::embeddings()->create([
            'model' => $model,
            'input' => "A fáraó gyűlöli a zsidókat",
            'dimensions' => $dimensions
        ]);
        $vector = $response->embeddings[0]->embedding;
        $neighbors = EmbeddedVerse::query()
            ->nearestNeighbors("embedding", $vector, Distance::L2)->take(5)->get();
        foreach ($neighbors as $neighbor) {
            $this->info("{$neighbor->content} Distance: {$neighbor->neighbor_distance}");
        }
        $tokenCount += $response->usage->totalTokens;
        $this->info("Tokens used: $tokenCount");

    }
    
}
