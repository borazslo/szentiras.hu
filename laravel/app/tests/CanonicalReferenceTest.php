<?php

class CanonicalReferenceTest extends TestCase {

    public function testCanonicalBookString() {
        $s = "1Móz";
        $ref = CanonicalReference::fromCanonicalString($s);
        $this->assertCount(1, $ref->bookRefs);
        $this->assertEquals($s, $ref->bookRefs[0]['bookId']);

        $s = "2Móz; 2Kor";
        $ref = CanonicalReference::fromCanonicalString($s);
        $this->assertCount(2, $ref->bookRefs);
        $this->assertEquals('2Móz', $ref->bookRefs[0]['bookId']);
        $this->assertEquals('2Kor', $ref->bookRefs[1]['bookId']);

    }

}
 