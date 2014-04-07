<?php

/**

 */
class BookTableSeeder extends Seeder
{
    public function run()
    {
        $translation = new \SzentirasHu\Models\Entities\Translation();
        $translation->id = 1;
        $translation->name='Translation Name';
        $translation->abbrev='TESTTRANS';
        $translation->save();

        $book = new \SzentirasHu\Models\Entities\Book();
        $book->abbrev = "Ter";
        $book->id = 101;
        $book->translation()->associate($translation);
        $book->save();

        $book = new \SzentirasHu\Models\Entities\Book();
        $book->abbrev = "Kiv";
        $book->id = 102;
        $book->translation()->associate($translation);
        $book->save();

        $translation = new \SzentirasHu\Models\Entities\Translation();
        $translation->id = 2;
        $translation->name='Translation Name 2';
        $translation->abbrev='TESTTRANS2';
        $translation->save();

        $book = new \SzentirasHu\Models\Entities\Book();
        $book->abbrev = "1Móz";
        $book->id = 101;
        $book->translation()->associate($translation);
        $book->save();


    }


} 