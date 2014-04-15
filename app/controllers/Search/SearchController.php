<?php

namespace SzentirasHu\Controllers\Search;
use BaseController;
use Input;
use SzentirasHu\Models\Entities\Translation;
use View;

/**
 * Controller for searching. Based on REST conventions.
 *
 * @author berti
 */
class SearchController extends BaseController {

    public function getIndex() {
        return $this->getView(new SearchForm());
    }

    public function postSearch() {
        $form = new SearchForm();
        $form->textToSearch = Input::get('textToSearch');
        if (Input::has('translation')) {
            $form->translation = Input::get('translation');
        } else {
            $form->translation = Translation::getDefaultTranslation()->id;
        }
        return $this->getView($form);
    }

    private function getView($form) {
        return View::make("search", [
            'form' => $form,
            'translations' => Translation::orderBy('name')->get()
        ]);
    }

}
