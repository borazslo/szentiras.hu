<?php
/**

 */

namespace SzentirasHu\Service\Text\VerseParsers;


use SzentirasHu\Data\Entity\Verse;
use SzentirasHu\Http\Controllers\Display\VerseParsers\Footnote;
use SzentirasHu\Http\Controllers\Display\VerseParsers\VerseData;
use SzentirasHu\Http\Controllers\Display\VerseParsers\Xref;

class DefaultVerseParser extends AbstractVerseParser
{

    protected function parseTextVerse($rawVerse, VerseData $verse)
    {
        $verse->simpleText .= $rawVerse->verse;
        $verse->elements[] = $rawVerse->verse;
    }

    protected function parseXrefverse($book, $rawVerse, VerseData $verse)
    {
        $xref = new Xref();
        $xref->text = $rawVerse->verse;
        $verse->xrefs[]= $xref;
        $verse->elements[] = $xref;
    }

    protected function parseHeading($rawVerse, VerseData $verse)
    {
        $level = str_replace('heading','', $rawVerse->getType());
        $heading = preg_replace('/([ ]{0,1})<br([\/]{0,1})>([ ]{0,1})/',' – ',$rawVerse->verse);     
        // https://github.com/molnarm/igemutato
        $regexp = "/((?:[12](?:K(?:[io]r|rón)|Makk?|Pé?t(?:er)?|Sám|T(?:h?essz?|im))|[1-3]Já?n(?:os)?|[1-5]Móz(?:es)?|(?:Ap)?Csel|A(?:gg?|bd)|Ám(?:ós)?|B(?:ár|[ií]r(?:ák)?|ölcs)|Dán|É(?:sa|zs|n(?:ek(?:ek|Én)?)?)|E(?:f(?:éz)?|szt?|z(?:s?dr?)?)|Fil(?:em)?|Gal|H(?:a[bg]|ós)|Iz|J(?:ak|á?n(?:os)?|e[lr]|o(?:el)?|ó(?:[bn]|zs|el)|[Ss]ir(?:alm?)?|úd(?:ás)?|ud(?:it)?)|K(?:iv|ol)|L(?:ev|u?k(?:ács)?)|M(?:al(?:ak)?|á?té?|(?:ár)?k|ik|Törv)|N[áe]h|(?:Ó|O)z|P(?:él|ré)d|R(?:óm|[uú]th?)|S(?:ir(?:alm?)?|ír|z?of|zám)|T(?:er|it|ób)|Z(?:ak|of|s(?:olt|id)?))\.?(?:\s*[0-9]{1,3}(?:[,:]\s*[0-9]{1,2}[a-z]?(?:\s*[-–—]\s*[0-9]{1,2}[a-z]?\b(?![,:]))?(?:\.\s*[0-9]{1,2}[a-z]?(?:\s*[-–—]\s*[0-9]{1,2}[a-z]?\b(?![,:]))?)*)?(?:\s*[-–—]\s*[0-9]{1,3}(?:[,:]\s*[0-9]{1,2}[a-z]?(?:\s*[-–—]\s*[0-9]{1,2}[a-z]?\b(?![,:]))?(?:\.\s*[0-9]{1,2}[a-z]?(?:\s*[-–—]\s*[0-9]{1,2}[a-z]?\b(?![,:]))?)*)?)?(?:\s*[\|;]\s*[0-9]{1,3}(?:[,:]\s*[0-9]{1,2}[a-z]?(?:\s*[-–—]\s*[0-9]{1,2}[a-z]?\b(?![,:]))?(?:\.\s*[0-9]{1,2}[a-z]?(?:\s*[-–—]\s*[0-9]{1,2}[a-z]?\b(?![,:]))?)*)?(?:\s*[-–—]\s*[0-9]{1,3}(?:[,:]\s*[0-9]{1,2}[a-z]?(?:\s*[-–—]\s*[0-9]{1,2}[a-z]?\b(?![,:]))?(?:\.\s*[0-9]{1,2}[a-z]?(?:\s*[-–—]\s*[0-9]{1,2}[a-z]?\b(?![,:]))?)*)?)?)*))(?:(?=[^\w])|$)/u";
        $heading = preg_replace_callback($regexp, function($match) {
             return "<a href='${match[0]}'>$match[0]</a>";
        }, $heading);        
        $verse->headings[$level] = $this->replaceTags($heading);
    }

    protected function parseFootnoteVerse(Verse $rawVerse, VerseData $verse) {
        $footnoteText = $rawVerse->verse;
        $footnoteSaved=false;
        foreach ($verse->footnotes as $footnote) {
            if (!$footnote->text) {
                $footnote->text = $footnoteText;
                $footnoteSaved = true;
            }
        }
        if (!$footnoteSaved) {
            $footnote = new Footnote();
            $footnote->text = $footnoteText;
            $verse->footnotes[] = $footnote;

        }
    }

    protected function parsePoemLine($rawVerse, VerseData $verse) {
        $poemLine = $this->replaceTags($rawVerse->verse);
        if ($verse != null && $poemLine !== null && $verse->poemLines !== null) {
            if($verse->poemLines[0] != '') {
                if (strlen($poemLine) > 1) { // don't add line breaks to one character lines.
                    $verse->poemLines[0] .= "<br>"; 
                }
                $verse->poemLines[0] .= $poemLine;
            }
            else
                $verse->poemLines[0] = $poemLine;
            
            $verse->elements[] = $poemLine;
        }
    }

    protected function replaceTags($rawVerse) {
        return $rawVerse;
    }

}