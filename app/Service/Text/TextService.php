<?php
/**

 */

namespace SzentirasHu\Service\Text;


use SzentirasHu\Service\Reference\CanonicalReference;
use SzentirasHu\Service\Reference\ReferenceService;
use SzentirasHu\Service\VerseContainer;
use SzentirasHu\Data\Repository\BookRepository;
use SzentirasHu\Data\Repository\VerseRepository;

class TextService {
    /**
     * @var \SzentirasHu\Service\Reference\ReferenceService
     */
    private $referenceService;
    /**
     * @var \SzentirasHu\Data\Repository\BookRepository
     */
    private $bookRepository;
    /**
     * @var \SzentirasHu\Data\Repository\VerseRepository
     */
    private $verseRepository;

    function __construct(ReferenceService $referenceService, BookRepository $bookRepository, VerseRepository $verseRepository)
    {
        $this->referenceService = $referenceService;
        $this->bookRepository = $bookRepository;
        $this->verseRepository = $verseRepository;
    }


    /**
     * @param $canonicalRef
     * @param $translation
     * @return VerseContainer[]
     */
    public function getTranslatedVerses($canonicalRef, $translationId)
    {
        $translatedRef = $this->referenceService->translateReference($canonicalRef, $translationId);
        $verseContainers = [];
        foreach ($translatedRef->bookRefs as $bookRef) {
            $book = $this->bookRepository->getByAbbrevForTranslation($bookRef->bookId, $translationId);
            if ($book) {
                $verseContainer = new VerseContainer($book, $bookRef);
                foreach ($bookRef->chapterRanges as $chapterRange) {
                    $searchedChapters = CanonicalReference::collectChapterIds($chapterRange);
                    $verses = $this->getChapterRangeVerses($chapterRange, $book, $searchedChapters);
                    foreach ($verses as $verse) {
                        $verseContainer->addVerse($verse);
                    }
                }
                $verseContainers[] = $verseContainer;
            }
        }
        return $verseContainers;
    }

    public function getChapterRangeVerses($chapterRange, $book, $searchedChapters)
    {
        $allChapterVerses = $this->verseRepository->getTranslatedChapterVerses($book->id, $searchedChapters);
        $chapterRangeVerses = [];
        foreach ($allChapterVerses as $verse) {
            if ($chapterRange->hasVerse($verse->chapter, $verse->numv)) {
                $chapterRangeVerses[] = $verse;
            }
        }
        return $chapterRangeVerses;
    }

    /**
     * @param $canonicalRef CanonicalReference | string
     * @param $translationId int
     * @return string
     */
    public function getPureText($canonicalRef, $translationId)
    {
        if (is_string($canonicalRef)) {
            $canonicalRef = CanonicalReference::fromString($canonicalRef);
        }
        $verseContainers = $this->getTranslatedVerses($canonicalRef, $translationId);
        $text = '';
        foreach ($verseContainers as $verseContainer) {
            $verses = $verseContainer->getParsedVerses();
            foreach ($verses as $verse) {
                $verseText = $verse -> text;
                $verseText = preg_replace('/<[^>]*>/', ' ', $verseText);
                $text .= $verseText.' ';
            }
        }
        return $text;
    }

    /**
     * @param VerseContainer[] $verseContainers
     * @return string
     */
    public function getTeaser($verseContainers)
    {
        $teaser = "";
        foreach ($verseContainers as $verseContainer) {
            $parsedVerses = $verseContainer->getParsedVerses();
            $teaser .= preg_replace('/<\/?[^>]+>/', ' ', $parsedVerses[0]->text);
            if ($verseContainer != last($verseContainers) || count($parsedVerses)>1) {
                $teaser .= ' ... ';
            }
        }
        return $teaser;
    }


} 