<?php

namespace SzentirasHu\Http\Controllers\Search;

use Illuminate\Http\Request;
use SzentirasHu\Http\Controllers\Controller;
use SzentirasHu\Http\Controllers\Search\SearchForm;
use SzentirasHu\Http\Controllers\Search\SemanticSearchForm;
use SzentirasHu\Service\Search\SemanticSearchService;
use View;

class SemanticSearchController extends Controller
{
    
    public function __construct(protected SemanticSearchService $semanticSearchService) {

    }

    public function anySearch(Request $request)
    {
        $textToSearch = $request->get('textToSearch');
        if (empty($textToSearch)) {
            return $this->getIndex();
        }
        $form = $this->prepareForm($textToSearch);
        $view = $this->getView($form);
        $view = $this->semanticSearch($form, $view);
        return $view;
    }

    public function getIndex()
    {
        return $this->getView($this->prepareForm());
    }

    private function getView($form)
    {
        return View::make("search.semanticSearch", [
            'form' => $form
        ]);
    }


    private function prepareForm($textToSearch = null) : SemanticSearchForm
    {
        $form = new SemanticSearchForm();
        $form->textToSearch = $textToSearch;
        return $form;
    }

    private function semanticSearch(SemanticSearchForm $form, $view)
    {
        $results = $this->semanticSearchService->findNeighbors($form->textToSearch);
        if (!empty($results)) {
            $view = $view->with('response', $results);
        }
        return $view;
    }


}
