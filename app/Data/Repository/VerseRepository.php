<?php

namespace SzentirasHu\Data\Repository;

use SzentirasHu\Data\Entity\Book;
use SzentirasHu\Data\Entity\Translation;
use SzentirasHu\Data\Entity\Verse;

interface VerseRepository {

    public function getTranslatedChapterVerses($bookId, $chapters);

    /**
     * @return Verse
     */
    public function getLeadVerses($bookId);

    /**
     * @param int[] $verseIds
     * @return Verse[]
     */
    public function getVersesInOrder($verseIds);

    public function getVerses($bookId);

    public function getMaxChapterByBookNumber($bookNumber, $translationId);

    public function getMaxNumv(Book $book, int $chapter, Translation $translation);

} 