<?php

namespace SzentirasHu\Controllers\Display;

use SzentirasHu\Lib\Reference\CanonicalReference;
use SzentirasHu\Models\Entities\Book;
use SzentirasHu\Models\Entities\Translation;
use SzentirasHu\Models\Entities\Verse;

/**
 *
 * @author berti
 */
class TextDisplayController extends \BaseController
{

    public function showTranslation($translationAbbrev)
    {
        $translation = Translation::where('abbrev', $translationAbbrev)->first();
        $books = $translation->books()->orderBy('id')->get();
        return \View::make('textDisplay.translation',
            ['translation' => $translation,
                'books' => $books]);
    }

    public function showReferenceText($reference)
    {
        return $this->showTranslatedReferenceText(\Config::get('settings.defaultTranslationAbbrev'), $reference);
    }

    public function showTranslatedReferenceText($translationAbbrev, $reference)
    {
        $canonicalRef = CanonicalReference::fromString($reference);
        if ($canonicalRef->isBookLevel()) {
            return $this->bookView($translationAbbrev, $canonicalRef);
        }
        $translation = Translation::byAbbrev($translationAbbrev);
        $translatedRef = $canonicalRef->toTranslated($translation->id);
        $verseContainers = [];
        foreach ($translatedRef->bookRefs as $bookRef) {
            $verseContainer = [];
            $book = Book::where('abbrev', $bookRef->bookId)->where('translation_id', $translation->id)->first();
            $verseContainer['book'] = $book;
            $verseContainer['verses'] = [];
            foreach ($bookRef->chapterRanges as $chapterRange) {
                $searchedChapters = [];
                $currentChapter = $chapterRange->chapterRef->chapterId;
                do {
                    $searchedChapters[] = $currentChapter;
                    $currentChapter++;
                } while ($chapterRange->untilChapterRef && $currentChapter <= $chapterRange->untilChapterRef->chapterId);
                $verses = Verse::where('book', $book->id)->
                whereIn('chapter', $searchedChapters)->
                where('trans', $translation->id)->
                    orderBy('gepi')
                    ->get();
                foreach ($verses as $verse) {
                    if ($chapterRange->hasVerse($verse->chapter, $verse->numv)) {
                        $verseContainer['verses'][]= [
                            'text' => $verse->verse,
                            'numv' => $verse->numv,
                            'type' => $verse->getType()
                        ];
                    }
                }
            }
            $verseContainers[] = $verseContainer;
        }
        return \View::make('textDisplay.verses')->with([
            'verseContainers' => $verseContainers,
            'translation' => $translation
        ]);

    }

    private function bookView($translationAbbrev, $canonicalRef)
    {
        $bookRef = $canonicalRef->bookRefs[0];
        $translation = Translation::byAbbrev($translationAbbrev);
        $translatedRef = $canonicalRef->toTranslated($translation->id);
        $book = Book::
        where('abbrev', $translatedRef->bookRefs[0]->bookId)->
        where('translation_id', $translation->id)
            ->first();
        $firstVerses = $book
            ->verses()
            ->where('trans', $translation->id)
            ->whereIn('numv', ['1', '2'])
            ->orderBy('chapter')
            ->orderBy('numv')
            ->get();
        $groupedVerses = [];
        foreach ($firstVerses as $verse) {
            $type = $verse->getType();
            if ($type == 'text') {
                $groupedVerses[$verse['chapter']][$verse['numv']] = $verse;
            }
        }

        return \View::make('textDisplay.book', [
            'translation' => $translation,
            'reference' => $translatedRef,
            'book' => $book,
            'groupedVerses' => $groupedVerses
        ]);

    }

}
