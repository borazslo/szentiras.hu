<?php

namespace SzentirasHu\Test\Smoke;
use SzentirasHu\Service\Search\Searcher;

/**

 */
class SearcherStub implements Searcher
{

    public function get()
    {
        return false;
    }

    public function getExcerpts($verses)
    {
        return [];
    }
}