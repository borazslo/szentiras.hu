<?php

namespace SzentirasHu\Controllers\Display;
use SzentirasHu\Lib\Reference\CanonicalReference;
use SzentirasHu\Models\Entities\Book;
use SzentirasHu\Models\Entities\Translation;

/**
 *
 * @author berti
 */
class TextDisplayController extends \BaseController {
    
    public function showTranslation($translationAbbrev) {
        $translation = Translation::where('abbrev', $translationAbbrev)->first();
        $books = $translation->books()->orderBy('id')->get();
        return \View::make('textDisplay.translation',
            ['translation' => $translation,
            'books' => $books]);
    }
    
    public function showReferenceText($reference) {
        return $this->showTranslatedReferenceText(\Config::get('settings.defaultTranslationAbbrev'), $reference);
    }
    
    public function showTranslatedReferenceText($translationAbbrev, $reference) {
        $canonicalRef = CanonicalReference::fromString($reference);
        $bookRef = $canonicalRef->bookRefs[0];
        $translation = Translation::where('abbrev', $translationAbbrev)->first();
        $translatedRef = $canonicalRef->toTranslated($translation->id);
        $book = Book::where('abbrev', $translatedRef->bookRefs[0]->bookId)->where('translation_id', $translation->id)->first();
        $firstVerses = $book->verses()->where('trans', $translation->id)->where('numv','1')->where('tip',6)->orderBy('chapter')->get();

        return \View::make('textDisplay.book', [
            'translation'=>$translation,
            'reference'=>$translatedRef,
            'book'=>$book,
            'firstVerses'=>$firstVerses
        ]);
    }
    
}
