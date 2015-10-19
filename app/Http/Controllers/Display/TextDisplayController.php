<?php

namespace SzentirasHu\Http\Controllers\Display;

use Config;
use Redirect;
use SzentirasHu\Http\Controllers\Controller;
use SzentirasHu\Service\Reference\CanonicalReference;
use SzentirasHu\Service\Reference\ParsingException;
use SzentirasHu\Service\Reference\ReferenceService;
use SzentirasHu\Service\Text\TextService;
use SzentirasHu\Service\VerseContainer;
use SzentirasHu\Data\Entity\Translation;
use SzentirasHu\Data\Repository\BookRepository;
use SzentirasHu\Data\Repository\TranslationRepository;
use SzentirasHu\Data\Repository\VerseRepository;
use View;


/**
 *
 * @author berti
 */
class TextDisplayController extends Controller
{


    /**
     * @var \SzentirasHu\Data\Repository\TranslationRepository
     */
    private $translationRepository;
    /**
     * @var \SzentirasHu\Data\Repository\BookRepository
     */
    private $bookRepository;
    /**
     * @var \SzentirasHu\Data\Repository\VerseRepository
     */
    private $verseRepository;

    private $referenceService;
    /**
     * @var \SzentirasHu\Service\Text\TextService
     */
    private $textService;

    function __construct(TranslationRepository $translationRepository, BookRepository $bookRepository, VerseRepository $verseRepository, ReferenceService $referenceService, TextService $textService)
    {
        $this->translationRepository = $translationRepository;
        $this->bookRepository = $bookRepository;
        $this->verseRepository = $verseRepository;
        $this->referenceService = $referenceService;
        $this->textService = $textService;
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
        return $this->showTranslatedReferenceText(null, $reference);
    }

    public function showTranslatedReferenceText($translationAbbrev, $reference)
    {
        try {
            $translation = $this->translationRepository->getByAbbrev($translationAbbrev ? $translationAbbrev : Config::get('settings.defaultTranslationAbbrev'));
            $canonicalRef = CanonicalReference::fromString($reference, $translation->id);
            if ($canonicalRef->isBookLevel()) {
                return $this->bookView($translationAbbrev, $canonicalRef);
            }
            $chapterLinks = $canonicalRef->isOneChapter() ?
                $this->createChapterLinks($canonicalRef, $translation)
                : false;
            $verseContainers = $this->textService->getTranslatedVerses($canonicalRef, $translation->id);
            $translations = $this->translationRepository->getAllOrderedByDenom();
            return View::make('textDisplay.verses')->with([
                'verseContainers' => $verseContainers,
                'translation' => $translation,
                'translations' => $translations,
                'canonicalUrl' => $this->referenceService->getCanonicalUrl($canonicalRef, $translation->id),
                'metaTitle' => $this->getTitle($verseContainers, $translation),
                'teaser' => $this->textService->getTeaser($verseContainers),
                'chapterLinks' => $chapterLinks,
                'translationLinks' => $translations->map(
                        function ($otherTranslation) use ($canonicalRef, $translation) {
                            $allBooksExistInTranslation = true;
                            foreach ($canonicalRef->bookRefs as $bookRef) {
                                $book = $this->bookRepository->getByAbbrevForTranslation($bookRef->bookId, $translation->id);
                                if (!$this->getAllBookTranslations($book->number)->contains($otherTranslation->id)) {
                                    $allBooksExistInTranslation = false;
                                    break;
                                }
                            }
                            return [
                                'id' => $otherTranslation->id,
                                'link' => $this->referenceService->getCanonicalUrl($canonicalRef, $otherTranslation->id, $translation->id),
                                'abbrev' => $otherTranslation->abbrev,
                                'enabled' => $allBooksExistInTranslation
                            ];
                        }
                    )
            ]);
        } catch (ParsingException $e) {
            // as this doesn't look like a valid reference, interpret as full text search
            return $this->fallbackSearch($translationAbbrev ? $translation : null, $reference);
        }
    }

    private function bookView($translationAbbrev, CanonicalReference $canonicalRef)
    {
        $translation = $this->translationRepository->getByAbbrev($translationAbbrev ? $translationAbbrev : Config::get('settings.defaultTranslationAbbrev'));
        $translatedRef = $this->referenceService->translateReference($canonicalRef, $translation->id);
        $book = $this->bookRepository->getByAbbrevForTranslation($translatedRef->bookRefs[0]->bookId, $translation->id);
        if ($book) {
            $firstVerses = $this->verseRepository->getLeadVerses($book->id);
            $groupedVerses = [];
            foreach ($firstVerses as $verse) {
                $type = $verse->getType();
                if ($type == 'text') {
                    $verseContainer = new VerseContainer($book);
                    $verseContainer->addVerse($verse);
                    $groupedVerses[$verse['chapter']][$verse['numv']] = $this->textService->getTeaser([$verseContainer]);
                }
            }
            $allTranslations = $this->translationRepository->getAllOrderedByDenom();
            $bookTranslations = $this->getAllBookTranslations($book->number);
            return View::make('textDisplay.book', [
                'translation' => $translation,
                'reference' => $translatedRef,
                'book' => $book,
                'groupedVerses' => $groupedVerses,
                'translations' => $allTranslations,
                'translationLinks' => $allTranslations->map(
                        function ($translation) use ($canonicalRef, $bookTranslations) {
                            $bookExistsInTranslation = $bookTranslations->contains($translation->id);
                            return [
                                'id' => $translation->id,
                                'link' => $this->referenceService->getCanonicalUrl($canonicalRef, $translation->id),
                                'abbrev' => $translation->abbrev,
                                'enabled' => $bookExistsInTranslation];
                        }
                    )

            ]);

        } else {
            return $this->fallbackSearch($translationAbbrev ? $translation : null, $canonicalRef->toString());
        }

    }

    private function fallbackSearch($translation, $reference)
    {
        $location = "/kereses/search?textToSearch={$reference}";
        if ($translation) {
            $location .= "&translation={$translation->id}";
        }
        return Redirect::to($location);
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

    private function createChapterLinks(CanonicalReference $canonicalReference, Translation $translation)
    {
        list($prevRef, $nextRef) = $this->referenceService->getPrevNextChapter($canonicalReference, $translation->id);
        $prevLink = $prevRef ?
            $this->referenceService->getCanonicalUrl($prevRef, $translation->id) :
            false;

        $nextLink = $nextRef ?
            $this->referenceService->getCanonicalUrl($nextRef, $translation->id) :
            false;
        return ['prevLink' => $prevLink, 'nextLink' => $nextLink];
    }

    /**
     * @param $book
     * @return mixed
     */
    private function getAllBookTranslations($bookNumber)
    {
        $translations = $this->translationRepository->getAllOrderedByDenom()->filter(function ($translation) use ($bookNumber) {
                return $this->bookRepository->getByNumberForTranslation($bookNumber, $translation->id);
            }
        );
        return $translations;
    }
}
