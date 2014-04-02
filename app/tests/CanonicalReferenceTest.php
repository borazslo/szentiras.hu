<?php

class CanonicalReferenceTest extends TestCase {

    public function testCanonicalBookString() {
        $s = "1Móz";
        $ref = CanonicalReference::fromString($s);
        $this->assertCount(1, $ref->bookRefs);
        $this->assertEquals($s, $ref->bookRefs[0]->bookId);

        $s = "  1Móz.;   ";
        $ref = CanonicalReference::fromString($s);
        $this->assertCount(1, $ref->bookRefs);
        $this->assertEquals("1Móz", $ref->bookRefs[0]->bookId);

        $s = "2Móz; 2Kor";
        $ref = CanonicalReference::fromString($s);
        $this->assertCount(2, $ref->bookRefs);
        $this->assertEquals('2Móz', $ref->bookRefs[0]->bookId);
        $this->assertEquals('2Kor', $ref->bookRefs[1]->bookId);

        $s = "2Móz; Kor;";
        $ref = CanonicalReference::fromString($s);
        $this->assertCount(2, $ref->bookRefs);
        $this->assertEquals('2Móz', $ref->bookRefs[0]->bookId);
        $this->assertEquals('Kor', $ref->bookRefs[1]->bookId);

    }

    public function testCanonicalBookWithChapterString() {
        $s = "Kor 13";
        $ref = CanonicalReference::fromString($s);
        $bookRef = $ref->bookRefs[0];
        $this->assertEquals("Kor", $bookRef->bookId);
        $this->assertCount(1, $bookRef->chapterRanges);
        $this->assertEquals("13", $bookRef->chapterRanges[0]->chapterRef->chapterId);

        $bookRef = CanonicalReference::fromString("1Móz 2b;")->bookRefs[0];
        $this->assertEquals("1Móz", $bookRef->bookId);
        $this->assertEquals("2b", $bookRef->chapterRanges[0]->chapterRef->chapterId);

        $bookRef = CanonicalReference::fromString("1Móz2b;")->bookRefs[0];
        $this->assertEquals("1Móz", $bookRef->bookId);
        $this->assertEquals("2b", $bookRef->chapterRanges[0]->chapterRef->chapterId);

        $bookRef = CanonicalReference::fromString("1Móz 3-4")->bookRefs[0];
        $this->assertEquals("1Móz", $bookRef->bookId);
        $this->assertEquals("3", $bookRef->chapterRanges[0]->chapterRef->chapterId);
        $this->assertEquals("4", $bookRef->chapterRanges[0]->untilChapterRef->chapterId);

        $bookRef = CanonicalReference::fromString("1Móz 3-4|6-7")->bookRefs[0];
        $this->assertEquals("1Móz", $bookRef->bookId);
        $this->assertEquals("3", $bookRef->chapterRanges[0]->chapterRef->chapterId);
        $this->assertEquals("4", $bookRef->chapterRanges[0]->untilChapterRef->chapterId);
        $this->assertEquals("6", $bookRef->chapterRanges[1]->chapterRef->chapterId);
        $this->assertEquals("7", $bookRef->chapterRanges[1]->untilChapterRef->chapterId);

        $ref = CanonicalReference::fromString("1Móz 3-4|6-7;2Kor 13");
        $bookRef = $ref->bookRefs[0];
        $this->assertEquals("1Móz", $bookRef->bookId);
        $this->assertEquals("3", $bookRef->chapterRanges[0]->chapterRef->chapterId);
        $this->assertEquals("4", $bookRef->chapterRanges[0]->untilChapterRef->chapterId);
        $this->assertEquals("6", $bookRef->chapterRanges[1]->chapterRef->chapterId);
        $this->assertEquals("7", $bookRef->chapterRanges[1]->untilChapterRef->chapterId);
        $bookRef = $ref->bookRefs[1];
        $this->assertEquals("2Kor", $bookRef->bookId);
        $this->assertEquals("13", $bookRef->chapterRanges[0]->chapterRef->chapterId);
    }

