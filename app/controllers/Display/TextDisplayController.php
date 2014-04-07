<?php

namespace SzentirasHu\Controllers\Display;
use SzentirasHu\Models\Entities\Translation;

/**
 *
 * @author berti
 */
class TextDisplayController extends \BaseController {
    
    public function showTranslation($translationAbbrev) {
        $translation = Translation::where('abbrev', $translationAbbrev)->first();
        $books = $translation->books()->orderBy('id')->get();
        return \View::make('translation',
            ['translation' => $translation,
            'books' => $books]);
    }
    
    public function showReferenceText($reference) {
        
    }
    
    public function showTranslatedReferenceText($translationAbbrev, $reference) {
        
    }
    
}
