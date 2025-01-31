<?php
/**

 */
namespace SzentirasHu\Http\Controllers\Display\VerseParsers;

/**
 * This class represents all information we have regarding a given bible verse.
 * A given bible verse typically consists of 1 simple text. However, sometimes it contains multiple elements:
 * headings, simpleTexts, poemLines, simpleTexts again etc. So it is an ordered list of various VerseParts. 
 * They must be written one after the other with proper formatting.
 */
class VerseData
{
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

    /** @var VersePart[] */
    public array $verseParts = [];

    function __construct($chapter, $numv)
    {
        $this->chapter = $chapter;
        $this->numv = $numv;
    }

    public function getVerseParts() {
        return $this->verseParts;
    }

    /**
     * @return VersePart[]
     */
    public function getHeadingVerseParts(): array {
        return array_filter($this->verseParts, function(VersePart $versePart) {
            return $versePart->type === VersePartType::HEADING && $versePart->headingLevel >=0 && $versePart->headingLevel <= 4;
        });        
    }

    public function getHeadingText()
    {
        $headings = $this->getHeadingVerseParts();
        if (count($headings) > 0) {
            $headingText = '';
            $previousOrder = null;
            foreach ($headings as $heading) {
                if ($previousOrder !== null && $heading->order - $previousOrder > 1) {
                    break;
                }
                $headingText .= $heading->content . ' ';
                $previousOrder = $heading->order;
            }
            return $headingText;
        } else {
            return null;
        }
    }

    public function getText() {
        $text = '';
        foreach ($this->verseParts as $versePart) {
            if ($versePart->type == VersePartType::POEM_LINE) {
                $text .= "{$versePart->content}";
            } else if ($versePart->type == VersePartType::HEADING) {
                $text .= "<h{$versePart->headingLevel}>{$versePart->content}</h{$versePart->headingLevel}>";
            } else {
                $text = $versePart->content;
            }
        }
    }

}