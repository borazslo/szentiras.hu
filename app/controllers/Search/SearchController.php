<?php

namespace SzentirasHu\Controllers\Search;
use App;
use BaseController;
use Input;
use Sphinx\SphinxClient;
use SphinxSearch;
use SzentirasHu\Controllers\Display\TextDisplayController;
use SzentirasHu\Lib\Reference\CanonicalReference;
use SzentirasHu\Lib\Reference\ParsingException;
use SzentirasHu\Lib\VerseContainer;
use SzentirasHu\Models\Entities\Book;
use SzentirasHu\Models\Entities\Translation;
use SzentirasHu\Models\Repositories\BookRepository;
use SzentirasHu\Models\Repositories\TranslationRepository;
use SzentirasHu\Models\Repositories\VerseRepository;
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
    /**
     * @var \SzentirasHu\Models\Repositories\VerseRepository
     */
    private $verseRepository;

    function __construct(BookRepository $bookRepository, TranslationRepository $translationRepository, VerseRepository $verseRepository)
    {
        $this->bookRepository = $bookRepository;
        $this->translationRepository = $translationRepository;
        $this->verseRepository = $verseRepository;
    }

    public function getIndex() {
        return $this->getView($this->prepareForm());
    }

    public function postSearch() {
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
    private function prepareForm() {
        $form = new SearchForm();
        $form->textToSearch = Input::get('textToSearch');
        $form->grouping = Input::get('grouping');
        $defaultTranslation = Translation::getDefaultTranslation();
        $form->book = Input::get('book');
        $form->translation = Input::has('translation') ? $this->translationRepository->getById(Input::get('translation')) : $defaultTranslation;
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
                $translation = $form->translation ? $form->translation : Translation::getDefaultTranslation();
                $translatedRef = CanonicalReference::translateBookRef($storedBookRef, $translation->id);
                $textDisplayController = App::make('SzentirasHu\Controllers\Display\TextDisplayController');
                $verseContainers = $textDisplayController->getTranslatedVerses(CanonicalReference::fromString($form->textToSearch), $translation);
                $augmentedView = $view->with('bookRef', [
                    'label' => $translatedRef->toString(),
                    'link' => "/{$translation->abbrev}/{$translatedRef->toString()}",
                    'verseContainers' => $verseContainers
                ]);
            }
        } catch (ParsingException $e) {
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
        $fullTextResults = SphinxSearch::
        search($form->textToSearch)
            ->setMatchMode(SphinxClient::SPH_MATCH_EXTENDED)
            ->setSortMode(SphinxClient::SPH_SORT_EXTENDED, "@relevance DESC, gepi ASC");
        if ($form->translation) {
            $fullTextResults = $fullTextResults->filter('trans', $form->translation->id);
        }
        $fullTextResults = $fullTextResults->get();
        if ($fullTextResults) {
            $sortedVerses = $this->verseRepository->getVersesInOrder(array_keys($fullTextResults['matches']));
            $verseContainers = [];
            foreach ($sortedVerses as $verse) {
                $book = $this->bookRepository->getByIdForTranslation($verse->book, $verse->trans);
                if (!array_key_exists($book->abbrev, $verseContainers)) {
                    $verseContainers[$book->abbrev] = new VerseContainer($book);
                }
                $verseContainer = $verseContainers[$book->abbrev];
                $verseContainer->addVerse($verse);
            }
            $results=[];
            $chapterCount = 0;
            $verseCount = 0;
            foreach ($verseContainers as $bookAbbrev => $verseContainer) {
                $result = [];
                $result['book'] = $verseContainer->book;
                $result['translation'] = $this->translationRepository->getById($verseContainer->book->translation_id);
                $parsedVerses = $verseContainer->getParsedVerses();
                $result['chapters'] = [];
                foreach ($parsedVerses as $verse) {
                    $verseData['chapter'] = $verse->chapter;
                    $verseData['numv'] = $verse->numv;
                    if ($verse->text) {
                        $verseData['text'] = preg_replace('/<[^>]*>/',' ',$verse->text);
                    } else {
                        foreach ($verse->headings as $heading) {
                            $verseData['text'] = $heading;
                        }
                    }
                    $result['chapters'][$verse->chapter][] = $verseData;
                    $result['verses'][] = $verseData;
                    $verseCount++;
                }
                $chapterCount += count($result['chapters']);
                if (array_key_exists('verses', $result)) {
                    $results[] = $result;
                }
            }
            $view = $view->with('fullTextResults', [
                'results' => $results,
                'hitCount' => $form->grouping == 'chapter' ? $chapterCount : $verseCount]);
        }
        return $view;
    }

}
