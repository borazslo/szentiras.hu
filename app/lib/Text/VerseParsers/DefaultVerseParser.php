<?php
/**

 */

namespace SzentirasHu\Lib\Text\VerseParsers;


use SzentirasHu\Controllers\Display\VerseParsers\VerseData;
use SzentirasHu\Controllers\Display\VerseParsers\XRef;

class DefaultVerseParser extends AbstractVerseParser
{

    protected function parseTextVerse($rawVerse, $verse)
    {
        $verse->text = $rawVerse->verse;
    }

    protected function parseXrefverse($book, $rawVerse, $verse)
    {
        $xref = new XRef();
        $xref->text = $rawVerse->verse;
        $verse->xrefs[]= $xref;
    }

    protected function parseHeading($rawVerse, VerseData $verse)
    {
        $level = str_replace('heading','', $rawVerse->getType());
        $verse->headings[$level] = $rawVerse->verse;
    }
}