<?php
/**

 */

namespace SzentirasHu\Controllers\Display\VerseParsers;


use SzentirasHu\Models\Entities\Book;
use SzentirasHu\Models\Entities\Verse;

abstract class AbstractVerseParser implements VerseParser {

    /**
     * @param $rawVerses
     * @return VerseData
     */
    protected function initVerseData($rawVerses)
    {
        $chapter = $rawVerses[0]->chapter;
        $numv = $rawVerses[0]->numv;
        $verse = new VerseData($chapter, $numv);
        return $verse;
    }

    /**
     * parses verses corresponding to one verse
     *
     * @param Verse[] $rawVerses
     * @param Book $book
     * @return VerseData
     */
    public function parse($rawVerses, $book)
    {
        \Log::debug("Parsing verse data", [$rawVerses]);
        $verse = $this->initVerseData($rawVerses);
        foreach ($rawVerses as $rawVerse) {
            $this->parseRawVerses($book, $rawVerse, $verse);
        }
        foreach ($verse->xrefs as $key => $xref) {
            if (!$xref->text) {
                unset($verse->xrefs[$key]);
            }
        }
        return $verse;
    }

    /**
     * @param Book $book
     * @param Verse $rawVerse
     * @param VerseData $verse
     */
    protected function parseRawVerses($book, $rawVerse, $verse)
    {
        if ($rawVerse->getType() == 'text') {
            $this->parseTextVerse($rawVerse, $verse);
        } else if ($rawVerse ->getType() == 'xref') {
            $this->parseXrefVerse($book, $rawVerse, $verse);
        }
    }

    /**
     * @param $rawVerse
     * @param $verse
     * @return void
     */
    abstract protected function parseTextVerse($rawVerse, $verse);

    /**
     * @param Book $book
     * @param Verse $rawVerse
     * @param VerseData $verse
     * @return void
     */
    abstract protected function parseXrefverse($book, $rawVerse, $verse);

}