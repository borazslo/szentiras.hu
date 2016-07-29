<?php
/**

 */
namespace SzentirasHu\Http\Controllers\Display\VerseParsers;

class VerseData
{
    public $headings;
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

    public function getHeadingText()
    {
        if (count($this->headings) > 0) {
            $headingText = '';
            foreach (range(0, 4) as $headingLevel) {
                if (array_key_exists($headingLevel, $this->headings)) {
                    $headingText .= $this->headings[$headingLevel];
                }
            }
            return $headingText;
        } else {
            return false;
        }
    }

}