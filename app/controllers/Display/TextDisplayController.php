<?php

namespace SzentirasHu\Controllers\Display;

use SzentirasHu\Controllers\Display\VerseParsers\VerseParser;
use SzentirasHu\Lib\Reference\CanonicalReference;
use SzentirasHu\Lib\Reference\ChapterRange;
use SzentirasHu\Models\Entities\Book;
use SzentirasHu\Models\Entities\Verse;
use SzentirasHu\Models\Repositories\TranslationRepository;
use View;


class VerseContainer
{
    /**
     * @var Book
     */
    public $book;


    /**
     * @var VerseParser
     */
    private $verseParser;

    /**
     * @var string[Verse][]
     */
    private $rawVerses;

    function __construct($book)
    {
        $this->book = $book;
        $this->rawVerses = [];
        $this->verseParser = \App::make('verseParsers')[$book->translation_id];
    }

    public function addVerse(Verse $verse)
    {
        $verseKey = $verse->gepi;
        if (!array_key_exists($verseKey, $this->rawVerses)) {
            $this->rawVerses[$verseKey] = [];
        }
        $this->rawVerses[$verseKey][]=$verse;
    }

    public function getParsedVerses() {
        $verseData = [];
        foreach ($this->rawVerses as $gepi => $rawVerses) {
            $parsedVerseData = $this->verseParser->parse($rawVerses, $this->book);
            $parsedVerseData->gepi=$gepi;
            $parsedVerseData->book=$this->book;
            $verseData[] = $parsedVerseData;
        }
        return $verseData;
    }

}

/**
 *
 * @author berti
 */
class TextDisplayController extends \BaseController
{


    /**
     * @var \SzentirasHu\Models\Repositories\TranslationRepository
     */
    private $translationRepository;

    function __construct(TranslationRepository $translationRepository)
    {
        $this->translationRepository = $translationRepository;
    }

    public function showTranslationList()
    {
        $translations = $this->translationRepository->getAllOrderedByDenom();
        return View::make('textDisplay.translationList', [
            'translations' => $translations
        ]);
    }

    public function showTranslation($translationAbbrev)
    {
        $translation = $this->translationRepository->getByAbbrev($translationAbbrev);
        $books = $this->translationRepository->getBooks($translation);
        return View::make('textDisplay.translation',
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
        $translation = $this->translationRepository->getByAbbrev($translationAbbrev);
        $verseContainers = $this->getTranslatedVerses($canonicalRef, $translation);
        return View::make('textDisplay.verses')->with([
            'verseContainers' => $verseContainers,
            'translation' => $translation
        ]);
    }

    private function bookView($translationAbbrev, $canonicalRef)
    {
        $bookRef = $canonicalRef->bookRefs[0];
        $translation = $this->translationRepository->getByAbbrev($translationAbbrev);
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

        return View::make('textDisplay.book', [
            'translation' => $translation,
            'reference' => $translatedRef,
            'book' => $book,
            'groupedVerses' => $groupedVerses
        ]);

    }

    public function getChapterRangeVerses($chapterRange, $book, $searchedChapters, $translation)
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

    /**
     * @param $canonicalRef
     * @param $translation
     * @return array
     */
    public function getTranslatedVerses($canonicalRef, $translation)
    {
        $translatedRef = $canonicalRef->toTranslated($translation->id);
        $verseContainers = [];
        foreach ($translatedRef->bookRefs as $bookRef) {
            $book = Book::where('abbrev', $bookRef->bookId)->where('translation_id', $translation->id)->first();
            $verseContainer = new VerseContainer($book);
            foreach ($bookRef->chapterRanges as $chapterRange) {
                $searchedChapters = DisplayHelper::collectChapterIds($chapterRange);
                $verses = $this->getChapterRangeVerses($chapterRange, $book, $searchedChapters, $translation);
                foreach ($verses as $verse) {
                    $verseContainer->addVerse($verse);
                }
            }
            $verseContainers[] = $verseContainer;
        }
        return $verseContainers;
    }

}
