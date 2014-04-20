<?php

namespace SzentirasHu\Views\Composers;

use SzentirasHu\Models\Entities\Translation;
use SzentirasHu\Models\Repositories\TranslationRepository;

/**
 * View composer for the menu. This will lookup and provide data needed for the menu.
 *
 * @author berti
 */
class MenuComposer
{


    /**
     * @var \SzentirasHu\Models\Repositories\TranslationRepository
     */
    private $translationRepository;

    function __construct(TranslationRepository $translationRepository)
    {
        $this->translationRepository = $translationRepository;
    }

    public function compose($view)
    {
        $translations = $this->translationRepository->getByDenom('katolikus');
        
        foreach ($translations as $translation) {
            $translationTitle = $translation['name']." (".$translation['abbrev'].")";
            $translationUrl = "/${translation['abbrev']}";
            $items[]  = [$translationTitle,$translationUrl];
        }

        $items[] = ["Újszövetség: hangfájlok", "/hang"];
        $items[] = ["További fordítások", "/forditasok"];
        $items[] = 'pause';
        $items[] = ["Keresés a Bibliában", '/kereses'];

        $items[] = \View::make("search.searchForm")->render();
        $items[] = 'pause';

        $items[] = ["Fejlesztőknek", "/api"];
        $items[] = ["Újdonságaink", "/info"];

        $items[] = 'pause';
        $items[] = ["Görög újszövetségi honlap","http://www.ujszov.hu/"];
        $items[] = ["Katolikus igenaptár","http://www.katolikus.hu/igenaptar/"];
        $items[] = ["Zsolozsma","http://zsolozsma.katolikus.hu/"];

        
        $view
            ->with('items', $items);
    }
    
    

}
