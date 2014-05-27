<?php
/**

 */

namespace SzentirasHu\Controllers\Display\VerseParsers;


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