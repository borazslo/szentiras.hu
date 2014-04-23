<?php
/**

 */

namespace SzentirasHu\Models\Repositories;


use SzentirasHu\Models\Entities\Book;
use SzentirasHu\Models\Entities\BookAbbrev;

class BookRepositoryEloquent implements BookRepository {


    public function getBooksByTranslation($translationId)
    {
        return Book::where('translation_id', $translationId)->remember(120)->orderBy('id')->get();
    }

    public function getByAbbrev($bookAbbrev)
    {
        return BookAbbrev::where('abbrev', $bookAbbrev)->first()->books()->first();
    }

    /**
     * @param string $abbrev
     * @param int $translationId
     * @return Book
     */
    public function getByAbbrevForTranslation($abbrev, $translationId)
    {
        return Book::where('translation_id', $translationId)->where('abbrev', $abbrev)->remember(120)->first();
    }
}