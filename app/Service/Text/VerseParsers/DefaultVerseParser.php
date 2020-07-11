<?php
/**

 */

namespace SzentirasHu\Service\Text\VerseParsers;


use SzentirasHu\Data\Entity\Verse;
use SzentirasHu\Http\Controllers\Display\VerseParsers\Footnote;
use SzentirasHu\Http\Controllers\Display\VerseParsers\VerseData;
use SzentirasHu\Http\Controllers\Display\VerseParsers\Xref;

class DefaultVerseParser extends AbstractVerseParser
{

    protected function parseTextVerse($rawVerse, VerseData $verse)
    {
        $verse->simpleText .= $rawVerse->verse;
        $verse->elements[] = $rawVerse->verse;
    }

    protected function parseXrefverse($book, $rawVerse, VerseData $verse)
    {
        $xref = new Xref();
        $xref->text = $rawVerse->verse;
        $verse->xrefs[]= $xref;
        $verse->elements[] = $xref;
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
        $verse->poemLines[0] = $verse->poemLines[0]." ".$poemLine;
        $verse->elements[] = $poemLine;
    }

    protected function replaceTags($rawVerse) {
        return $rawVerse;
    }

}