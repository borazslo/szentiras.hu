<?php

namespace SzentirasHu\Controllers\Display\VerseParsers;

use SzentirasHu\Lib\Reference\CanonicalReference;

class KGVerseParser implements VerseParser
{
    public static $xrefSigns = ["•","†"];

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
     * @param $rawVerses
     * @return VerseData
     */
    private function initVerseData($rawVerses)
    {
        $chapter = $rawVerses[0]->chapter;
        $numv = $rawVerses[0]->numv;
        $verse = new VerseData($chapter, $numv);
        return $verse;
    }

    /**
     * @param $book
     * @param $rawVerse
     * @param $verse
     */
    private function parseRawVerses($book, $rawVerse, $verse)
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
     */
    private function parseTextVerse($rawVerse, $verse)
    {
        $verse->text = $rawVerse->verse;
        foreach (self::$xrefSigns as $xrefSign) {
            $xrefSignPos = mb_strpos($rawVerse->verse, $xrefSign);
            if ($xrefSignPos) {
                $this->createXrefHolder($verse, $xrefSign);
                $verse->xrefs[$xrefSign]->position = $xrefSignPos;
                $verse->text = preg_replace("/" . $xrefSign . " ?/u", '', $verse->text);
            }
        }
    }

    /**
     * @param $book
     * @param $rawVerse
     * @param $verse
     */
    private function parseXrefVerse($book, $rawVerse, $verse)
    {
        $xrefParts = preg_split("/([•†][^•†]+)/u", $rawVerse->verse, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE);
        // " • A † B" becomes [ [" ", 0"], ["• A ", 1], ["† B", 5] ]

        foreach ($xrefParts as $part) {
            if (preg_match("/[" . implode(self::$xrefSigns) . "].*/u", $part[0])) {
                // this is a reference part, so just use its position and content
                $xrefSign = mb_substr($part[0], 0, 1);
                $this->createXrefHolder($verse, $xrefSign);
                $refString = str_replace($xrefSign, '', $part[0]);
                $refString = str_replace("rész", $book->abbrev, $refString);
                \Log::debug("Adding refstring as xref: ", ['refstring' => $refString]);
                $verse->xrefs[$xrefSign]->text = CanonicalReference::fromString($refString)->toString();
            }
        }
    }

    /**
     * @param $verse
     * @param $xrefSign
     */
    private function createXrefHolder($verse, $xrefSign)
    {
        if (!array_key_exists($xrefSign, $verse->xrefs)) {
            $verse->xrefs[$xrefSign] = new Xref();
        }
    }

} 