<?php

namespace SzentirasHu\Data\Repository;


use SzentirasHu\Data\Entity\Verse;

interface VerseRepository {

    public function getTranslatedChapterVerses($bookId, $chapters);

    public function getLeadVerses($bookId);

    /**
     * @param int[] $verseIds
     * @return Verse[]
     */
    public function getVersesInOrder($verseIds);

    public function getVerses($bookId);

} 