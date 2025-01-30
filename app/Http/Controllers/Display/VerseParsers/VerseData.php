<?php
/**

 */
namespace SzentirasHu\Http\Controllers\Display\VerseParsers;

/**
 * This class represents all information we have regarding a given bible verse.
 */
class VerseData
{
    public $headings;
    public $chapter;
    public $numv;
    /**
     * @var Xref[]
     */
    public $xrefs = [];
    public $footnotes = [];
    public $simpleText;
    public $gepi;
    public $book;
    public $poemLines;

    public $elements = [];

    function __construct($chapter, $numv)
    {
        $this->chapter = $chapter;
        $this->numv = $numv;
    }

    public function getHeadingText()
    {
        if ($this->headings !== null && count($this->headings) > 0) {
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

    public function getText() {
        if ($this->poemLines) {
            $poemText = join("<br>", $this->poemLines);
            return $this->simpleText . $poemText;
        } else {
            return $this->simpleText;
        }
    }

}