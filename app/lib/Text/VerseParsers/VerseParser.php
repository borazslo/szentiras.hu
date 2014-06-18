<?php
/**

 */

namespace SzentirasHu\Lib\Text\VerseParsers;


use SzentirasHu\Controllers\Display\VerseParsers\VerseData;
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