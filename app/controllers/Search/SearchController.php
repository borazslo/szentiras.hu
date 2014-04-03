<?php namespace SzentirasHu\Controller\Search;

/**
 * Controller for searching. Based on REST conventions.
 *
 * @author berti
 */
class SearchController extends \BaseController {

    public function index() {
        return View::make("search", array("searchForm" => new SearchForm()));
    }
        
    public function show($textToSearch) {
        $form = new SearchForm();
        $form->textToSearch = $textToSearch;
        return View::make("search", array("searchForm" => $form, 
            'pageTitle' => 'Szentírás | Keresés: '.$textToSearch));
    }
    
    public function store() {
        return $this->show(Input::get('textToSearch'));
    }
    
}
