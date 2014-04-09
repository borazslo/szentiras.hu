<?php

use SzentirasHu\Models\Entities\Verse;

class VerseTest extends TestCase {

	public function testType()
	{
        $verse = new Verse();
        $verse->tip=6;
        $this->assertEquals('text', $verse->getType());

        $verse = new Verse();
        $verse->tip=5;
        $this->assertEquals('headings0', $verse->getType());

	}

}