<?php

/**

 */
class BookTableSeeder extends Seeder
{
    public function run()
    {
        $translation = new \SzentirasHu\Models\Entities\Translation();
        $translation->save();

        $book = new \SzentirasHu\Models\Entities\Book();
        $book->abbrev = "Szám";
        $book->id = 104;
        $book->translation()->associate($translation);
        $book->save();
    }


} 