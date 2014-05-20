<?php
/**

 */

namespace SzentirasHu\Lib\Search;


use Config;
use Sphinx\SphinxClient;
use SphinxSearch;

class SphinxSearcher
{
    /**
     * @var SphinxSearch
     */
    private $sphinxClient;

    public function __construct(FullTextSearchParams $params)
    {
        $this->sphinxClient = SphinxSearch::
        search($params->text)
            ->setMatchMode(SphinxClient::SPH_MATCH_EXTENDED)
            ->setSortMode(SphinxClient::SPH_SORT_EXTENDED, "@relevance DESC, gepi ASC");
        if ($params->limit) {
            $limit = $params->limit;
        } else {
            $limit = (int)Config::get('settings.searchLimit') + 1;
        }
        $this->sphinxClient = $this->sphinxClient->limit($limit);
        if ($params->translationId) {
            $this->sphinxClient = $this->sphinxClient->filter('trans', $params->translationId);
        }
        if (count($params->bookIds) > 0) {
            $this->sphinxClient = $this->sphinxClient->filter('book', $params->bookIds);
        }
    }

    /**
     * @return FullTextSearchResult|false
     */
    public function get()
    {
        $sphinxResult = $this->sphinxClient->get();
        if ($sphinxResult) {
            $fullTextSearchResult = new FullTextSearchResult();
            $fullTextSearchResult->verseIds = array_keys($sphinxResult['matches']);
            $fullTextSearchResult->hitCount = count($sphinxResult['matches']);

            return $fullTextSearchResult;
        }
    }

}