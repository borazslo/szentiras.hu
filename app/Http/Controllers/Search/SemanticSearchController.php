<?php

namespace SzentirasHu\Http\Controllers\Search;

use Illuminate\Http\Request;
use SzentirasHu\Data\Entity\EmbeddedExcerptScope;
use SzentirasHu\Http\Controllers\Controller;
use SzentirasHu\Http\Controllers\Search\SearchForm;
use SzentirasHu\Http\Controllers\Search\SemanticSearchForm;
use SzentirasHu\Service\Search\SemanticSearchParams;
use SzentirasHu\Service\Search\SemanticSearchService;
use SzentirasHu\Service\Text\BookService;
use SzentirasHu\Service\Text\TranslationService;
use View;

class SemanticSearchController extends Controller
{
    
    public function __construct(
        protected SemanticSearchService $semanticSearchService, 
        protected TranslationService $translationService, 
        protected BookService $bookService)
    {

    }

    public function anySearch(Request $request)
    {
        $textToSearch = $request->get('textToSearch');
        if (empty($textToSearch)) {
            return $this->getIndex($request);
        }
        $form = $this->prepareForm($request, $textToSearch);
        $view = $this->getView($form);
        $view = $this->semanticSearch($form, $view);
        return $view;
    }

    public function getIndex(Request $request)
    {
        return $this->getView($this->prepareForm($request));
    }

    private function getView($form)
    {
        $translations = $this->translationService->getAllTranslations();
        $books = $this->bookService->getBooksForTranslation($this->translationService->getDefaultTranslation());
        return View::make("search.semanticSearch", [
            'form' => $form,
            'translations' => $translations,
            'books' => $books,
        ]);
    }


    private function prepareForm($request) : SemanticSearchForm
    {
        $form = new SemanticSearchForm();
        $form->textToSearch = $request->get('textToSearch');
        $form->bookNumber =  $request->get('bookNumber');
        if ($request->get('translationId') > 0) {
            $form->translationId = $request->get('translationId');
        }

        return $form;
    }

    private function semanticSearch(SemanticSearchForm $form, $view)
    {
        $semanticSearchParams = new SemanticSearchParams();
        $semanticSearchParams->text = $form->textToSearch;
        $semanticSearchParams->translationAbbrev = $form->translationId;
        $semanticSearchParams->usxCodes = SearchController::extractBookNumbers($form->bookNumber);
        $aiResult = $this->semanticSearchService->generateVector($form->textToSearch);
        $response = $this->semanticSearchService->findNeighbors($semanticSearchParams, $aiResult->vector);
        $chapterResponse = $this->semanticSearchService->findNeighbors($semanticSearchParams, $aiResult->vector, EmbeddedExcerptScope::Chapter);
        $rangeResponse = $this->semanticSearchService->findNeighbors($semanticSearchParams, $aiResult->vector, EmbeddedExcerptScope::Range);
        $view = $view->with('response', $response);
        $view = $view->with('chapterResponse', $chapterResponse);
        $view = $view->with('rangeResponse', $rangeResponse);

        return $view;
    }


}
