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
        $verse->simpleText = $rawVerse->verse;
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
        $verse->headings[$level] = $this->replaceTags($rawVerse->verse);
    }

    protected function parseFootnoteVerse(Verse $rawVerse, VerseData $verse) {
        $footnoteText = $rawVerse->verse;
        $footnoteSaved=false;
        foreach ($verse->footnotes as $footnote) {
            if (!$footnote->text) {
                $footnote->text = $footnoteText;
                $footnoteSaved = true;
            }
        }
        if (!$footnoteSaved) {
            $footnote = new Footnote();
            $footnote->text = $footnoteText;
            $verse->footnotes[] = $footnote;

        }
    }

    protected function parsePoemLine($rawVerse, VerseData $verse) {
        $poemLine = $this->replaceTags($rawVerse->verse);
        $verse->poemLines[] = $poemLine;
    }

    protected function replaceTags($rawVerse) {
        return $rawVerse;
    }

}