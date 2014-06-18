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
        } else {
            return false;
        }
    }

    /**
     * @param string $abbrev
     * @param int $translationId
     * @return Book
     */
    public function getByAbbrevForTranslation($abbrev, $translationId)
    {
        $number = $this->getByAbbrev($abbrev)->number;
        return $this->getByNumberForTranslation($number, $translationId);
    }

    public function getByNumberForTranslation($number, $translationId)
    {
        return Book::where('number', $number)->where('translation_id', $translationId)->remember(120)->first();
    }
}