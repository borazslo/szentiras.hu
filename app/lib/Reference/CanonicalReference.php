<?php

namespace SzentirasHu\Lib\Reference;

use SzentirasHu\Models\Entities\BookAbbrev;

/**
 * Class CanonicalReference to represent a unique reference to some Bible verses.
 * This reference is agnostic of translations, uses the primary
 *
 */
class CanonicalReference
{

    /**
     * @var BookRef[]
     */
    public $bookRefs;

    public function __construct($bookRefs = []) {
        $this->bookRefs = $bookRefs;
    }

    public static function fromString($s)
    {
        $ref = new CanonicalReference();
        $parser = new ReferenceParser($s);
        $bookRefs = $parser->bookRefs();
        $ref->bookRefs = $bookRefs;
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

    /**
     *
     * Takes a bookref and get an other bookref according
     * to the given translation.
     *
     * @return BookRef
     */
    public static function translateBookRef(BookRef $bookRef, $translationId)
    {
        $result = $bookRef;

        $abbrev = BookAbbrev::where('abbrev', $bookRef->bookId)->first();
        if (!$abbrev) {
            \Log::warning("Book abbrev not found in database: {$abbrev}");
        } else {
            $book = $abbrev->books()->where('translation_id', $translationId)->first();
            if ($book) {
                $result = new BookRef($book->abbrev);
                $result->chapterRanges = $bookRef->chapterRanges;
            } else {
                \Log::warning("Book not found in database: abbrev: {$abbrev->abbrev}, book id: {$abbrev->books_id}");
            }
        }
        return $result;
    }

    public function toTranslated($translationId) {
        $bookRefs = array_map(function($bookRef) use ($translationId) {
           return self::translateBookRef($bookRef, $translationId);
        }, $this->bookRefs);
        return new CanonicalReference($bookRefs);
    }

}
