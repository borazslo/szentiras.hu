<?php

namespace SzentirasHu\Controllers\Home;

use SzentirasHu\Lib\Reference\CanonicalReference;
use SzentirasHu\Models\Entities\Verse;
use SzentirasHu\Models\Repositories\BookRepository;

class LectureSelector
{

    private $date;
    /**
     * @var \SzentirasHu\Models\Repositories\BookRepository
     */
    private $bookRepository;

    public function __construct(BookRepository $bookRepository)
    {
        $this->bookRepository = $bookRepository;
    }

    public function getLectures($date = false)
    {
        $date = $date ? $date : date("Ymd");
        $resultLectures = array();
        $lectureDownloader = \App::make('SzentirasHu\Lib\LectureDownloader', [$date]);
        $referenceString = $lectureDownloader->getReferenceString();
        if (!$referenceString) {
            return $resultLectures;
        }

        $translationId = \Config::get('settings.defaultTranslationId');
        $bookRefs = CanonicalReference::fromString($referenceString)->toTranslated($translationId)->bookRefs;

        foreach ($bookRefs as $bookRef) {
            // extract and convert Psalm numbering
            if (preg_match('/Zs.*/', $bookRef->bookId, $matches)) {
                $vulgataNum = $bookRef->chapterRanges[0]->chapterRef->chapterId;
                $bookRef->chapterRanges[0]->chapterRef->chapterId = $this->getHebrewPsalmNum($vulgataNum);
            }
            $lecture = new Lecture();
            $lecture->ref = $bookRef->toString();
            $lecture->link = str_replace(' ', '', $lecture->ref);
            $lecture->translationId = $translationId;
            $lecture->bookAbbrev = $bookRef->bookId;

            $extLinks = array();

            $book = $this->bookRepository->getByAbbrev($lecture->bookAbbrev);
            $verse = Verse::where('trans', $translationId)->where('book', $book->id)->first();
            if ($verse) {
                $availableTranslatedVerses = Verse::whereIn('tip', array(60, 6, 901))
                    ->where('gepi', $verse->gepi)->get();
                foreach ($availableTranslatedVerses as $verse) {
                    $translation = $verse->translation;
                    $extLink = new ExtLink();
                    $extLink->label = $translation->abbrev;
                    $extLink->title = $extLink->label;
                    $extLink->url = "/{$translation->abbrev}/{$lecture->link}";
                    $extLinks[] = $extLink;
                }
            }


            $lecture->extLinks = $extLinks;
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
