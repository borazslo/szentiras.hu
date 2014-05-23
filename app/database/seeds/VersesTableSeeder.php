<?php

use SzentirasHu\Models\Entities\Verse;

class VersesTableSeeder extends Seeder
{

    public function run()
    {
        $v = new Verse();
        $v->did = 1;
        $v->trans = 1;
        $v->gepi = 10100200300;
        $v->book_number = 101;
        $v->book_id = 1;
        $v->chapter = 2;
        $v->numv = '3';
        $v->hiv = "Ter 2,3";
        $v->old = 0;
        $v->tip = 6;
        $v->jelenseg = "Könyvbeli versszöveg";
        $v->verse = "verse Ter 2,3";
        $v->versesimple = "versesimple Ter 2,3";
        $v->verseroot = "verseroot Ter 2,3";
        $v->save();

        $v = new Verse();
        $v->did = 1;
        $v->trans = 2;
        $v->gepi = 10100200300;
        $v->book_number = 101;
        $v->book_id = 3;
        $v->chapter = 2;
        $v->numv = '3';
        $v->hiv = "1Móz 2,3";
        $v->old = 0;
        $v->tip = 6;
        $v->jelenseg = "Könyvbeli versszöveg";
        $v->verse = "verse 1Móz 2,3";
        $v->versesimple = "versesimple 1Móz 2,3";
        $v->verseroot = "verseroot 1Móz 2,3";
        $v->save();

        $v = new Verse();
        $v->did = 1;
        $v->trans = 1;
        $v->gepi = 10200300400;
        $v->book_number = 102;
        $v->book_id = 2;
        $v->chapter = 3;
        $v->numv = '4';
        $v->hiv = "Kiv 3,4";
        $v->old = 0;
        $v->tip = 6;
        $v->jelenseg = "Könyvbeli versszöveg";
        $v->verse = "verse Kiv 3,4";
        $v->versesimple = "versesimple Kiv 3,4";
        $v->verseroot = "verseroot Kiv 3,4";
        $v->save();
    }
}