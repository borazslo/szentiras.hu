<?php
/**

 */

namespace SzentirasHu\views\Composers;


use SzentirasHu\Models\Entities\Translation;

class BookAbbrevListComposer
{
    public function compose($view)
    {
        $translation = array_key_exists('translation', $view->getData()) ?
            $view['translation'] :
            Translation::where('id', \Config::get('settings.defaultTranslationId'))->first();
        \Log::debug("Translation id: ", [$translation]);
        $books = $translation->books()->get();
        $view
            ->with(['books'=>$books, 'translation'=>$translation]);
    }
}