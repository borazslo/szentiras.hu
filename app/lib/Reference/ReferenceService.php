<?php
/**

 */

namespace SzentirasHu\Lib\Reference;


use SzentirasHu\Models\Repositories\BookRepository;
use SzentirasHu\Models\Repositories\TranslationRepository;
use SzentirasHu\Models\Repositories\VerseRepository;

class ReferenceService {

    /**
     * @var \SzentirasHu\Models\Repositories\TranslationRepository
     */
    private $translationRepository;
    /**
     * @var \SzentirasHu\Models\Repositories\BookRepository
     */
    private $bookRepository;
    /**
     * @var \SzentirasHu\Models\Repositories\VerseRepository
     */
    private $verseRepository;

    function __construct(TranslationRepository $translationRepository, BookRepository $bookRepository, VerseRepository $verseRepository)
    {
        $this->translationRepository = $translationRepository;
        $this->bookRepository = $bookRepository;
        $this->verseRepository = $verseRepository;
    }

    public function getExistingBookRef(CanonicalReference $ref)
    {
        foreach ($this->translationRepository->getAll() as $translation) {
            $storedBookRef = $this->findStoredBookRef($ref->bookRefs[0], $translation->id);
            if ($storedBookRef) {
                return $storedBookRef;
            }
        }
        return false;
    }

    private function findStoredBookRef($bookRef, $translationId)
    {
        $result = false;
        $abbreviatedBook = $this->bookRepository->getByAbbrev($bookRef->bookId);
        if ($abbreviatedBook) {
            $book = $this->bookRepository->getByNumberForTranslation($abbreviatedBook->number, $translationId);
            if ($book) {
                $result = new BookRef($book->abbrev);
                $result->chapterRanges = $bookRef->chapterRanges;
            } else {
                \Log::debug("Book not found in database: {$bookRef->toString()}");
            }
        }
        return $result;
    }

    /**
     *
     * Takes a bookref and get an other bookref according
     * to the given translation.
     *
     * @return BookRef
     */
    public function translateBookRef(BookRef $bookRef, $translationId)
    {
        $result = $this->findStoredBookRef($bookRef, $translationId);
        return $result ? $result : $bookRef;
    }

    public function translateReference(CanonicalReference $ref, $translationId)
    {
        $bookRefs = array_map(function ($bookRef) use ($translationId) {
            return $this->translateBookRef($bookRef, $translationId);
        }, $ref->bookRefs);
        return new CanonicalReference($bookRefs);
    }


    public function getCanonicalUrl(CanonicalReference $ref, $translationId)
    {
        $translation = $this->translationRepository->getById($translationId);
        $translatedRef = $this->translateReference($ref, $translationId);
        $url = preg_replace('/[ ]+/', '', "{$translation->abbrev}/{$translatedRef->toString()}");
        return $url;
    }

} 