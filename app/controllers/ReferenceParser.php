<?php

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
class ReferenceParser {

    const PARSE_BOOK = 1;
    const PARSE_CHAPTER = 2;
    const PARSE_VERSE = 3;

    private $lexer;
    private $stateStack = [];

    public function __construct($referenceString) {
        $this->lexer = new ReferenceLexer($referenceString);
    }

    public function bookRefs() {
        array_push($this->stateStack, self::PARSE_BOOK);

        $bookRefs = [];

        while ($this->lexer->moveNext()) {
            $token = $this->lexer->lookahead;
            switch (last($this->stateStack)) {
                case self::PARSE_BOOK:
                    $bookId = $this->bookId();
                    $bookRefs[] = new BookRef($bookId);
                    array_push($this->stateStack, self::PARSE_CHAPTER);
                    break;
                case self::PARSE_CHAPTER:
                    // if in this state we get a ';', an other book is coming
                    if ($token['value'] == ';') {
                        array_pop($this->stateStack);
                        break;
                    }
                    $currentBookRef = end($bookRefs);
                    $currentBookRef->chapterRanges = $this->chapterRanges();
            }
        }
        return $bookRefs;
    }

    public function bookId() {
        $bookId = '';
        $token = $this->lexer->lookahead;
        if ($token['type'] === ReferenceLexer::T_NUMERIC) {
            $bookId = $bookId . $token['value'];
            $this->lexer->moveNext();
            $token = $this->lexer->lookahead;
        }
        $bookId = $bookId . $token['value'];
        if ($this->lexer->glimpse()['value'] == '.') {
            $this->lexer->moveNext();
        }
        return $bookId;
    }

    public function chapterRanges() {
        $chapterRanges = [];
        $chapterRanges[] = $this->chapterRange();
        if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_CHAPTER_RANGE_SEPARATOR) {
            $this->lexer->moveNext();
            $this->lexer->moveNext();
            $chapterRanges = array_merge($chapterRanges, $this->chapterRanges());
        }
        return $chapterRanges;
    }

    public function chapterRange() {
        $range = new ChapterRange();
        $range->chapterRef = $this->chapterRef();
        if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_RANGE_OPERATOR) {
            $this->lexer->moveNext();
            $this->lexer->moveNext();
            $range->untilChapterRef = $this->chapterRef();
            // a bit hard to understand, but this is used when the ranges are through chapters, 2,3-3,4
        } else {
            if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_CHAPTER_VERSE_SEPARATOR) {
                $range->untilChapterRef = $this->chapterRef();
            }
        }
        return $range;
    }

    public function chapterRef() {
        $chapterRef = new ChapterRef($this->chapterId());
        if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_CHAPTER_VERSE_SEPARATOR) {
            $this->lexer->moveNext();
            $this->lexer->moveNext();
            $chapterRef->verseRanges = $this->verseRanges();
        }
        return $chapterRef;
    }

    public function chapterId() {
        $token = $this->lexer->lookahead;
        $chapterId = $token['value'];
        if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_TEXT) {
            $this->lexer->moveNext();
            $chapterId .= $this->lexer->lookahead['value'];
        }
        return $chapterId;
    }

    public function verseRanges() {
        $verseRanges = [];
        $verseRanges[] = $this->verseRange();
        if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_VERSE_RANGE_SEPARATOR) {
            $this->lexer->moveNext();
            $this->lexer->moveNext();
            $verseRanges = array_merge($verseRanges, $this->verseRanges());
        }
        return $verseRanges;
    }

    public function verseRange() {
        $range = new VerseRange();
        $range->verseRef = $this->verseRef();
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

    public function verseRef() {
        $ref = new VerseRef($this->verseId());
        if ($this->lexer->glimpse()['type'] == ReferenceLexer::T_TEXT) {
            $this->lexer->moveNext();
            $ref->versePart = $this->lexer->lookahead['value'];
        }

        return $ref;
    }

    public function verseId() {
        $token = $this->lexer->lookahead;
        $verseId = $token['value'];
        return $verseId;
    }

        private function pushState($state) {
        array_push($this->stateStack, $state);
    }
}

class BookRef {
    public $bookId;
    /**
     * @var ChapterRange[]
     */
    public $chapterRanges = [];

    public function __construct($bookId) {
        $this->bookId = $bookId;
    }

    public function toString() {
        $s = $this->bookId;
        if (count($this->chapterRanges) > 0) {
            $s .= ' ';
            $last = end($this->chapterRanges);
            foreach ($this->chapterRanges as $chapterRange) {
                $s .= $chapterRange->toString();
                if ($last !== $chapterRange) {
                    $s .= "|";
                }
            }
        }
        return $s;
    }
}

class ChapterRange {
    /**
     * @var ChapterRef
     */
    public $chapterRef;
    /**
     * @var ChapterRef
     */
    public $untilChapterRef;

    public function toString() {
        $s = $this->chapterRef->toString();
        if ($this->untilChapterRef) {
            $s .= "-{$this->untilChapterRef->toString()}";
        }
        return $s;
    }
}

class ChapterRef {
    public $chapterId;
    /**
     * @var VerseRange[]
     */
    public $verseRanges = [];

    function __construct($chapterId) {
        $this->chapterId = $chapterId;
    }

    public function toString() {
        $s = $this->chapterId;
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

class VerseRange {
    /**
     * @var VerseRef
     */
    public $verseRef;

    /**
     * @var VerseRef
     */
    public $untilVerseRef;

    public function toString() {
        $s = $this->verseRef->toString();
        if ($this->untilVerseRef) {
            $s.="-{$this->untilVerseRef->toString()}";
        }
        return $s;
    }
}

class VerseRef {
    public $verseId;
    public $versePart;

    function __construct($verseId) {
        $this->verseId = $verseId;
    }

    public function toString() {
        return $this->verseId.$this->versePart;
    }

}
