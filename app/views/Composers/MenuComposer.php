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
        $translations = $this->translationRepository->getAllOrderedByDenom();
        foreach ($translations as $translation) {
            $translationTitle = $translation['name']." (".$translation['abbrev'].")";
            $translationUrl = "/${translation['abbrev']}";
            $navTranslations[]  = [$translationTitle,$translationUrl];
        }

        $navItems[] = ["További fordítások", "/forditasok"];
        $navItems[] = ["Újszövetség: hangfájlok", "/hang"];
        $menuItems[] = 'pause';
        $menuItems[] = ["Keresés a Bibliában"];
        $menuItems[] = \View::make("search.searchForm")->render();
        $menuItems[] = 'pause';

        $menuItems[] = ["Fejlesztőknek", "/api"];
        $menuItems[] = ["Újdonságaink", "/info"];

        $menuItems[] = 'pause';
        $menuItems[] = ["Görög újszövetségi honlap","http://www.ujszov.hu/"];

        
        $view
            ->with('menuItems', $menuItems)
            ->with('navTranslations', $navTranslations)
        ;
    }

}
