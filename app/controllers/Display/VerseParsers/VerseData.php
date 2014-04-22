<?php
/**

 */
namespace SzentirasHu\Controllers\Display\VerseParsers;

class VerseData
{
    public $heading;
    public $chapter;
    public $numv;
    /**
     * @var Xref[]
     */
    public $xrefs = [];
    public $text;
    public $gepi;
    public $book;

    function __construct($chapter, $numv)
    {
        $this->chapter = $chapter;
        $this->numv = $numv;
    }

}