<?php
/**

 */

namespace SzentirasHu\Data\Repository;

use SzentirasHu\Data\Entity\Book;
use SzentirasHu\Data\Entity\Verse;

class VerseRepositoryEloquent implements VerseRepository {

    public function getVerses($bookId)
    {
        $verses = Book::find($bookId)->verses()->orderBy('gepi')->get();
        return $verses;
    }

    public function getTranslatedChapterVerses($bookId, $chapters)
    {
        $verses = Verse::where('book_id', $bookId)->
        whereIn('chapter', $chapters)->
        orderBy('gepi')
            ->get();
        return $verses;

    }

    public function getLeadVerses($bookId)
    {
        return Verse::where('book_id', $bookId)
            ->whereIn('numv', ['1', '2'])
            ->orderBy('chapter')
            ->orderBy('numv')
            ->get();
    }

    public function getVersesInOrder($verseIds)
    {
        $verses = Verse::whereIn('id', $verseIds)->with([
            'translation',
            'book'])->get();
        foreach ($verses as $verse) {
            $idVerseMap[$verse->id] = $verse;
        }
        return array_replace(array_flip($verseIds), $idVerseMap);
    }
}