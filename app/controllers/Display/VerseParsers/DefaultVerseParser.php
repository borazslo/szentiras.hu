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
}