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
    /**
     * @var FullTextSearchParams
     */
    private $params;

    private function addStars($text)
    {
        return preg_replace('/(\w+)/u', '($1 | *$1* )', $text);
    }

    public function __construct(FullTextSearchParams $params)
    {
        $this->sphinxClient = SphinxSearch::
        search($this->addStars($params->text))
            ->setMatchMode(SphinxClient::SPH_MATCH_EXTENDED)
            ->setSortMode(SphinxClient::SPH_SORT_EXTENDED, "@relevance DESC, gepi ASC");
        if ($params->limit) {
            $limit = $params->limit;
        } else {
            $limit = (int)Config::get('settings.searchLimit') + 1;
        }
        $this->sphinxClient->limit($limit);
        if ($params->groupByVerse) {
            $this->sphinxClient->setGroupBy('gepi', SphinxClient::SPH_GROUPBY_ATTR, '@relevance desc');
        }
        if ($params->translationId) {
            $this->sphinxClient->filter('trans', $params->translationId);
        }
        if (count($params->bookIds) > 0) {
             $this->sphinxClient->filter('book', $params->bookIds);
        }
        $this->params = $params;
    }

    public function getExcerpts($verses)
    {
        return $this->sphinxClient->buildExcerpts($verses, "verse", $this->addStars($this->params->text), ['query_mode' => 1]);
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