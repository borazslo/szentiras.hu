<?php

namespace SzentirasHu\Lib\Reference;

use SzentirasHu\Models\Entities\Translation;
use SzentirasHu\Models\Repositories\BookRepository;

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

    public static function fromString($s)
    {
        $ref = new CanonicalReference();
        $parser = new ReferenceParser($s);
        $bookRefs = $parser->bookRefs();
        $ref->bookRefs = $bookRefs;
        return $ref;
    }

    public function getExistingBookRef()
    {
        $translationRepository = \App::make('SzentirasHu\Models\Repositories\TranslationRepository');
        foreach ($translationRepository->getAll() as $translation) {
            $storedBookRef = self::findStoredBookRef($this->bookRefs[0], $translation->id);
            if ($storedBookRef) {
                return $storedBookRef;
            }
        }
        return false;
    }

    private static function findStoredBookRef($bookRef, $translationId)
    {
        $result = false;
        $bookRepository = \App::make('SzentirasHu\Models\Repositories\BookRepository');
        $abbreviatedBook = $bookRepository->getByAbbrev($bookRef->bookId);
        if ($abbreviatedBook) {
            $book = $bookRepository->getByNumberForTranslation($abbreviatedBook->number, $translationId);
            if ($book) {
                $result = new BookRef($book->abbrev);
                $result->chapterRanges = $bookRef->chapterRanges;
            } else {
                \Log::debug("Book not found in database: {$bookRef->toString()}");
            }
        }
        return $result;
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

    public function toTranslated($translationId)
    {
        $bookRefs = array_map(function ($bookRef) use ($translationId) {
            return self::translateBookRef($bookRef, $translationId);
        }, $this->bookRefs);
        return new CanonicalReference($bookRefs);
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
        $result = self::findStoredBookRef($bookRef, $translationId);
        return $result ? $result : $bookRef;
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

    public function getCanonicalUrl($translation)
    {
        $translatedRef = $this->toTranslated($translation->id);
        $url = preg_replace('/[ ]+/', '', "{$translation->abbrev}/{$translatedRef->toString()}");
        return $url;
    }

}