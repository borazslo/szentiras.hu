<?php

namespace SzentirasHu\Service\Search;

use Config;
use Log;
use OpenAI\Laravel\Facades\OpenAI;
use Pgvector\Laravel\Distance;
use SzentirasHu\Data\Entity\EmbeddedExcerpt;
use SzentirasHu\Data\Entity\EmbeddedExcerptScope;
use SzentirasHu\Service\Reference\CanonicalReference;
use SzentirasHu\Service\Text\TextService;

class EmbeddingResult {
    /**
     * @param array<float> $vector
     */
    public function __construct(public array $vector, public int $totalTokens ) {
    }
}

class SemanticSearchService {

    public function __construct(protected TextService $textService) {
    }

    public function generateVector(string $text, string $model = null, int $dimensions = null) : EmbeddingResult{
        if (is_null($model)) {
            $model = Config::get("settings.ai.embeddingModel");
        }
        if (is_null($dimensions)) {
            $dimensions = Config::get("settings.ai.embeddingDimensions");
        }
        $response = OpenAI::embeddings()->create([
            'model' => $model,
            'input' => $text,
            'dimensions' => $dimensions,
            'user' => "szentiras.eu"
        ]);
        $vector = $response->embeddings[0]->embedding;
        $totalTokens = $response->usage->totalTokens;
        Log::info("OpenAI request finished, total tokens: {$totalTokens}");
        return new EmbeddingResult($vector, $totalTokens);
    }

    public function findNeighbors(array $vector, $scope = EmbeddedExcerptScope::Verse, $maxResults = 10, $metric = Distance::Cosine, string $model = null) : SemanticSearchResponse {        
        if (is_null($model)) {
            $model = Config::get("settings.ai.embeddingModel");
        }
        $neighbors = EmbeddedExcerpt::query()
            ->where("scope", $scope)
            ->where("model", $model)
            ->nearestNeighbors("embedding", $vector, $metric)
            ->take($maxResults)            
            ->get();
        // if we are looking for chapters, look for the most relevant verse in the chapter
        $topVerseContainers = [];
        $results = [];
        foreach ($neighbors as $neighbor) {
            if ($scope == EmbeddedExcerptScope::Chapter || $scope == EmbeddedExcerptScope::Range) {
                $book = $neighbor->book;
                $topVerseInChapter = EmbeddedExcerpt::query()
                    ->nearestNeighbors("embedding", $vector, $metric)
                    ->where("model", $model)
                    ->whereBelongsTo($book)                    
                    ->where("scope", EmbeddedExcerptScope::Verse);
                if ($scope == EmbeddedExcerptScope::Chapter) {
                    $topVerseInChapter = $topVerseInChapter
                        ->where("chapter",  $neighbor->chapter);
                } else if ($scope == EmbeddedExcerptScope::Range) {
                    $pointerFrom = $neighbor->chapter * 10000 + $neighbor->verse;
                    $pointerTo = $neighbor->to_chapter * 10000 + $neighbor->to_verse;
                    $topVerseInChapter = $topVerseInChapter->whereRaw(
                        "chapter * 10000 + verse >= ? AND chapter * 10000 + verse <= ?",
                        [$pointerFrom, $pointerTo]);
                }
                $topVerseInChapter = $topVerseInChapter->first();
                if (!empty($topVerseInChapter)) {
                    $topVerseContainers = $this->textService->getTranslatedVerses(CanonicalReference::fromString($topVerseInChapter->reference, $topVerseInChapter->translation->id), $topVerseInChapter->translation->id);                
                }
            }
            $result = new SemanticSearchResult;
            $result->embeddedExcerpt = $neighbor;
            $result->distance = $neighbor->neighbor_distance;
            $result->verseContainers = $this->textService->getTranslatedVerses(CanonicalReference::fromString($neighbor->reference, $neighbor->translation->id), $neighbor->translation->id);
            $highlightedGepis = [];
            foreach ($topVerseContainers as $verseContainer) {
                $highlightedGepis = array_map(fn($k) => "{$k}",array_keys($verseContainer->rawVerses));
            }
            $result->highlightedGepis = $highlightedGepis;
            $results[] = $result;
        }        
        $response = new SemanticSearchResponse($results, $metric);
        return $response;
    }
}