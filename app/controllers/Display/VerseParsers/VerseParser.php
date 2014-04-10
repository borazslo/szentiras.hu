<?php
/**

 */

namespace SzentirasHu\Controllers\Display\VerseParsers;


use SzentirasHu\Models\Entities\Book;
use SzentirasHu\Models\Entities\Verse;

interface VerseParser {

    /**
     * parses verses corresponding to one verse
     *
     * @param Verse[] $rawVerses
     * @param Book $book
     * @return VerseData
     */
    public function parse($rawVerses, $book);

} 