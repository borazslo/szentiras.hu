<?php

use SzentirasHu\Data\Entity\Verse;

class VersesTableSeeder extends \Illuminate\Database\Seeder
{

    public function run()
    {
        $this->addVerse(101, 101, 'Ter', 2, 3);
        $this->addVerse(101, 101, 'Ter', 2, 4);
        $this->addVerse(101, 101, 'Ter', 50, 9);
        $this->addVerse(102, 102, 'Kiv', 1, 1);
        $this->addVerse(102, 102, 'Kiv', 2, 3);
        $this->addVerse(102, 102, 'Kiv', 3, 3);
        $this->addVerse(201, 101, '1Móz', 2, 3);
    }

    private function addVerse($bookId, $bookNumber, $bookAbbrev, $chapter, $numv, $opts = [])
    {
        $v = new Verse();
        $v->trans = 1;
        $v->gepi = sprintf("%d%03d%03d00", $bookNumber, $chapter, $numv);
        $v->book_number = $bookNumber;
        $v->book_id = $bookId;
        $v->chapter = $chapter;
        $v->numv = $numv;
        $v->tip = 6;
        $v->verse = "verse " . $v->hiv;
        $v->verseroot = "verseroot" . $v->hiv;
        $v->save();
    }
}