    public function testCanonicalBookWithChapterVerses() {
        $bookRef = CanonicalReference::fromString("Kor 13,1")->bookRefs[0];
        $this->assertEquals("Kor", $bookRef->bookId);
        $this->assertEquals("13", $bookRef->chapterRanges[0]->chapterRef->chapterId);
        $this->assertEquals("1", $bookRef->chapterRanges[0]->chapterRef->verseRanges[0]->verseRef->verseId);

        $bookRef = CanonicalReference::fromString("Kor 13:1")->bookRefs[0];
        $this->assertEquals("Kor", $bookRef->bookId);
        $this->assertEquals("13", $bookRef->chapterRanges[0]->chapterRef->chapterId);
        $this->assertEquals("1", $bookRef->chapterRanges[0]->chapterRef->verseRanges[0]->verseRef->verseId);

        $bookRef = CanonicalReference::fromString("Kor 13,1a-4b")->bookRefs[0];
        $this->assertEquals("Kor", $bookRef->bookId);
        $this->assertEquals("13", $bookRef->chapterRanges[0]->chapterRef->chapterId);
        $this->assertEquals("1a", $bookRef->chapterRanges[0]->chapterRef->verseRanges[0]->verseRef->verseId);
        $this->assertEquals("4b", $bookRef->chapterRanges[0]->chapterRef->verseRanges[0]->untilVerseRef->verseId);

        $bookRef = CanonicalReference::fromString("Kor 13,1a-14,2b")->bookRefs[0];
        $this->assertEquals("Kor", $bookRef->bookId);
        $this->assertEquals("13", $bookRef->chapterRanges[0]->chapterRef->chapterId);
        $this->assertEquals("1a", $bookRef->chapterRanges[0]->chapterRef->verseRanges[0]->verseRef->verseId);
        $this->assertEquals("14", $bookRef->chapterRanges[0]->untilChapterRef->chapterId);
        $this->assertEquals("2b", $bookRef->chapterRanges[0]->untilChapterRef->verseRanges[0]->verseRef->verseId);

    }

    public function testCanonicalBookChapterVerseComplicated() {
        $ref = CanonicalReference::fromString("2Sám 7,4-5a.12-14a.16; Zs 88,2-29; 2Kor 4,13a.14b-5,1b.6-8.7|2,3.4-5,6.7-12");
        $this->assertCount(3, $ref->bookRefs);
        $bookRef = $ref->bookRefs[0];
        $this->assertEquals("2Sám", $bookRef->bookId);
        $this->assertCount(1,$bookRef->chapterRanges);
        $this->assertEquals("7",$bookRef->chapterRanges[0]->chapterRef->chapterId);
        $this->assertCount(3,$bookRef->chapterRanges[0]->chapterRef->verseRanges);
        $this->assertEquals("4", $bookRef->chapterRanges[0]->chapterRef->verseRanges[0]->verseRef->verseId);
        $this->assertEquals("5a", $bookRef->chapterRanges[0]->chapterRef->verseRanges[0]->untilVerseRef->verseId);
        $this->assertEquals("12", $bookRef->chapterRanges[0]->chapterRef->verseRanges[1]->verseRef->verseId);
        $this->assertEquals("14a", $bookRef->chapterRanges[0]->chapterRef->verseRanges[1]->untilVerseRef->verseId);
        $this->assertEquals("16", $bookRef->chapterRanges[0]->chapterRef->verseRanges[2]->verseRef->verseId);
        $this->assertNull($bookRef->chapterRanges[0]->chapterRef->verseRanges[2]->untilVerseRef);
        $bookRef = $ref->bookRefs[1];
        $this->assertEquals("Zs", $bookRef->bookId);
        $this->assertEquals("88",$bookRef->chapterRanges[0]->chapterRef->chapterId);
        $bookRef = $ref->bookRefs[2];
        $this->assertEquals("2Kor", $bookRef->bookId);
        $this->assertCount(2,$bookRef->chapterRanges[0]->chapterRef->verseRanges);
        $this->assertCount(3,$bookRef->chapterRanges[0]->untilChapterRef->verseRanges);
        $this->assertEquals("5", $bookRef->chapterRanges[0]->untilChapterRef->chapterId);
        $this->assertEquals("8", $bookRef->chapterRanges[0]->untilChapterRef->verseRanges[1]->untilVerseRef->verseId);
        $this->assertCount(2,$bookRef->chapterRanges[1]->untilChapterRef->verseRanges);
        $this->assertEquals("12",$bookRef->chapterRanges[1]->untilChapterRef->verseRanges[1]->untilVerseRef->verseId);

    }

    public function testToCanonicalString() {
        $this->assertEquals("2Sám", CanonicalReference::fromString("2Sám")->toString());
        $this->assertEquals("2Sám; 2Kor", $s = CanonicalReference::fromString("  2 Sám. ; 2Kor ;")->toString());
        $this->assertEquals("2Sám 3; 1Kor 2-5; 2Kor 5|6", CanonicalReference::fromString("2Sám 3; 1Kor2-5;2Kor5|6")->toString());
        $this->assertEquals("2Kor 3,1", CanonicalReference::fromString("2Kor 3, 1")->toString());
        $this->assertEquals("Zs 88,2-29", CanonicalReference::fromString("Zs 88:2-29")->toString());

        $complicatedString = ("2Sám7:4-5a.12-14a.16;Zs88,2-29;2Kor4:13a.14b-5:1b.6-8.7|2,3.4-5,6.7-12");
        $this->assertEquals("2Sám 7,4-5a.12-14a.16; Zs 88,2-29; 2Kor 4,13a.14b-5,1b.6-8.7|2,3.4-5,6.7-12", CanonicalReference::fromString($complicatedString)->toString());
    }

}
 