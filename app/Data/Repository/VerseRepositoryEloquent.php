<?php
/**

 */

namespace SzentirasHu\Data\Repository;

use Illuminate\Database\Eloquent\Collection;
use SzentirasHu\Data\Entity\Book;
use SzentirasHu\Data\Entity\Translation;
use SzentirasHu\Data\Entity\Verse;

class VerseRepositoryEloquent implements VerseRepository {

    public function getVerses($bookId)
    {
        $verses = Book::find($bookId)->verses()->orderBy('gepi')->get();
        return $verses;
    }

    public function getTranslatedChapterVerses($bookId, $chapters, $types = []) : Collection
    {
        $verses = Verse::where('book_id', $bookId);
        if (!empty($chapters)) {
            $verses->whereIn('chapter', $chapters);
        }
        if (!empty($types)) {
            $verses->whereIn('tip', $types);
        }
        return $verses
            ->orderBy('id')
            ->get();
        

    }

    public function getLeadVerses($bookId)
    {
        return Verse::where('book_id', $bookId)
            ->whereIn('numv', ['1', '2'])
            ->orderBy('chapter')
            ->orderBy('numv')
            ->orderBy('id')
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

    public function getMaxChapterByBookNumber($bookNumber, $translationId)
    {
        return Verse::where('book_number', $bookNumber)->where('trans', $translationId)->max('chapter');
    }

    public function getMaxNumv(Book $book, int $chapter, Translation $translation)
    {
        return Verse::whereBelongsTo($translation)->where("book_number", $book->number)->where('chapter', $chapter)->max('numv');
    }


}