<?php

namespace SzentirasHu\Models\Repositories;


use SzentirasHu\Models\Entities\Verse;

interface VerseRepository {

    public function getTranslatedChapterVerses($translationId, $bookId, $chapters);

    public function getLeadVerses($translationId, $bookId);

    /**
     * @param int[] $verseIds
     * @return Verse[]
     */
    public function getVersesInOrder($verseIds);

} 