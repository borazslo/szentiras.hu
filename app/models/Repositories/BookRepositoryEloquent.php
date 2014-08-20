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

    /**
     * If translationId is not null, abbrevs associated with the given translation are preferred.
     * If translationId is null, abbrevs not associated with anything are preferred..
     *
     */
    public function getByAbbrev($bookAbbrev, $translationId = null)
    {
        $query = BookAbbrev::where('abbrev', $bookAbbrev);
        if ($translationId) {
            $query = $query->where(function ($query) use ($translationId) {
                $query->where('translation_id', $translationId)->orWhere('translation_id', null);
            });
            $query = $query ->orderBy('translation_id', 'desc');
        } else {
            $query = $query ->orderBy('translation_id', 'asc');
        }
        $abbrev = $query->remember(120)->first();
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
        $book = $this->getByAbbrev($abbrev, $translationId);
        if ($book) {
            return $this->getByNumberForTranslation($book->number, $translationId);
        }
    }

    public function getByNumberForTranslation($number, $translationId)
    {
        return Book::where('number', $number)->where('translation_id', $translationId)->remember(120)->first();
    }
}