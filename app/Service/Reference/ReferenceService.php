<?php
/**

 */

namespace SzentirasHu\Service\Reference;


use Log;
use SzentirasHu\Data\Repository\BookRepository;
use SzentirasHu\Data\Repository\TranslationRepository;
use SzentirasHu\Data\Repository\VerseRepository;

class ReferenceService
{

    /**
     * @var \SzentirasHu\Data\Repository\TranslationRepository
     */
    private $translationRepository;
    /**
     * @var \SzentirasHu\Data\Repository\BookRepository
     */
    private $bookRepository;
    /**
     * @var \SzentirasHu\Data\Repository\VerseRepository
     */
    private $verseRepository;

    function __construct(TranslationRepository $translationRepository, BookRepository $bookRepository, VerseRepository $verseRepository)
    {
        $this->translationRepository = $translationRepository;
        $this->bookRepository = $bookRepository;
        $this->verseRepository = $verseRepository;
    }

    public function getExistingBookRef(CanonicalReference $ref, $translationId = false)
    {
        if ($translationId) {
            $translations = [ $this->translationRepository->getById($translationId) ];
        } else {
            $translations = $this->translationRepository->getAll();
        }
        Log::debug("Read {$translations} as translations");
        foreach ($translations as $translation) {
            $storedBookRef = $this->findStoredBookRef($ref->bookRefs[0], $translation->id);
            if ($storedBookRef) {
                return $storedBookRef;
            }
        }
        return false;
    }

    private function findStoredBookRef($bookRef, $translationId, $refTranslationId = null)
    {
        $result = false;
        $abbreviatedBook = $this->bookRepository->getByAbbrev($bookRef->bookId, $refTranslationId);
        if ($abbreviatedBook) {
            $book = $this->bookRepository->getByNumberForTranslation($abbreviatedBook->number, $translationId);
            if ($book) {
                $result = new BookRef($book->abbrev);
                $result->chapterRanges = $bookRef->chapterRanges;
            } else {
                Log::debug("Book not found in database: {$bookRef->toString()}");
            }
        } else {
            Log::debug("Book not found for abbrev {$bookRef->bookId} in translation {$refTranslationId}");
        }
        return $result;
    }

    /**
     *
     * Takes a bookref and get an other bookref according
     * to the given translation.
     *
     * @param refTranslationId The id of the translation the bookref is interpreted according to.
     * @return BookRef
     */
    public function translateBookRef(BookRef $bookRef, $translationId, $refTranslationId = null)
    {
        $result = $this->findStoredBookRef($bookRef, $translationId, $refTranslationId);
        return $result ? $result : $bookRef;
    }

    public function translateReference(CanonicalReference $ref, $translationId)
    {
        $bookRefs = array_map(function ($bookRef) use ($translationId, $ref) {
            return $this->translateBookRef($bookRef, $translationId, $ref->translationId);
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

    public function getBook($canonicalReference, $translationId)
    {
        $bookRef = $canonicalReference->bookRefs[0];
        return $this->bookRepository->getByAbbrevForTranslation($bookRef->bookId, $translationId);
    }

    public function getChapterRange($book)
    {
        $bookVerses = $this->verseRepository->getVerses($book->id);
        $fromChapter = $bookVerses->first()->chapter;
        $fromNumv = $bookVerses->first()->numv;
        $toChapter = $bookVerses->last()->chapter;
        $toNumv = $bookVerses->last()->numv;
        return [$fromChapter, $fromNumv, $toChapter, $toNumv];
    }

    public function getPrevNextChapter($canonicalReference, $translationId)
    {
        $book = $this->getBook($canonicalReference, $translationId);
        list($fromChapter, $fromNumv, $toChapter, $toNumv) = $this->getChapterRange($book);
        $bookRef = $canonicalReference->bookRefs[0];
        $chapterId = $bookRef->chapterRanges[0]->chapterRef->chapterId;
        $prevChapter = false;
        $nextChapter = false;
        if ($chapterId > $fromChapter) {
            $prevChapter = $chapterId - 1;
        }
        if ($chapterId < $toChapter) {
            $nextChapter = $chapterId + 1;
        }
        $prevRef = $prevChapter ?
            CanonicalReference::fromString("{$bookRef->bookId} {$prevChapter}") :
            false;
        $nextRef = $nextChapter ?
            CanonicalReference::fromString("{$bookRef->bookId} {$nextChapter}") :
            false;

        return [$prevRef, $nextRef];
    }

}