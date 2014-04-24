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
        $abbrev = BookAbbrev::where('abbrev', $bookAbbrev)->remember(120)->first();
        if ($abbrev) {
            return $abbrev->books()->first();
        }
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

    public function getByIdForTranslation($bookId, $translationId)
    {
        return Book::where('id', $bookId)->where('translation_id', $translationId)->remember(120)->first();
    }
}