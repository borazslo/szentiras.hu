<?php
/**

 */

namespace SzentirasHu\Lib\Search;


use Sphinx\SphinxClient;
use SphinxSearch;

class SphinxSearcher
{
    /**
     * @var SphinxSearch
     */
    private $sphinxClient;

    public function __construct($textToSearch, $translation = false)
    {
        $this->sphinxClient = SphinxSearch::
        search($textToSearch)
            ->limit(1000)
            ->setMatchMode(SphinxClient::SPH_MATCH_EXTENDED)
            ->setSortMode(SphinxClient::SPH_SORT_EXTENDED, "@relevance DESC, gepi ASC");
        if ($translation) {
            $this->sphinxClient = $this->sphinxClient->filter('trans', $translation->id);
        }
    }

    /**
     * @return FullTextSearchResult|false
     */
    public function get() {
        $sphinxResult = $this->sphinxClient->get();
        if ($sphinxResult) {
            $fullTextSearchResult = new FullTextSearchResult();
            $fullTextSearchResult->hitCount = $sphinxResult['total_found'];
            $fullTextSearchResult->verseIds = array_keys($sphinxResult['matches']);
            return $fullTextSearchResult;
        }
    }

}