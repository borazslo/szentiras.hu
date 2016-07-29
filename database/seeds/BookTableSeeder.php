<?php

/**

 */
class BookTableSeeder extends \Illuminate\Database\Seeder
{
    public function run()
    {
        \Log::info('Running book table seeder');
        $translation = $this->addTranslation(1, 'Translation Name 1', 'TESTTRANS');
        $this->addBook(101, 101, "Ter", $translation);
        $this->addBook(102, 102, "Kiv", $translation);
        $this->addBook(103, 103, "Lev", $translation);
        $this->addBook(104, 104, "Szám", $translation);

        $translation = $this->addTranslation(2, 'Translation Name 2', 'TESTTRANS2');
        $this->addBook(201, 101, "1Móz", $translation);
        $this->addBook(202, 102, "2Móz", $translation);
    }

    private function addBook($id, $number, $abbrev, $translation)
    {
        $book = new \SzentirasHu\Data\Entity\Book();
        $book->id = $id;
        $book->number = $number;
        $book->abbrev = $abbrev;
        $book->translation()->associate($translation);
        $book->save();
        return $book;
    }

    private function addTranslation($id, $name, $abbrev)
    {
        $translation = new \SzentirasHu\Data\Entity\Translation();
        $translation->id = $id;
        $translation->name = $name;
        $translation->abbrev = $abbrev;
        $translation->save();
        return $translation;
    }

} 
