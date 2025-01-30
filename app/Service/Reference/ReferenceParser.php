<?php

namespace SzentirasHu\Service\Reference;

/**
 * Examples of possible formats:
 * - 1Kor - full book
 * - 1Kor 13 - a full chapter
 * - 1Kor 13,1 - a verse
 * - 1Kor 13,1-10 - a verse range
 * - 1Kor 13,2-14,3 - crosschapter ranges
 * - 1Kor 13,1.2-10.24-30 - multiple ranges
 * - 1Kor 13,1a-3.5b-14 - multiple ranges with verse parts
 * - 1Kor 13;25 - multiple chapters
 * - 1Kor 13,1a-3.5b-14|14,23.24-26 - multiple chapters
 * - 1Kor; Jn; 2Fil - multiple books
 * - 1Kor 13,1a-3.5b-14.6a|14,23-15,22; Jn 14,22-39 - everything combined
 * So generally we have the following grammar here:
 * CanonicalReference = BookReference ("; " BookReference)*
 * BookReference = BookId (" "? ChapterReference)
 * ChapterReference = ChapterId ("," VerseReference)? (";" ChapterReference)*
 * VerseReference = VerseRange ("." VerseRange)*
 * VerseRange = VerseId ("-" VerseId)?
 * VerseId = [0-9]+[a-z]?
 * BookId = [0-9]? Alpha+ '.'?
 * ChapterId = [0-9]+
 *
 * So as an example:
 * - 1Kor 13,1a-3.5b-14.6a|14,23; Jn 14,22-39
 * will become an object tree, which is at the root is an array of BookRef objects.
 */
class ReferenceParser
{

    private $lexer;
    private $input;

    public function __construct($referenceString)
    {
        $this->lexer = new ReferenceLexer($referenceString);
        $this->input = $referenceString;
        ////print("Parsing: $referenceString\n");
    }

    public function bookRefs()
    {
        $bookRefs = [];
        while ($this->lexer->moveNext()) {
            $token = $this->lexer->lookahead;
            if ($token['type'] == ReferenceLexer::T_NUMERIC
                || $token['type'] == ReferenceLexer::T_TEXT
            ) {
                $bookRefs[] = $this->bookRef();
            }
        }
        return $bookRefs;
    }

    public function bookRef()
    {
        $bookRef = new BookRef($this->bookId());
        $bookRefs[] = $bookRef;
        $nextToken = $this->lexer->glimpse();
        if (null !== $nextToken) { // something is coming, either a new book or something from the book
            if ($nextToken['type'] == ReferenceLexer::T_NUMERIC) { // something from the book
                $this->lexer->moveNext();
                $bookRef->chapterRanges = $this->chapterRanges();
            } else if ($nextToken['type'] == ReferenceLexer::T_BOOK_SEPARATOR) { // an other book or an other chapter range.
                $this->lexer->moveNext(); // skip the separator
            } else if ($nextToken) { // something else, which is an error
                $this->parsingError();
            }
        }
        return $bookRef;
    }

    public function bookId()
    {
        $bookId = '';
        $token = $this->lexer->lookahead;
        if ($token['type'] === ReferenceLexer::T_NUMERIC) { // e.g. the 1 from 1Kor
            $bookId = $bookId . $token['value']; // add the number to the beginning of the bookId
            $this->lexer->moveNext();
            $token = $this->lexer->lookahead;
        }        
        if ($token == null || $token['type'] !== ReferenceLexer::T_TEXT) { // if not text coming, that's an error
            $this->parsingError();
        }
        $bookId = $bookId . $token['value'];
        if (null !== $this->lexer->glimpse() && 
            ($this->lexer->glimpse()['value'] == '.' || $this->lexer->glimpse()['value'] == ',')) { // The book is written as 2Kor. as abbreviation
            $this->lexer->moveNext();
        }
        //print_r("Book ID: $bookId\n");
        return $bookId;
    }

    public function chapterRanges()
    {
        $chapterRanges = [];
        $chapterRanges[] = $this->chapterRange(); // we read something like 6,2-5
        $newRange = false;
        if ($this->lexer->glimpse() != null) {
            if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_BOOK_SEPARATOR) { // we read a chapter range, but can have a new one separated with a ;
                $this->lexer->peek(); // skip the separator
                $newPeek = $this->lexer->peek();
                if ($newPeek !== null && $newPeek['type'] == ReferenceLexer::T_NUMERIC) { // a number is coming, eg 2
                    $textPeek = $this->lexer->peek(); 
                    if ($textPeek !== null && $textPeek['type'] != ReferenceLexer::T_TEXT) {
                        $newRange = true; // a new Book Id is coming, like 1Kor after the book separator
                    } else if ($textPeek == null) {
                        // just a number alone, that will be a new chapter range,
                        $newRange = true;
                    }
                }
                $this->lexer->resetPeek();
            }

            if (!$newRange) { // not a new book
                $newRange = $this->lexer->glimpse()['type'] == ReferenceLexer::T_CHAPTER_RANGE_SEPARATOR // a chapter range separator
                    || $this->lexer->glimpse()['type'] == ReferenceLexer::T_VERSE_RANGE_SEPARATOR; // or a verse range separator coming, so we need to handle that
            }
        }

