<?php

namespace SzentirasHu\Models\Repositories;


interface VerseRepository {

    public function getTranslatedChapterVerses($translationId, $bookId, $chapters);

    public function getLeadVerses($translationId, $bookId);

    public function getVersesInOrder($array_keys);

} 