<?php

namespace SzentirasHu\Models\Repositories;


use SzentirasHu\Models\Entities\Book;

interface BookRepository {

    public function getBooksByTranslation($translationId);

    /**
     * @param $bookAbbrev
     * @return Book The first book of the given abbrev.
     */
    public function getByAbbrev($bookAbbrev, $translationId = null);

    /**
     * @param string $abbrev
     * @param int $translationId
     * @return Book
     */
    public function getByAbbrevForTranslation($abbrev, $translationId);

    public function getByNumberForTranslation($number, $translationId);

} 