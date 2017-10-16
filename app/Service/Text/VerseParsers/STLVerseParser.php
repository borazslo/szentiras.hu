<?php

namespace SzentirasHu\Service\Text\VerseParsers;

use SzentirasHu\Data\Entity\Verse;
use SzentirasHu\Http\Controllers\Display\VerseParsers\Footnote;
use SzentirasHu\Http\Controllers\Display\VerseParsers\VerseData;
use SzentirasHu\Http\Controllers\Display\VerseParsers\Xref;

class STLVerseParser extends DefaultVerseParser
{
    const FOOTNOTE_REGEXP = '\*+';

    /**
     * @param $rawVerse
     * @param VerseData $verseData
     */
    protected function parseTextVerse($rawVerse, $verseData)
    {
        $purified = $rawVerse->verse;
        if (preg_match_all("/".self::FOOTNOTE_REGEXP."/u", $purified, $matches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE)) {
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                $match = $matches[0][$i];
                $footnotePos = $match[1];
                $footnoteSymbol = $match[0];

                if (array_key_exists($footnoteSymbol, $verseData->footnotes)) {
                    $verseData->footnotes[$footnoteSymbol]->position = $footnotePos;
                } else {
                    $footnote = new Footnote();
                    $footnote->position = $footnotePos;
                    $verseData->footnotes[$footnoteSymbol] = $footnote;
                }
            }
            $purified = preg_replace('/\s*'.self::FOOTNOTE_REGEXP.'/u', '', $purified);
        }
        $verseData->text = $purified;
    }

}