<?php
/**

 */

namespace SzentirasHu\Models\Repositories;

use SzentirasHu\Models\Entities\Verse;

class VerseRepositoryEloquent implements VerseRepository {

    public function getTranslatedChapterVerses($translationId, $bookId, $chapters)
    {
        $verses = Verse::where('book', $bookId)->
        whereIn('chapter', $chapters)->
        where('trans', $translationId)->
        orderBy('gepi')
            ->remember(120)
            ->get();
        return $verses;

    }

    public function getLeadVerses($translationId, $bookId)
    {
        return Verse::where('book', $bookId)
            ->where('trans', $translationId)
            ->whereIn('numv', ['1', '2'])
            ->orderBy('chapter')
            ->orderBy('numv')
            ->remember(120)
            ->get();
    }

    public function getVersesInOrder($verseIds)
    {
        $verses = Verse::whereIn('id', $verseIds)->get();
        foreach ($verses as $verse) {
            $idVerseMap[$verse->id] = $verse;
        }
        return array_replace(array_flip($verseIds), $idVerseMap);
    }
}