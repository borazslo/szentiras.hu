<?php

namespace SzentirasHu\Http\Controllers\Home;

use SzentirasHu\Service\LectureDownloader;
use SzentirasHu\Service\Reference\CanonicalReference;
use SzentirasHu\Service\Reference\ReferenceService;
use SzentirasHu\Service\Text\TextService;
use SzentirasHu\Data\Entity\Verse;
use SzentirasHu\Data\Repository\BookRepository;

class LectureSelector
{

    private $translationPriority = [
        3 => 0,
        1 => 1,
        2 => 2,
        4 => 3
    ];

    private $date;
    /**
     * @var \SzentirasHu\Data\Repository\BookRepository
     */
    private $bookRepository;
    /**
     * @var \SzentirasHu\Service\Reference\ReferenceService
     */
    private $referenceService;
    /**
     * @var \SzentirasHu\Service\LectureDownloader
     */
    private $lectureDownloader;
    /**
     * @var \SzentirasHu\Service\Text\TextService
     */
    private $textService;

    public function __construct(BookRepository $bookRepository, ReferenceService $referenceService, LectureDownloader $lectureDownloader, TextService $textService)
    {
        $this->bookRepository = $bookRepository;
        $this->referenceService = $referenceService;
        $this->lectureDownloader = $lectureDownloader;
        $this->textService = $textService;
    }

    public function getLectures()
    {
        $resultLectures = [];
        $referenceString = $this->lectureDownloader->getReferenceString();
        if (!$referenceString) {
            return $resultLectures;
        } else {
            $referenceString = preg_replace('/\s+vagy\s+/', '; ', $referenceString);
        }

        $translationId = \Config::get('settings.defaultTranslationId');
        $ref = CanonicalReference::fromString($referenceString);
        $bookRefs = $this->referenceService->translateReference($ref, $translationId)->bookRefs;

        foreach ($bookRefs as $bookRef) {
            // extract and convert Psalm numbering
            if (preg_match('/Zs.*/', $bookRef->bookId, $matches)) {
                $vulgataNum = $bookRef->chapterRanges[0]->chapterRef->chapterId;
                $bookRef->chapterRanges[0]->chapterRef->chapterId = $this->getHebrewPsalmNum($vulgataNum);
            }
            $lecture = new Lecture();
            $lecture->ref = $bookRef->toString();
            $lecture->teaser = $this->textService->getTeaser($this->textService->getTranslatedVerses(CanonicalReference::fromString($bookRef->toString()), $translationId));
            $lecture->link = str_replace(' ', '', $lecture->ref);
            $lecture->translationId = $translationId;
            $lecture->bookAbbrev = $bookRef->bookId;

            $resultLectures[] = $lecture;
        }
        return $resultLectures;
    }

    private function getHebrewPsalmNum($vulgataNum)
    {
        // see http://en.wikipedia.org/wiki/Psalms#Numbering
        if ($vulgataNum >= 10 && $vulgataNum <= 113
            || $vulgataNum >= 116 && $vulgataNum <= 145
        ) {
            $hebrewNum = $vulgataNum + 1;
        } else {
            if ($vulgataNum == 114 || $vulgataNum == 115) {
                $hebrewNum = 116;
            } else {
                if ($vulgataNum == 146 || $vulgataNum == 147) {
                    $hebrewNum = 146;
                } else {
                    $hebrewNum = $vulgataNum;
                }
            }
        }
        return $hebrewNum;
    }
}
