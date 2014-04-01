<?php

class CanonicalReferenceTest extends TestCase {

    public function testCanonicalBookString() {
        $s = "1Móz";
        $ref = CanonicalReference::fromCanonicalString($s);
        $this->assertCount(1, $ref->bookRefs);
        $this->assertEquals($s, $ref->bookRefs[0]->bookId);

        $s = "  1Móz;   ";
        $ref = CanonicalReference::fromCanonicalString($s);
        $this->assertCount(1, $ref->bookRefs);
        $this->assertEquals("1Móz", $ref->bookRefs[0]->bookId);

        $s = "2Móz; 2Kor";
        $ref = CanonicalReference::fromCanonicalString($s);
        $this->assertCount(2, $ref->bookRefs);
        $this->assertEquals('2Móz', $ref->bookRefs[0]->bookId);
        $this->assertEquals('2Kor', $ref->bookRefs[1]->bookId);

        $s = "2Móz; Kor;";
        $ref = CanonicalReference::fromCanonicalString($s);
        $this->assertCount(2, $ref->bookRefs);
        $this->assertEquals('2Móz', $ref->bookRefs[0]->bookId);
        $this->assertEquals('Kor', $ref->bookRefs[1]->bookId);

    }

    public function testCanonicalBookWithChapterString() {
        $s = "Kor 13";
        $ref = CanonicalReference::fromCanonicalString($s);
        $bookRef = $ref->bookRefs[0];
        $this->assertEquals("Kor", $bookRef->bookId);
        $this->assertCount(1, $bookRef->chapterRanges);
        $this->assertEquals("13", $bookRef->chapterRanges[0]->chapterRef->chapterId);

        $bookRef = CanonicalReference::fromCanonicalString("1Móz 2b;")->bookRefs[0];
        $this->assertEquals("1Móz", $bookRef->bookId);
        $this->assertEquals("2b", $bookRef->chapterRanges[0]->chapterRef->chapterId);

        $bookRef = CanonicalReference::fromCanonicalString("1Móz2b;")->bookRefs[0];
        $this->assertEquals("1Móz", $bookRef->bookId);
        $this->assertEquals("2b", $bookRef->chapterRanges[0]->chapterRef->chapterId);

        $bookRef = CanonicalReference::fromCanonicalString("1Móz 3-4")->bookRefs[0];
        $this->assertEquals("1Móz", $bookRef->bookId);
        $this->assertEquals("3", $bookRef->chapterRanges[0]->chapterRef->chapterId);
        $this->assertEquals("4", $bookRef->chapterRanges[0]->untilChapterRef->chapterId);

        $bookRef = CanonicalReference::fromCanonicalString("1Móz 3-4|6-7")->bookRefs[0];
        $this->assertEquals("1Móz", $bookRef->bookId);
        $this->assertEquals("3", $bookRef->chapterRanges[0]->chapterRef->chapterId);
        $this->assertEquals("4", $bookRef->chapterRanges[0]->untilChapterRef->chapterId);
        $this->assertEquals("6", $bookRef->chapterRanges[1]->chapterRef->chapterId);
        $this->assertEquals("7", $bookRef->chapterRanges[1]->untilChapterRef->chapterId);

        $ref = CanonicalReference::fromCanonicalString("1Móz 3-4|6-7;2Kor 13");
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
        $bookRef = CanonicalReference::fromCanonicalString("Kor 13,1")->bookRefs[0];
        $this->assertEquals("Kor", $bookRef->bookId);
        $this->assertEquass("13", $bookRef->chapterRanges[0]->chapterRef->chapterId);
    }


}
 