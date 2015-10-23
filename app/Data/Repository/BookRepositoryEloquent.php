<?php
/**

 */

namespace SzentirasHu\Data\Repository;


use Cache;
use SzentirasHu\Data\Entity\Book;
use SzentirasHu\Data\Entity\BookAbbrev;

class BookRepositoryEloquent implements BookRepository {


    public function getBooksByTranslation($translationId)
    {
        return Cache::remember("getBooksByTranslation_{$translationId}", 120, function() use ($translationId) {
         return Book::where('translation_id', $translationId)->orderBy('id')->get();
        });
    }

    /**
     * If translationId is not null, abbrevs associated with the given translation are preferred.
     * If translationId is null, abbrevs not associated with anything are preferred..
     *
     */
    public function getByAbbrev($bookAbbrev, $translationId = null)
    {
        return Cache::remember("book_getByAbbrev_{$bookAbbrev}_{$translationId}", 120, function() use ($bookAbbrev, $translationId) {
            $query = BookAbbrev::whereRaw('LOWER(abbrev) = ?', [mb_strtolower($bookAbbrev)]);
            if ($translationId) {
                $query = $query->where(function ($query) use ($translationId) {
                    $query->where('translation_id', $translationId)->orWhere('translation_id', null);
                });
                $query = $query ->orderBy('translation_id', 'desc');
            } else {
                $query = $query ->orderBy('translation_id', 'asc');
            }
            $abbrev = $query->first();
            if ($abbrev) {
                return $abbrev->books()->first();
            } else {
                return false;
            }
        });

    }

    /**
     * @param string $abbrev
     * @param int $translationId
     * @return Book
     */
    public function getByAbbrevForTranslation($abbrev, $translationId)
    {
        return Cache::remember("getBookByNumberForTranslation_{$abbrev}_{$translationId}", 120, function() use ($abbrev, $translationId) {
            $book = $this->getByAbbrev($abbrev, $translationId);
            if ($book) {
                return $this->getByNumberForTranslation($book->number, $translationId);
            }
        });
    }

    public function getByNumberForTranslation($number, $translationId)
    {
        return Cache::remember("getBookByNumberForTranslation_{$number}_{$translationId}", 120, function() use ($translationId, $number) {
            $book = Book::where('number', $number)->where('translation_id', $translationId)->first();
            if ($book == null) {
                return false;
            } else {
                return $book;
            }
        });
    }
}