        if ($newRange) {
            $this->lexer->moveNext(); // skip the separator
            $this->lexer->moveNext(); // move to the next thing
            $chapterRanges = array_merge($chapterRanges, $this->chapterRanges()); // read the new range
        }
        return $chapterRanges;
    }

    public function chapterRange()
    {
        $range = new ChapterRange();
        $range->chapterRef = $this->chapterRef();
        $operator = $this->lexer->glimpse();
        if ($operator !== null) {
            if ($operator['type'] == ReferenceLexer::T_RANGE_OPERATOR) {
                $this->lexer->moveNext();
                $this->lexer->moveNext();
                $range->untilChapterRef = $this->chapterRef();
            } else {
                // a bit hard to understand, but this is used when the ranges are through chapters, 2,3-3,4
                if ($operator['type'] == ReferenceLexer::T_CHAPTER_VERSE_SEPARATOR) {
                    $range->untilChapterRef = $this->chapterRef(true);
                }
            }
        }
        //print("Range chapterId: {$range->chapterRef->chapterId}\n");
        return $range;
    }

    public function chapterRef($fromPreviousChapter = false)
    {
        $chapterRef = new ChapterRef($this->chapterId());
        if ($this->lexer->glimpse() !== null && $this->lexer->glimpse()['type'] == ReferenceLexer::T_TEXT) { // a text is coming, like 3a, accept it
            $this->lexer->moveNext();
            $chapterRef->chapterPart .= $this->lexer->lookahead['value'];
        }
        if ($this->lexer->glimpse() !== null && $this->lexer->glimpse()['type'] == ReferenceLexer::T_CHAPTER_VERSE_SEPARATOR) { // a separator is coming like ,
            $this->lexer->moveNext(); // skip the separator
            $this->lexer->moveNext(); // get to the number or numbers 
            $chapterRef->verseRanges = $this->verseRanges($fromPreviousChapter);
        }
        return $chapterRef;
    }

    public function chapterId()
    {        
        $token = $this->lexer->lookahead;
        $chapterId = $token['value'];
        //print("Chapter id: $chapterId\n");
        return $chapterId;
    }

    public function verseRanges($fromPreviousChapter = false)
    {
        $verseRanges = [];
        $verseRanges[] = $this->verseRange($fromPreviousChapter);
        $nextToken = $this->lexer->peek();
        if ($nextToken !== null) {
            if ($nextToken['type'] == ReferenceLexer::T_VERSE_RANGE_SEPARATOR) 
            {
                $numericPeek = $this->lexer->peek();
                $verseSeparatorPeek = $this->lexer->peek();
                if ($numericPeek !== null && $verseSeparatorPeek !== null
                    && $numericPeek['type'] == ReferenceLexer::T_NUMERIC
                    && $verseSeparatorPeek['type'] == ReferenceLexer::T_CHAPTER_VERSE_SEPARATOR
                ) {
                    $this->lexer->resetPeek();
                } else {
                    $this->lexer->resetPeek();
                    $this->lexer->moveNext();                    
                    if ($this->lexer->glimpse() !== null && $this->lexer->glimpse()['type'] == ReferenceLexer::T_NUMERIC) {
                        $this->lexer->moveNext();
                        $verseRanges = array_merge($verseRanges, $this->verseRanges());
                    }
                }
            }
        }
        $this->lexer->resetPeek();
        return $verseRanges;
    }

    public function verseRange($fromPreviousChapter = false)
    {
        $range = new VerseRange();
        if (!$fromPreviousChapter) {
            $range->verseRef = $this->verseRef(); // if not from a previous chapter, just parse the verse number (like 3 or 3a)
        } else {
            $range->untilVerseRef = $this->verseRef(); 
        }
        $operator = $this->lexer->glimpse(); // let's see if we have actually a range or not.
        if ($operator !== null && $operator['type'] == ReferenceLexer::T_RANGE_OPERATOR) { // yes, that's a range
            $this->lexer->moveNext(); // skip the operator
            $this->lexer->moveNext(); // move to the number itself.
            /**
             * This is some specialty: 12,5-13,14 -> range is through chapters
             */
            $nextOperator = $this->lexer->glimpse(); // let's see, what comes after the number
            if ($nextOperator == null ||
                $nextOperator['type'] != ReferenceLexer::T_CHAPTER_VERSE_SEPARATOR) { // we are either done (end of string) or not chapter-verse separator, so can save the value
                $range->untilVerseRef = $this->verseRef();
            }
        }
        return $range;
    }

    public function verseRef()
    {
        $ref = new VerseRef($this->verseId());
        if ($this->lexer->glimpse() !== null) { 
            if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_TEXT) { // it can be again a text like 2a
                $this->lexer->moveNext(); 
                $ref->versePart = $this->lexer->lookahead['value']; // read it and store it
            }
        }
        return $ref;
    }

    public function verseId()
    {
        $token = $this->lexer->lookahead;
        if ($token['type'] != ReferenceLexer::T_NUMERIC) { // the verse id is not a number, that's an error
            $this->parsingError();
        }
        $verseId = $token['value'];
        //print("Verse id: $verseId\n");
        return $verseId;
    }

    private function pushState($state)
    {
        array_push($this->stateStack, $state);
    }

    private function parsingError()
    {
        $lookahead = $this->lexer->lookahead;
        $position = 0;
        if ($lookahead !== null) {
            $position = $lookahead['position'];
        }
        throw new ParsingException($position, $this->input);
    }
}

