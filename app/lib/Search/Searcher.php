<?php
/**

 */

namespace SzentirasHu\Lib\Search;


interface Searcher {

    public function getExcerpts($verses);

    /**
     * @return FullTextSearchResult|false
     */
    public function get();

} 