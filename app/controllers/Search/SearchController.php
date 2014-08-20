<?php

namespace SzentirasHu\Controllers\Search;

use App;
use BaseController;
use Input;
use Response;
use SzentirasHu\Lib\Reference\CanonicalReference;
use SzentirasHu\Lib\Reference\ParsingException;
use SzentirasHu\Lib\Reference\ReferenceService;
use SzentirasHu\Lib\Search\FullTextSearchParams;
use SzentirasHu\Lib\Search\FullTextSearchResult;
use SzentirasHu\Lib\Search\SearchService;
use SzentirasHu\Lib\Text\TextService;
use SzentirasHu\Lib\VerseContainer;
use SzentirasHu\Models\Repositories\BookRepository;
use SzentirasHu\Models\Repositories\TranslationRepository;
use SzentirasHu\Models\Repositories\VerseRepository;
use View;

/**
 * Controller for searching. Based on REST conventions.
 *
 * @author berti
 */
class SearchController extends BaseController
{

    /**
     * @var BookRepository
     */
    private $bookRepository;

    /**
     * @var TranslationRepository
     */
    private $translationRepository;
    /**
     * @var \SzentirasHu\Models\Repositories\VerseRepository
     */
    private $verseRepository;
    /**
     * @var \SzentirasHu\Lib\Reference\ReferenceService
     */
    private $referenceService;
    /**
     * @var \SzentirasHu\Lib\Text\TextService
     */
    private $textService;
    /**
     * @var \SzentirasHu\Lib\Search\SearchService
     */
    private $searchService;

    function __construct(BookRepository $bookRepository, TranslationRepository $translationRepository, VerseRepository $verseRepository, ReferenceService $referenceService, TextService $textService, SearchService $searchService)
    {
        $this->bookRepository = $bookRepository;
        $this->translationRepository = $translationRepository;
        $this->verseRepository = $verseRepository;
        $this->referenceService = $referenceService;
        $this->textService = $textService;
        $this->searchService = $searchService;
    }

    public function getIndex()
    {
        return $this->getView($this->prepareForm());
    }

    public function anySuggest()
    {
        $result = [];
        $term = Input::get('term');
        $ref = $this->findTranslatedRef($term);
        if ($ref) {
            $result[] = [
                'cat' => 'ref',
                'label' => $ref->toString(),
                'link' => "/{$ref->toString()}"
            ];
        }
        $suggestions = $this->searchService->getSuggestionsFor($term);
        if (is_array($suggestions)) {
            $result = array_merge($result, $suggestions);
        }
        return Response::json($result);
    }

    public function anySearch()
    {
        if (Input::get('textToSearch') == null) {
            return $this->getIndex();
        }
        $form = $this->prepareForm();
        $view = $this->getView($form);
        $view = $this->searchBookRef($form, $view);
        $view = $this->searchFullText($form, $view);
        return $view;
    }

    /**
     * @return SearchForm
     */
    private function prepareForm()
    {
        $form = new SearchForm();
        $form->textToSearch = Input::get('textToSearch');
        $form->grouping = Input::get('grouping');
        $form->book = Input::get('book');
        if (Input::get('translation') > 0) {
            $form->translation = $this->translationRepository->getById(Input::get('translation'));
        }
        return $form;
    }

    private function getView($form)
    {
        $translations = $this->translationRepository->getAll();
        $books = $this->bookRepository->getBooksByTranslation($this->translationRepository->getDefault()->id);
        return View::make("search.search", [
            'form' => $form,
            'translations' => $translations,
            'books' => $books
        ]);
    }

    /**
     * @param $form
     * @param $view
     * @return mixed
     */
    private function searchBookRef($form, $view)
    {
        $augmentedView = $view;
        $translatedRef = $this->findTranslatedRef($form->textToSearch, $form->translation);
        if ($translatedRef) {
            $translation = $form->translation ? $form->translation : $this->translationRepository->getDefault();
            $verseContainers = $this->textService->getTranslatedVerses(CanonicalReference::fromString($form->textToSearch), $translation->id);
            if ($verseContainers) {
                $augmentedView = $view->with('bookRef', [
                    'label' => $translatedRef->toString(),
                    'link' => "/{$translation->abbrev}/{$translatedRef->toString()}",
                    'verseContainers' => $verseContainers
                ]);
            }
        }
        return $augmentedView;
    }

    /**
     * @param SearchForm $form
     * @param $view
     * @return mixed
     */
    private function searchFullText($form, $view)
    {
        $searchParams = $this->createFullTextSearchParams($form);
        $view = $this->addTranslationHits($view, $searchParams);
        $results = $this->searchService->getDetailedResults($searchParams);
        if ($results) {
            $view = $view->with('fullTextResults', $results);
        }
        return $view;
    }

    private function extractBookNumbers($form)
    {
        $bookIds = [];
        if ($form->book) {
            if ($form->book == 'old_testament') {
                $bookIds = range(101, 146);
            } else if ($form->book == 'new_testament') {
                $bookIds = range(201, 227);
            } else if ($form->book == 'all') {
                $bookIds = [];
            } else {
                $bookIds = [$form->book];
            }
        }
        return $bookIds;
    }

    /**
     * @param $view
     * @param $searchParams
     * @return mixed
     */
    private function addTranslationHits($view, $searchParams)
    {
        $translationHits = [];
        foreach ($this->translationRepository->getAll() as $translation) {
            $params = clone $searchParams;
            $params->translationId = $translation->id;
            $searchHits = $this->searchService->getSimpleResults($params);
            if ($searchHits) {
                $translationHits[] = ['translation' => $translation, 'hitCount' => $searchHits->hitCount];
            }
        }
        $view = $view->with('translationHits', $translationHits);
        return $view;
    }

    /**
     * @param $form
     * @return FullTextSearchParams
     */
    private function createFullTextSearchParams($form)
    {
        $searchParams = new FullTextSearchParams;
        $searchParams->text = $form->textToSearch;
        if ($form->translation) {
            $searchParams->translationId = $form->translation->id;
        }
        $searchParams->bookNumbers = $this->extractBookNumbers($form);
        $searchParams->synonyms = true;
        return $searchParams;
    }

    /**
     * @param $refToSearch
     * @param $translation
     */
    private function findTranslatedRef($refToSearch, $translation = null)
    {
        try {
            $ref = CanonicalReference::fromString($refToSearch);
            $storedBookRef = $this->referenceService->getExistingBookRef($ref);
            if ($storedBookRef) {
                $translation = $translation ? $translation : $this->translationRepository->getDefault();
                return $this->referenceService->translateBookRef($storedBookRef, $translation->id);
            }
        } catch (ParsingException $e) {
        }
    }

}
