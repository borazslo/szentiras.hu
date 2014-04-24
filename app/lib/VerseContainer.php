<?php
/**

 */
namespace SzentirasHu\Lib;

use SzentirasHu\Controllers\Display\VerseParsers\VerseParser;
use SzentirasHu\Models\Entities\Book;
use SzentirasHu\Models\Entities\Verse;

/**
 * this class contains verses and their metadata for displaying etc.
 * @package SzentirasHu\Lib
 */
class VerseContainer
{
    /**
     * @var Book
     */
    public $book;
    public $bookRef;
    /**
     * @var VerseParser
     */
    private $verseParser;
    /**
     * @var string[Verse][]
     */
    private $rawVerses;

    function __construct($book, $bookRef=false)
    {
        $this->book = $book;
        $this->rawVerses = [];
        $this->verseParser = \App::make('verseParsers')[$book->translation_id];
        $this->bookRef = $bookRef;
    }

    public function addVerse(Verse $verse)
    {
        $verseKey = $verse->gepi;
        if (!array_key_exists($verseKey, $this->rawVerses)) {
            $this->rawVerses[$verseKey] = [];
        }
        $this->rawVerses[$verseKey][] = $verse;
    }

    public function getParsedVerses()
    {
        $verseData = [];
        foreach ($this->rawVerses as $gepi => $rawVerses) {
            $parsedVerseData = $this->verseParser->parse($rawVerses, $this->book);
            $parsedVerseData->gepi = $gepi;
            $parsedVerseData->book = $this->book;
            $verseData[] = $parsedVerseData;
        }
        return $verseData;
    }

}