<?php

namespace SzentirasHu\Controllers\Search;
use BaseController;
use Input;
use Sphinx\SphinxClient;
use SphinxSearch;
use SzentirasHu\Controllers\Display\TextDisplayController;
use SzentirasHu\Lib\Reference\CanonicalReference;
use SzentirasHu\Lib\Reference\ParsingException;
use SzentirasHu\Models\Entities\Book;
use SzentirasHu\Models\Entities\Translation;
use SzentirasHu\Models\Repositories\BookRepository;
use SzentirasHu\Models\Repositories\TranslationRepository;
use View;

/**
 * Controller for searching. Based on REST conventions.
 *
 * @author berti
 */
class SearchController extends BaseController {

    /**
     * @var BookRepository
     */
    private $bookRepository;

    /**
     * @var TranslationRepository
     */
    private $translationRepository;

    function __construct(BookRepository $bookRepository, TranslationRepository $translationRepository)
    {
        $this->bookRepository = $bookRepository;
        $this->translationRepository = $translationRepository;
    }

    public function getIndex() {
        return $this->getView($this->prepareForm());
    }

    public function postSearch() {
        $form = $this->prepareForm();
        $view = $this->getView($form);
        $view = $this->searchBookRef($form, $view);
        $view = $this->searchFullText($form, $view);
        return $view;
    }

    /**
     * @return SearchForm
     */
    private function prepareForm() {
        $form = new SearchForm();
        $form->textToSearch = Input::get('textToSearch');
        $form->grouping = Input::get('grouping');
        $defaultTranslation = Translation::getDefaultTranslation();
        $form->book = Input::has('book') ? Input::get('book') : 0;
        $form->translation = Input::has('translation') ? Translation::find(Input::get('translation')) : $defaultTranslation;
        return $form;
    }

    private function getView($form) {
        $translations = $this->translationRepository->getAll();
        $books = $this->bookRepository->getBooksByTranslation(Translation::getDefaultTranslation()->id);
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
        try {
            $storedBookRef = CanonicalReference::fromString($form->textToSearch)->getExistingBookRef();
            if ($storedBookRef) {
                $translatedRef = CanonicalReference::translateBookRef($storedBookRef, $form->translation->id);
                $textDisplayController = new TextDisplayController();
                $verseContainers = $textDisplayController->getTranslatedVerses(CanonicalReference::fromString($form->textToSearch), $form->translation);
                $augmentedView = $view->with('bookRef', [
                    'label' => $translatedRef->toString(),
                    'link' => "/{$form->translation->abbrev}/{$translatedRef->toString()}",
                    'verseContainers' => $verseContainers
                ]);
            }
        } catch (ParsingException $e) {
        }
        return $augmentedView;
    }

    /**
     * @param $form
     * @param $view
     * @return mixed
     */
    private function searchFullText($form, $view)
    {
        $fullTextResults = SphinxSearch::
        search($form->textToSearch, 'verse')
            ->limit(1000)
            ->filter('trans', $form->translation->id)
            ->setMatchMode(SphinxClient::SPH_MATCH_EXTENDED)
            ->setSortMode(SphinxClient::SPH_SORT_EXTENDED, "@weight DESC")
            ->get();
        if ($fullTextResults) {
            $view = $view->with('fullTextResults', $fullTextResults);
        }
        return $view;
    }

}
