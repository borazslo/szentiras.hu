<?php

namespace SzentirasHu\Views\Composers;

use SzentirasHu\Controllers\Search\SearchForm;
use SzentirasHu\Models\Entities\Translation;

/**
 * View composer for the menu. This will lookup and provide data needed for the menu.
 *
 * @author berti
 */
class MenuComposer
{

    public function compose($view)
    {
        $translations = Translation::where('denom', 'katolikus')->orderBy('name')->get();
        $view
            ->with('translations', $translations)
            ->with('searchForm', new SearchForm());
    }

}
