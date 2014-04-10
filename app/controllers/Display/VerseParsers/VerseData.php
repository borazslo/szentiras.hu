<?php
/**

 */
namespace SzentirasHu\Controllers\Display\VerseParsers;

class VerseData
{
    public $chapter;
    public $numv;
    /**
     * @var Xref[]
     */
    public $xrefs = [];
    public $text;

    function __construct($chapter, $numv)
    {
        $this->chapter = $chapter;
        $this->numv = $numv;
    }

}