class BookRef
{
    public $bookId;
    /**
     * @var ChapterRange[]
     */
    public $chapterRanges = [];

    public function __construct($bookId)
    {
        $this->bookId = $bookId;
    }

    public function toString()
    {
        $s = $this->bookId;
        if (count($this->chapterRanges) > 0) {
            $s .= ' ';
            $last = end($this->chapterRanges);
            foreach ($this->chapterRanges as $chapterRange) {
                $s .= $chapterRange->toString();
                if ($last !== $chapterRange) {
                    $s .= ";";
                }
            }
        }
        return $s;
    }
}

class ChapterRange
{
    /**
     * @var ChapterRef
     */
    public $chapterRef;
    /**
     * @var ChapterRef
     */
    public $untilChapterRef;

    public function toString()
    {
        $s = $this->chapterRef->toString();
        if ($this->untilChapterRef) {
            $s .= "-{$this->untilChapterRef->toString()}";
        }
        return $s;
    }

    public function hasVerse($chapter, $verse)
    {
        // inside range (regardless of verse)
        if ($chapter > $this->chapterRef->chapterId && $this->untilChapterRef && $chapter < $this->untilChapterRef->chapterId) {
            return true;
        }
        // outside range (regardless of verse)
        if ($chapter < $this->chapterRef->chapterId
            || $chapter > $this->chapterRef->chapterId && !$this->untilChapterRef
            || $this->untilChapterRef && $chapter > $this->untilChapterRef->chapterId
        ) {
            return false;
        }
        // if no verses, all verses are good.
        if (count($this->chapterRef->verseRanges) === 0) {
            return true;
        }
        // can be inside range depending on verse
        if ($chapter == $this->chapterRef->chapterId) {
            foreach ($this->chapterRef->verseRanges as $verseRange) {
                if ($verseRange->contains($verse)) {
                    return true;
                }
            }
        }

        if ($this->untilChapterRef) {
            if ($chapter == $this->chapterRef->chapterId) {
                if ($verse >= last($this->chapterRef->verseRanges)->verseRef->verseId) {
                    return true;
                }
            } else if ($chapter == $this->untilChapterRef->chapterId) {
                if ($verse <= head($this->untilChapterRef->verseRanges)->untilVerseRef->verseId) {
                    return true;
                }
            } else if ($chapter > $this->chapterRef->chapterId && $chapter < $this->untilChapterRef->chapterId) {
                return true;
            }
        }
        return false;
    }
}

class ChapterRef
{
    public $chapterId;
    public $chapterPart;

    /**
     * @var VerseRange[]
     */
    public $verseRanges = [];

    function __construct($chapterId)
    {
        $this->chapterId = $chapterId;
    }

    public function toString()
    {
        $s = $this->chapterId;
        if ($this->chapterPart) {
            $s .= $this->chapterPart;
        }
        if (count($this->verseRanges) > 0) {
            $s .= ',';
            $last = end($this->verseRanges);
            foreach ($this->verseRanges as $verseRange) {
                $s .= $verseRange->toString();
                if ($last !== $verseRange) {
                    $s .= '.';
                }
            }
        }
        return $s;
    }

}

class VerseRange
{
    /**
     * @var VerseRef
     */
    public $verseRef;

    /**
     * @var VerseRef
     */
    public $untilVerseRef;

    public function toString()
    {
        $s="";
        if ($this->verseRef) {
            $s .= $this->verseRef->toString();
        }
        if ($this->untilVerseRef) {
            if ($this->verseRef) {
                $s .= "-";
            }
            $s .= $this->untilVerseRef->toString();
        }
        return $s;
    }

    public function contains($verse)
    {
        $exact = false;
        if ($this->verseRef) {
            $inside = $verse >= $this->verseRef->verseId && $this->untilVerseRef && $verse <= $this->untilVerseRef->verseId;
            $exact = $verse === $this->verseRef->verseId;
        } else {
            $inside = $this->untilVerseRef && $verse <= $this->untilVerseRef->verseId;
        }
        return $inside || $exact;
    }
}

class VerseRef
{
    public $verseId;
    public $versePart;

    function __construct($verseId)
    {
        $this->verseId = $verseId;
    }

    public function toString()
    {
        return $this->verseId . $this->versePart;
    }
}