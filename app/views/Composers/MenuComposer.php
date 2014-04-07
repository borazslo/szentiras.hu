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
        
        foreach ($translations as $translation) {
            $translationTitle = $translation['name']." (".$translation['abbrev'].")";
            $translationUrl = "/${translation['abbrev']}";
            $items[]  = [$translationTitle,$translationUrl];
        }

        $items[] = ["Újszövetség: hangfájlok", "/hang"];
        $items[] = ["További fordítások", "/forditasok"];
        $items[] = 'pause';
        $items[] = ["Keresés a Bibliában", '/kereses'];

        $items[] = \View::make("searchForm")->render();
        $items[] = 'pause';

        $items[] = ["FEJLESZTŐKNEK", "/API"];
        $items[] = ["Újdonságaink", "/info"];

        $items[] = 'pause';
        $items[] = ["Görög újszövetségi honlap","http://www.ujszov.hu/"];
        $items[] = ["Katolikus igenaptár","http://www.katolikus.hu/igenaptar/"];
        $items[] = ["Zsolozsma","http://zsolozsma.katolikus.hu/"];

        
        $view
            ->with('items', $items);
    }
    
    

}
