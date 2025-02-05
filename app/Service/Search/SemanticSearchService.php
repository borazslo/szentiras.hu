<?php

namespace SzentirasHu\Service\Search;

use Config;
use Log;
use OpenAI\Laravel\Facades\OpenAI;
use Pgvector\Laravel\Distance;
use SzentirasHu\EmbeddedVerse;

class SemanticSearchService {

    public function findNeighbors($text, $metric = Distance::Cosine, $maxResults = 10) {
        $model = Config::get("settings.ai.embeddingModel");
        $dimensions = Config::get("settings.ai.embeddingDimensions");
        $response = OpenAI::embeddings()->create([
            'model' => $model,
            'input' => $text,
            'dimensions' => $dimensions
        ]);
        $vector = $response->embeddings[0]->embedding;
        $neighbors = EmbeddedVerse::query()
            ->nearestNeighbors("embedding", $vector, $metric)->take($maxResults)->get();
        $results = [];
        Log::info("OpenAI request finished, total tokens: {$response->usage->totalTokens}");
        foreach ($neighbors as $neighbor) {
            $result = new SemanticSearchResult;
            $result->embeddedVerse = $neighbor;
            $result->distance = $neighbor->neighbor_distance;
            $results[] = $result;
        }
        $response = new SemanticSearchResponse($results, $text, $metric, $response->usage->totalTokens);
        return $response;
    }
}