<?php
/**

 */

namespace SzentirasHu\Service\Text\VerseParsers;


use SzentirasHu\Http\Controllers\Display\VerseParsers\VerseData;
use SzentirasHu\Data\Entity\Book;
use SzentirasHu\Data\Entity\Verse;

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