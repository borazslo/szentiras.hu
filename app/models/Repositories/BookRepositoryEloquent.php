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
}