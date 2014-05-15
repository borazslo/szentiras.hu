<?php

namespace SzentirasHu\Controllers\Display;

use Illuminate\Support\Facades\Redirect;
use SzentirasHu\Lib\Reference\CanonicalReference;
use SzentirasHu\Lib\Reference\ParsingException;
use SzentirasHu\Lib\VerseContainer;
use SzentirasHu\Models\Repositories\BookRepository;
use SzentirasHu\Models\Repositories\TranslationRepository;
use SzentirasHu\Models\Repositories\VerseRepository;
use View;


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
    /**
     * @var \SzentirasHu\Models\Repositories\BookRepository
     */
    private $bookRepository;
    /**
     * @var \SzentirasHu\Models\Repositories\VerseRepository
     */
    private $verseRepository;

    function __construct(TranslationRepository $translationRepository, BookRepository $bookRepository, VerseRepository $verseRepository)
    {
        $this->translationRepository = $translationRepository;
        $this->bookRepository = $bookRepository;
        $this->verseRepository = $verseRepository;
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
        try {
            $canonicalRef = CanonicalReference::fromString($reference);
            if ($canonicalRef->isBookLevel()) {
                return $this->bookView($translationAbbrev, $canonicalRef);
            }
            $translation = $this->translationRepository->getByAbbrev($translationAbbrev);
            $verseContainers = $this->getTranslatedVerses($canonicalRef, $translation);
            return View::make('textDisplay.verses')->with([
                'verseContainers' => $verseContainers,
                'translation' => $translation,
                'translations' => $this->translationRepository->getAllOrderedByDenom(),
                'canonicalUrl' => $canonicalRef->getCanonicalUrl($translation),
                'metaTitle' => $this->getTitle($verseContainers, $translation),
                'teaser' => $this->getTeaser($verseContainers)
            ]);
        } catch (ParsingException $e) {
            // as this doesn't look like a valid reference, interpret as full text search
            return Redirect::action("SzentirasHu\Controllers\Search\SearchController@anySearch", ['textToSearch' => $reference]);
        }
    }

    private function bookView($translationAbbrev, CanonicalReference $canonicalRef)
    {
        $bookRef = $canonicalRef->bookRefs[0];
        $translation = $this->translationRepository->getByAbbrev($translationAbbrev);
        $translatedRef = $canonicalRef->toTranslated($translation->id);
        $book = $this->bookRepository->getByAbbrevForTranslation($bookRef->bookId, $translation->id);
        if ($book) {
            $firstVerses = $this->verseRepository->getLeadVerses($translation->id, $book->id);
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
                'groupedVerses' => $groupedVerses,
                'translations' => $this->translationRepository->getAllOrderedByDenom()
            ]);

        }

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
            $book = $this->bookRepository->getByAbbrevForTranslation($bookRef->bookId, $translation->id);
            $verseContainer = new VerseContainer($book, $bookRef);
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

    public function getChapterRangeVerses($chapterRange, $book, $searchedChapters, $translation)
    {
        $allChapterVerses = $this->verseRepository->getTranslatedChapterVerses($translation->id, $book->id, $searchedChapters);
        $chapterRangeVerses = [];
        foreach ($allChapterVerses as $verse) {
            if ($chapterRange->hasVerse($verse->chapter, $verse->numv)) {
                $chapterRangeVerses[] = $verse;
            }
        }
        return $chapterRangeVerses;
    }

    private function getTitle($verseContainers, $translation)
    {
        $title = "";
        $title .= "{$translation->name}";
        foreach ($verseContainers as $verseContainer) {
            if (isset($verseContainer->book)) {
                $title .= " - {$verseContainer->book->name}";
            }
            if (isset($verseContainer->bookRef)) {
                $title .= " - {$verseContainer->bookRef->toString()}";
            }
        }
        return $title;
    }

    /**
     * @param VerseContainer[] $verseContainers
     * @return string
     */
    private function getTeaser($verseContainers)
    {
        $teaser = "";
        foreach ($verseContainers as $verseContainer) {
            $teaser .= $verseContainer->getParsedVerses()[0]->text . '... ';
        }
        return $teaser;
    }

}
