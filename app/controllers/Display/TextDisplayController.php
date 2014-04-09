<?php

namespace SzentirasHu\Controllers\Display;

use SzentirasHu\Lib\Reference\CanonicalReference;
use SzentirasHu\Models\Entities\Book;
use SzentirasHu\Models\Entities\Translation;
use SzentirasHu\Models\Entities\Verse;


class XRef
{
    public $position;
    public $text;
}

class VerseData
{
    public $chapter;
    public $numv;
    /**
     * @var XRef[]
     */
    public $xrefs;
    public $text;

    function __construct($chapter, $numv)
    {
        $this->chapter = $chapter;
        $this->numv = $numv;
    }

}

class VerseContainer
{

    /**
     * @var Book
     */
    public $book;
    /**
     * @var VerseData[]
     */
    public $verses;

    function __construct($book)
    {
        $this->book = $book;
        $this->verses = [];
    }

    public function addVerse(Verse $verse)
    {
        $verseKey = $verse->gepi;
        if (!array_key_exists($verseKey, $this->verses)) {
            $verseData = new VerseData($verse->chapter, $verse->numv);
            $this->verses[$verseKey] = $verseData;
        } else {
            $verseData = $this->verses[$verseKey];
        }
        if ($verse->getType() == 'text') {
            $verseData->text = $verse->verse;
        }
    }

}

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
            $book = Book::where('abbrev', $bookRef->bookId)->where('translation_id', $translation->id)->first();
            $verseContainer = new VerseContainer($book);
            foreach ($bookRef->chapterRanges as $chapterRange) {
                $searchedChapters = $this->collectChapterIds($chapterRange);
                $verses = $this->getChapterRangeVerses($chapterRange, $book, $searchedChapters, $translation);
                foreach ($verses as $verse) {
                    $verseContainer->addVerse($verse);
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

    /**
     * @param $chapterRange
     * @return array
     */
    private function collectChapterIds($chapterRange)
    {
        $searchedChapters = [];
        $currentChapter = $chapterRange->chapterRef->chapterId;
        do {
            $searchedChapters[] = $currentChapter;
            $currentChapter++;
            return $searchedChapters;
        } while ($chapterRange->untilChapterRef && $currentChapter <= $chapterRange->untilChapterRef->chapterId);
    }

    private function getChapterRangeVerses($chapterRange, $book, $searchedChapters, $translation)
    {
        $allChapterVerses = $this->getChapterVerses($book, $searchedChapters, $translation);
        $chapterRangeVerses = [];
        foreach ($allChapterVerses as $verse) {
            if ($chapterRange->hasVerse($verse->chapter, $verse->numv)) {
                $chapterRangeVerses[] = $verse;
            }
        }
        return $chapterRangeVerses;
    }

    private function getChapterVerses($book, $searchedChapters, $translation)
    {
        $verses = Verse::where('book', $book->id)->
        whereIn('chapter', $searchedChapters)->
        where('trans', $translation->id)->
        orderBy('gepi')
            ->get();
        return $verses;
    }

}
