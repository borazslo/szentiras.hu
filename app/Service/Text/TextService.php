<?php
/**
 */

namespace SzentirasHu\Service\Text;

use SzentirasHu\Data\Entity\Book;
use SzentirasHu\Data\Entity\Translation;
use SzentirasHu\Service\Reference\CanonicalReference;
use SzentirasHu\Service\Reference\ReferenceService;
use SzentirasHu\Service\VerseContainer;
use SzentirasHu\Data\Repository\BookRepository;
use SzentirasHu\Data\Repository\TranslationRepository;
use SzentirasHu\Data\Repository\VerseRepository;
use SzentirasHu\Http\Controllers\Display\VerseParsers\VersePart;
use SzentirasHu\Service\Reference\ChapterRange;

class TextService
{
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
    /**
     * @var \SzentirasHu\Data\Repository\TranslationRepository
     */
    private $translationRepository;

    function __construct(ReferenceService $referenceService, BookRepository $bookRepository, VerseRepository $verseRepository, TranslationRepository $translationRepository)
    {
        $this->referenceService = $referenceService;
        $this->bookRepository = $bookRepository;
        $this->verseRepository = $verseRepository;
        $this->translationRepository = $translationRepository;
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

    public function getChapterRangeVerses(ChapterRange $chapterRange, Book $book, $searchedChapters)
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
    public function getPureText($canonicalRef, $translation)
    {
        if (is_string($canonicalRef)) {
            $canonicalRef = CanonicalReference::fromString($canonicalRef);
        }
        $verseContainers = $this->getTranslatedVerses($canonicalRef, $translation->id);
        $text = '';
        foreach ($verseContainers as $verseContainer) {
            $verses = $verseContainer->getParsedVerses();
            foreach ($verses as $verse) {
                $verseText = $verse->getText();
                $verseText = preg_replace('/<[^>]*>/', ' ', $verseText);
                $text .= $verseText . ' ';
            }
        }
        return $text;
    }

    public function getPureTextFromNumbers($bookNumber, $chapterNumber, int $verseNumber, $translation) {
        $reference = $this->referenceService->createReferenceFromNumbers($bookNumber, $chapterNumber, $verseNumber, $translation);
        return $this->getPureText($reference, $translation);
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
            if (sizeof($parsedVerses) > 0) {
                $teaser .= preg_replace('/<\/?[^>]+>/', ' ', $parsedVerses[0]->getText());
                if ($verseContainer != last($verseContainers) || count($parsedVerses) > 1) {
                    $teaser .= ' ... ';
                }
            }
        }
        return $teaser;
    }

     /**
     * @param VerseContainer[] $verseContainers
     * @return VersePart[]
     */
    public function getHeadings($verseContainers)
    {
        $headings = [];
        foreach ($verseContainers as $verseContainer) {
            $parsedVerses = $verseContainer->getParsedVerses();
            foreach ($parsedVerses as $verseData)
            $headings = array_merge($headings, $verseData->getHeadingVerseParts());            
        }      
        return $headings;
    }

} 
