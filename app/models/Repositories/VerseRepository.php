<?php

namespace SzentirasHu\Models\Repositories;


use SzentirasHu\Models\Entities\Verse;

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