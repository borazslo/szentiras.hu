<?php

/**
 * Description of SearchController
 *
 * @author berti
 */
class SearchController extends BaseController {

    public function index() {
        return View::make("search", array("searchForm" => new SearchForm()));
    }
        
    public function show($textToSearch) {
        $form = new SearchForm();
        $form->textToSearch = $textToSearch;
        return View::make("search", array("searchForm" => $form));
    }
    
    public function store() {
        return $this->show(Input::get('textToSearch'));
    }

    
}
