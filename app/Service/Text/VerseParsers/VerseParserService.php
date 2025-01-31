<?php
/**
 */

namespace SzentirasHu\Service\Text\VerseParsers;


class VerseParserService
{

    private $parserMapping;

    private $defaultParser; 

    public function __construct()
    {        
        $this->defaultParser = \App::make('\SzentirasHu\Service\Text\VerseParsers\DefaultVerseParser');
        $this->parserMapping =
            [1 => $this->defaultParser,
                2 => $this->defaultParser,
                3 => \App::make('\SzentirasHu\Service\Text\VerseParsers\KNBVerseParser'),
                4 => \App::make('\SzentirasHu\Service\Text\VerseParsers\KGVerseParser'),
                5 => $this->defaultParser,
                6 => $this->defaultParser,
                7 => \App::make('\SzentirasHu\Service\Text\VerseParsers\STLVerseParser'),
            ];
    }

    public function getParser($translation_id)
    {
        $parser = $this->parserMapping[$translation_id] ?? $this->defaultParser;
        return $parser;
    }
}