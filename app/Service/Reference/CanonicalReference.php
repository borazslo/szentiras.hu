<?php

namespace SzentirasHu\Service\Reference;

/**
 * Class CanonicalReference to represent a unique reference to some Bible verses.
 * This reference normally is agnostic to translation but to handle collisions, optionally can contain a translation id.
 *
 */
class CanonicalReference
{

    /**
     * @var BookRef[]
     */
    public $bookRefs;

    public $translationId;

    public function __construct($bookRefs = [])
    {
        $this->bookRefs = $bookRefs;
    }

    public static function isValid($referenceString)
    {
        try {
            $ref = self::fromString($referenceString);
        } catch (ParsingException $e) {
            return false;
        }
        return count($ref->bookRefs) > 0;
    }

    public static function fromString(string $s, int $translationId = null)
    {
        $ref = new CanonicalReference();
        $parser = new ReferenceParser($s);
        $bookRefs = $parser->bookRefs();
        $ref->bookRefs = $bookRefs;
        $ref->translationId = $translationId;
        return $ref;
    }

    public static function fromBookChapterVerse($bookAbbrev, $chapterNumber, int $verseNumber) {
        $ref = new CanonicalReference();
        $bookRef = new BookRef($bookAbbrev);
        $verseRef = new VerseRef($verseNumber);
        $verseRange = new VerseRange($verseRef);
        $chapterRef = new ChapterRef($chapterNumber);
        $chapterRef->addVerseRange($verseRange);
        $chapterRange = new ChapterRange($chapterRef);        
        $bookRef->addChapterRange($chapterRange);
        $ref->addBookRef($bookRef);
        return $ref;
    }

    public function toString()
    {
        $s = '';
        $lastBook = end($this->bookRefs);
        foreach ($this->bookRefs as $bookRef) {
            $s .= $bookRef->toString();
            if ($lastBook !== $bookRef) {
                $s .= "; ";
            }
        }
        return $s;
    }

    public function isBookLevel()
    {
        foreach ($this->bookRefs as $bookRef) {
            if (count($bookRef->chapterRanges) > 0) {
                return false;
            }
        }
        return true;
    }

    public function isOneChapter()
    {
        $result = count($this->bookRefs) == 1 &&
            count($this->bookRefs[0]->chapterRanges) == 1 &&
                !$this->bookRefs[0]->chapterRanges[0]->untilChapterRef &&
                count($this->bookRefs[0]->chapterRanges[0]->chapterRef->verseRanges)==0;
        return $result;
    }

    /**
     * @param ChapterRange $chapterRange
     * @return int[]
     */
    public static function collectChapterIds($chapterRange)
    {
        $searchedChapters = [];
        $currentChapter = $chapterRange->chapterRef->chapterId;
        do {
            $searchedChapters[] = $currentChapter;
            $currentChapter++;
        } while ($chapterRange->untilChapterRef && $currentChapter <= $chapterRange->untilChapterRef->chapterId);
        return $searchedChapters;
    }

    public function addBookRef(BookRef $bookRef) {
        $this->bookRefs[] = $bookRef;
    }

}