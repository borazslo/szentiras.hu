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

    public function __construct($referenceString)
    {
        $this->lexer = new ReferenceLexer($referenceString);
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
        if ($nextToken['type'] == ReferenceLexer::T_NUMERIC) {
            $this->lexer->moveNext();
            $bookRef->chapterRanges = $this->chapterRanges();
        } else if (
            $nextToken['type'] == ReferenceLexer::T_BOOK_SEPARATOR
        ) {
            $this->lexer->moveNext();
        } else if ($nextToken) {
            $this->parsingError();
        }
        return $bookRef;
    }

    public function bookId()
    {
        $bookId = '';
        $token = $this->lexer->lookahead;
        if ($token['type'] === ReferenceLexer::T_NUMERIC) {
            $bookId = $bookId . $token['value'];
            $this->lexer->moveNext();
            $token = $this->lexer->lookahead;
            if ($token['type'] !== ReferenceLexer::T_TEXT) {
                $this->parsingError();
            }
        }
        $bookId = $bookId . $token['value'];
        if ($this->lexer->glimpse()['value'] == '.' ||
            $this->lexer->glimpse()['value'] == ','
        ) {
            $this->lexer->moveNext();
        }
        return $bookId;
    }

    public function chapterRanges()
    {
        $chapterRanges = [];
        $chapterRanges[] = $this->chapterRange();
        $newRange = false;
        if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_BOOK_SEPARATOR) {
            $this->lexer->peek();
            if ($this->lexer->peek()['type'] == ReferenceLexer::T_NUMERIC) {
                if ($this->lexer->peek()['type'] != ReferenceLexer::T_TEXT)
                $newRange = true;
            }
            $this->lexer->resetPeek();
        }
        if (!$newRange) {
            $newRange = $this->lexer->glimpse()['type'] == ReferenceLexer::T_CHAPTER_RANGE_SEPARATOR
                || $this->lexer->glimpse()['type'] == ReferenceLexer::T_VERSE_RANGE_SEPARATOR;
        }

        if ($newRange) {
            $this->lexer->moveNext();
            $this->lexer->moveNext();
            $chapterRanges = array_merge($chapterRanges, $this->chapterRanges());
        }
        return $chapterRanges;
    }

    public function chapterRange()
    {
        $range = new ChapterRange();
        $range->chapterRef = $this->chapterRef();
        if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_RANGE_OPERATOR) {
            $this->lexer->moveNext();
            $this->lexer->moveNext();
            $range->untilChapterRef = $this->chapterRef();
        } else {
            // a bit hard to understand, but this is used when the ranges are through chapters, 2,3-3,4
            if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_CHAPTER_VERSE_SEPARATOR) {
                $range->untilChapterRef = $this->chapterRef(true);
            }
        }
        return $range;
    }

    public function chapterRef($fromPreviousChapter = false)
    {
        $chapterRef = new ChapterRef($this->chapterId());
        if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_TEXT) {
            $this->lexer->moveNext();
            $chapterRef->chapterPart .= $this->lexer->lookahead['value'];
        }
        if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_CHAPTER_VERSE_SEPARATOR) {
            $this->lexer->moveNext();
            $this->lexer->moveNext();
            $chapterRef->verseRanges = $this->verseRanges($fromPreviousChapter);
        }
        return $chapterRef;
    }

    public function chapterId()
    {
        $token = $this->lexer->lookahead;
        $chapterId = $token['value'];
        return $chapterId;
    }

    public function verseRanges($fromPreviousChapter = false)
    {
        $verseRanges = [];
        $verseRanges[] = $this->verseRange($fromPreviousChapter);
        $nextToken = $this->lexer->peek();
        if ($nextToken['type'] == ReferenceLexer::T_VERSE_RANGE_SEPARATOR
        ) {
            if ($this->lexer->peek()['type'] == ReferenceLexer::T_NUMERIC
                && $this->lexer->peek()['type'] == ReferenceLexer::T_CHAPTER_VERSE_SEPARATOR
            ) {
                $this->lexer->resetPeek();
            } else {
                $this->lexer->resetPeek();
                $this->lexer->moveNext();
                if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_NUMERIC) {
                    $this->lexer->moveNext();
                    $verseRanges = array_merge($verseRanges, $this->verseRanges());
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
            $range->verseRef = $this->verseRef();
        } else {
            $range->untilVerseRef = $this->verseRef();
        }
        if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_RANGE_OPERATOR) {
            $this->lexer->moveNext();
            $this->lexer->moveNext();
            /**
             * This is some specialty: 12,5-13,14 -> range is through chapters
             */
            if ($this->lexer->glimpse()['type'] != ReferenceLexer::T_CHAPTER_VERSE_SEPARATOR) {
                $range->untilVerseRef = $this->verseRef();
            }
        }
        return $range;
    }

    public function verseRef()
    {
        $ref = new VerseRef($this->verseId());
        if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_TEXT) {
            $this->lexer->moveNext();
            $ref->versePart = $this->lexer->lookahead['value'];
        }

        return $ref;
    }

    public function verseId()
    {
        $token = $this->lexer->lookahead;
        if ($token['type'] != ReferenceLexer::T_NUMERIC) {
            $this->parsingError();
        }
        $verseId = $token['value'];
        return $verseId;
    }

    private function pushState($state)
    {
        array_push($this->stateStack, $state);
    }

    private function parsingError()
    {
        throw new ParsingException($this->lexer->lookahead['position']);
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