<?php
/**

 */

namespace SzentirasHu\Lib\Search;

/**
 * Simple factory class to allow creating mock searchers
 * @package SzentirasHu\Lib\Search
 */
class SearcherFactory {

    public function createSearcherFor(FullTextSearchParams $params) {
        return new SphinxSearcher($params);
    }

} 