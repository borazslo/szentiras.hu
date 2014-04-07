<?php
/**

 */

namespace SzentirasHu\views\Composers;


use SzentirasHu\Models\Entities\Translation;

class BookAbbrevListComposer
{
    public function compose($view)
    {
        $translationId = array_key_exists('translation', $view->getData()) ? $view['translation']->id : \Config::get('settings.defaultTranslationId');
        \Log::debug("Translation id: ", [$translationId]);
        $books = Translation::where('id', $translationId)->first()->books()->get();
        $view
            ->with('books', $books);
    }
}