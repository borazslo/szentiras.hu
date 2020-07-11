<?php

namespace SzentirasHu\Service\Text\VerseParsers;

use SzentirasHu\Http\Controllers\Display\VerseParsers\VerseData;
use SzentirasHu\Http\Controllers\Display\VerseParsers\Xref;

class KNBVerseParser extends DefaultVerseParser
{
    const XREF_REGEXP = '\s*\{([A-Z][^\}^\{]+)\}';

    protected function replaceTags($rawText) {
        $rawText = preg_replace('/\{\{br\}\}/', '<br>', $rawText);
        $rawText = preg_replace('/\{\{ej\}\}/', '', $rawText);
        $rawText = preg_replace('/\{\{i\}\}/', '<em>', $rawText);
        $rawText = preg_replace('/\{\{\/i\}\}/', '</em>', $rawText);
        $purified = preg_replace('/\s*'.self::XREF_REGEXP.'/u', '', $rawText);
        $purified = preg_replace('/<\/?i>/', '', $purified);
        return $purified;
    }

    private function parseXrefs($rawText, VerseData $verseData)
    {
        preg_match_all("/".self::XREF_REGEXP."/u", $rawText, $matches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);
        $count = count($matches[1]);
        for ($i = 0; $i < $count; $i++) {
            $match = $matches[1][$i];
            $xrefPos = $match[1];
            $xrefText = $match[0];
            $xref = new Xref();
            $xref->position = $xrefPos;
            $xref->text = $xrefText;
            $verseData->xrefs[] = $xref;
        }

    }

    protected function parseXrefverse($book, $rawVerse, VerseData $verse)
    {
        $xref = new Xref();
        $xref->text = substr($rawVerse->verse, 1, -1);
        $verse->xrefs[]= $xref;
        $verse->elements[] = $xref;
    }

    /**
     * @param $rawVerse
     * @param VerseData $verseData
     */
    protected function parseTextVerse($rawVerse, VerseData $verseData)
    {
        $rawText = $rawVerse->verse;
        $this->parseXrefs($rawText, $verseData);
        $verseData->simpleText .= $this->replaceTags($rawText);
    }

}