<?php
/**

 */

class VerseRepositoryTest extends TestCase {

    public function testVersesInOrder() {
        /** @var $repo \SzentirasHu\Models\Repositories\VerseRepositoryEloquent */
        $repo = App::make('\SzentirasHu\Models\Repositories\VerseRepositoryEloquent');
        $verses = $repo->getVersesInOrder([2, 1]);
        $verse = array_pop($verses);
        $this->assertEquals(1, $verse->id);
        $this->assertEquals('TESTTRANS', $verse->translation->abbrev);
        $this->assertEquals('Ter', $verse->book()->first()->abbrev);
    }

} 