<?php

/**
 * View composer for the menu. This will lookup and provide data needed for the menu.
 *
 * @author berti
 */
class MenuComposer {
    
    public function compose($view) {
        $translations = Translation::where('denom','katolikus')->orderBy('name')->get();
        $view
                ->with('translations',$translations)
                ->with('searchForm', new SearchForm());
    }
    
}
