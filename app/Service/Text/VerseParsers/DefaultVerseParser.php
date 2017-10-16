<?php
/**

 */

namespace SzentirasHu\Service\Text\VerseParsers;


use SzentirasHu\Data\Entity\Verse;
use SzentirasHu\Http\Controllers\Display\VerseParsers\Footnote;
use SzentirasHu\Http\Controllers\Display\VerseParsers\VerseData;
use SzentirasHu\Http\Controllers\Display\VerseParsers\XRef;

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

    protected function parseFootnoteVerse(Verse $rawVerse, VerseData $verse) {
        $footnoteText = $rawVerse->verse;
        $footnoteSymbol = substr($footnoteText, 0, 1);
        if (array_key_exists($footnoteSymbol, $verse->footnotes)) {
            $verse->footnotes[$footnoteSymbol]->text = substr($footnoteText, strlen($footnoteSymbol));
        } else {
            $footnote = new Footnote();
            $footnote->text = $footnoteText;
            $verse->footnotes[$footnoteSymbol] = $footnote;
        }
    }
}