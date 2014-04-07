<?php

namespace SzentirasHu\Controllers\Display;

/**
 *
 * @author berti
 */
class TextDisplayController extends \BaseController {
    
    public function showTranslation($translationAbbrev) {
        $translation = \SzentirasHu\Models\Entities\Translation::where('abbrev', $translationAbbrev)->first();
        return \View::make('translation', ['translation' => $translation]);
    }
    
    public function showReferenceText($reference) {
        
    }
    
    public function showTranslatedReferenceText($translationAbbrev, $reference) {
        
    }
    
}
