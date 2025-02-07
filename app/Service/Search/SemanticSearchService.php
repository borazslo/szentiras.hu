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
    public function __construct(public array $vector, int $totalTokens, ) {
    }
}

class SemanticSearchService {

    public function __construct(protected TextService $textService) {
    }

    public function generateVector(string $text, string $model = null, int $dimensions = null) {
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

    public function findNeighbors(array $vector, $scope = EmbeddedExcerptScope::Verse, $maxResults = 10, $metric = Distance::Cosine) {
        
        $neighbors = EmbeddedExcerpt::query()
            ->nearestNeighbors("embedding", $vector, $metric);
        if ($scope == EmbeddedExcerptScope::Verse) {
            $neighbors = $neighbors->where("scope", EmbeddedExcerptScope::Verse);
        } else if ($scope == EmbeddedExcerptScope::Chapter) {
            $neighbors = $neighbors->where("scope", EmbeddedExcerptScope::Chapter);
        }
        $neighbors = $neighbors->take($maxResults)->get();
        // if we are looking for chapters, look for the most relevant verse in the chapter
        $topVerseContainers = [];
        $results = [];
        foreach ($neighbors as $neighbor) {
            if ($neighbor->scope == EmbeddedExcerptScope::Chapter) {
                $book = $neighbor->book;
                $topVerseInChapter = EmbeddedExcerpt::query()
                    ->nearestNeighbors("embedding", $vector, $metric)
                    ->whereBelongsTo($book)                    
                    ->where("scope", EmbeddedExcerptScope::Verse)
                    ->where("chapter", $neighbor->chapter)
                    ->first();
                $topVerseContainers = $this->textService->getTranslatedVerses(CanonicalReference::fromString($topVerseInChapter->reference, $topVerseInChapter->translation->id), $topVerseInChapter->translation->id);                
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