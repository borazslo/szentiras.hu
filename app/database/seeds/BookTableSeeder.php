<?php

/**

 */
class BookTableSeeder extends Seeder
{
    public function run()
    {
        $translation = $this->addTranslation(1, 'Translation Name 1', 'TESTTRANS');
        $this->addBook(101, "Ter", $translation);
        $this->addBook(102, "Kiv", $translation);

        $translation = $this->addTranslation(2, 'Translation Name 2', 'TESTTRANS2');
        $this->addBook(101, "1Móz", $translation);
        $this->addBook(102, "2Móz", $translation);
    }

    private function addBook($number, $abbrev, $translation)
    {
        $book = new \SzentirasHu\Models\Entities\Book();
        $book->number = $number;
        $book->abbrev = $abbrev;
        $book->translation()->associate($translation);
        $book->save();
        return $book;
    }

    private function addTranslation($id, $name, $abbrev)
    {
        $translation = new \SzentirasHu\Models\Entities\Translation();
        $translation->id = $id;
        $translation->name = $name;
        $translation->abbrev = $abbrev;
        $translation->save();
        return $translation;
    }

} 