<?php
/**
 */

namespace SzentirasHu\Service\Text\VerseParsers;


class VerseParserService
{

    private $parserMapping;

    public function __construct()
    {
        $this->parserMapping =
            [1 => \App::make('\SzentirasHu\Service\Text\VerseParsers\DefaultVerseParser'),
                2 => \App::make('\SzentirasHu\Service\Text\VerseParsers\DefaultVerseParser'),
                3 => \App::make('\SzentirasHu\Service\Text\VerseParsers\KNBVerseParser'),
                4 => \App::make('\SzentirasHu\Service\Text\VerseParsers\KGVerseParser'),
                5 => \App::make('\SzentirasHu\Service\Text\VerseParsers\DefaultVerseParser'),
                6 => \App::make('\SzentirasHu\Service\Text\VerseParsers\DefaultVerseParser'),
            ];
    }

    public function getParser($translation_id)
    {
        return $this->parserMapping[$translation_id];
    }
}