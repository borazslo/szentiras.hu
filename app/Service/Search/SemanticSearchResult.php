<?php

namespace SzentirasHu\Service\Search;

use SzentirasHu\Data\Entity\EmbeddedExcerpt;

class SemanticSearchResult {

    public $distance;
    public EmbeddedExcerpt $embeddedExcerpt;
    public $verseContainers;
    public $topVerseContainers;
    public $highlightedGepis;
}