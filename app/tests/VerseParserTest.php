<?php

use SzentirasHu\Controllers\Display\VerseParsers\KGVerseParser;
use SzentirasHu\Controllers\Display\VerseParsers\KNBVerseParser;
use SzentirasHu\Models\Entities\Book;
use SzentirasHu\Models\Entities\Verse;

class VerseParserTest extends TestCase
{

    public function testKNBVerseParser()
    {
        $parser = new KNBVerseParser();
        $book = new Book();
        $book->abbrev = "Mt";

        $v = new Verse();
        $chapter = 2;
        $v->chapter = $chapter;
        $numv = 3;
        $v->numv = $numv;
        $v->verse = "abc {Mk 12,34} xyz {Mt 23,45} zyx";
        $v->tip = \Config::get('verseTypes.KNB.text.0');
        $verseData = $parser->parse([$v], $book);

        $this->assertEquals("abc xyz zyx", $verseData->text);
        $this->assertCount(2, $verseData->xrefs);
        $this->assertEquals("Mk 12,34", $verseData->xrefs[0]->text);

    }

    public function testKGVerseParser()
    {

        $parser = new KGVerseParser();
        $book = new Book();
        $book->abbrev = "Mt";

        $v = new Verse();
        $chapter = 2;
        $v->chapter = $chapter;
        $numv = 3;
        $v->numv = $numv;
        $v->verse = "abc " . KGVerseParser::$xrefSigns[0] . " xyz";
        $v->tip = \Config::get('verseTypes.KG.text.0');

        $xrefVerse = new Verse();
        $xrefVerse->chapter = $chapter;
        $xrefVerse->numv = $numv;
        $xrefVerse->verse = KGVerseParser::$xrefSigns[0] . " Mk. 12,34.";
        $xrefVerse->tip = \Config::get('verseTypes.KG.xref.0');;

        $verseData = $parser->parse([$v, $xrefVerse], $book);

        $this->assertEquals($v->chapter, $verseData->chapter);
        $this->assertEquals($v->numv, $verseData->numv);
        $this->assertEquals("abc xyz", $verseData->text);
        $this->assertCount(1, $verseData->xrefs);
        $this->assertEquals("Mk 12,34", $verseData->xrefs[KGVerseParser::$xrefSigns[0]]->text);

        // no inline xref
        $v = new Verse();
        $chapter = 2;
        $v->chapter = $chapter;
        $numv = 3;
        $v->numv = $numv;
        $v->verse = "abc xyz";
        $v->tip = \Config::get('verseTypes.KG.text.0');

        $verseData = $parser->parse([$v, $xrefVerse], $book);

        $this->assertCount(1, $verseData->xrefs);
        $this->assertEquals("Mk 12,34", $verseData->xrefs[KGVerseParser::$xrefSigns[0]]->text);

        $v = new Verse();
        $v->chapter = $chapter;
        $v->numv = $numv;
        $v->verse = "Abc • cde † fgh";
        $v->tip = \Config::get('verseTypes.KG.text.0');
        $ref = new Verse();
        $ref->chapter = $chapter;
        $ref->numv = $numv;
        $ref->verse = "• rész 5,7. † Zsolt. 16,10.";
        $ref->tip = \Config::get('verseTypes.KG.xref.0');
        $verseData = $parser->parse([$v, $ref], $book);
        $this->assertCount(2, $verseData->xrefs);
        $this->assertEquals("Abc cde fgh", $verseData->text);
        $this->assertEquals("Mt 5,7", $verseData->xrefs[KGVerseParser::$xrefSigns[0]]->text);
        $this->assertEquals("Zsolt 16,10", $verseData->xrefs[KGVerseParser::$xrefSigns[1]]->text);


    }

} 