<?php

class CanonicalReferenceTest extends TestCase {

    public function testCanonicalBookString() {
        $s = "1Móz";
        $ref = CanonicalReference::fromCanonicalString($s);
        $this->assertCount(1, $ref->bookRefs);
        $this->assertEquals($s, $ref->bookRefs[0]['bookAbbrev']);

        $s = "2Móz; 2Kor";
        $ref = CanonicalReference::fromCanonicalString($s);
        $this->assertCount(2, $ref->bookRefs);
        $this->assertEquals($s, $ref->bookRefs[0]['2Móz']);
        $this->assertEquals($s, $ref->bookRefs[1]['2Kor']);

    }

}
 