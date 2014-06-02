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
use SzentirasHu\Lib\Search\SearcherFactory;
use SzentirasHu\Lib\Search\SphinxSearcher;
use SzentirasHu\Lib\Text\TextService;
use SzentirasHu\Lib\VerseContainer;
use SzentirasHu\Models\Entities\Translation;
use SzentirasHu\Models\Entities\Verse;
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
     * @var \SzentirasHu\Lib\Search\SearcherFactory
     */
    private $searcherFactory;

    function __construct(BookRepository $bookRepository, TranslationRepository $translationRepository, VerseRepository $verseRepository, ReferenceService $referenceService, TextService $textService, SearcherFactory $searcherFactory)
    {
        $this->bookRepository = $bookRepository;
        $this->translationRepository = $translationRepository;
        $this->verseRepository = $verseRepository;
        $this->referenceService = $referenceService;
        $this->textService = $textService;
        $this->searcherFactory = $searcherFactory;
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
        $searchParams = new FullTextSearchParams;
        $searchParams->text = $term;
        $searchParams->limit = 10;
        $searchParams->groupByVerse = true;
        $searchParams->synonyms = true;
        $sphinxSearcher = $this->searcherFactory->createSearcherFor($searchParams);
        $sphinxResults = $sphinxSearcher->get();
        if ($sphinxResults) {
            $verses = $this->verseRepository->getVersesInOrder($sphinxResults->verseIds);
            $texts = [];
            foreach ($verses as $key => $verse) {
                $parsedVerse = $this->getParsedVerse($verse);
                if ($parsedVerse) {
                    $texts[$key] = $parsedVerse;
                }
            }
            $excerpts = $sphinxSearcher->getExcerpts($texts);
            $textKeys = array_keys($texts);
            if ($excerpts) {
                foreach ($excerpts as $i => $excerpt) {
                    $verse = $verses[$textKeys[$i]];
                    $linkLabel = "{$verse->book->abbrev} {$verse->chapter},{$verse->numv}";
                    $result[] = [
                        'cat' => 'verse',
                        'label' => $excerpt,
                        'link' => "/{$verse->translation->abbrev}/{$linkLabel}",
                        'linkLabel' => $linkLabel
                    ];
                }
            }
        }
        return Response::json($result);
    }

    private function getParsedVerse(Verse $verse)
    {
        $verseContainer = new VerseContainer($verse->book);
        $verseContainer->addVerse($verse);
        $parsedVerses = $verseContainer->getParsedVerses();
        if ($parsedVerses[0]->getHeadingText()) {
            return $parsedVerses[0]->getHeadingText();
        } else {
            return $parsedVerses[0]->text;
        }
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
        $translatedRef = $this->findTranslatedRef($form->textToSearch, $form->translation);
        if ($translatedRef) {
            $translation = $form->translation ? $form->translation : Translation::getDefaultTranslation();
            $verseContainers = $this->textService->getTranslatedVerses(CanonicalReference::fromString($form->textToSearch), $translation);
            $augmentedView = $view->with('bookRef', [
                'label' => $translatedRef->toString(),
                'link' => "/{$translation->abbrev}/{$translatedRef->toString()}",
                'verseContainers' => $verseContainers
            ]);
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
        $sphinxSearcher = $this->searcherFactory->createSearcherFor($searchParams);
        $sphinxResults = $sphinxSearcher->get();
        if ($sphinxResults) {
            $view = $this->handleFullTextResults($form, $view, $sphinxResults);
        }
        return $view;
    }

    /**
     * @param SearchForm $form
     * @param $view
     * @param FullTextSearchResult $sphinxResults
     * @return mixed
     */
    private function handleFullTextResults($form, $view, $sphinxResults)
    {
        $sortedVerses = $this->verseRepository->getVersesInOrder($sphinxResults->verseIds);
        $verseContainers = $this->groupVersesByBook($sortedVerses, $form->translation);
        $results = [];
        $chapterCount = 0;
        $verseCount = 0;
        foreach ($verseContainers as $verseContainer) {
            $result = [];
            $result['book'] = $verseContainer->book;
            $result['translation'] = $this->translationRepository->getById($verseContainer->book->translation_id);
            $parsedVerses = $verseContainer->getParsedVerses();
            $result['chapters'] = [];
            foreach ($parsedVerses as $verse) {
                $verseData = [];
                $verseData['chapter'] = $verse->chapter;
                $verseData['numv'] = $verse->numv;
                $verseData['text'] = '';
                if ($verse->headings) {
                    foreach ($verse->headings as $heading) {
                        $verseData['text'] .= $heading . ' ';
                    }
                }
                if ($verse->text) {
                    $verseData['text'] .= preg_replace('/<[^>]*>/', ' ', $verse->text);
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
            'hitCount' => $form->grouping == 'chapter' ? $chapterCount : $verseCount,
        ]);
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
     * @param $sortedVerses
     * @return VerseContainer[]
     */
    private function groupVersesByBook($sortedVerses, $translation)
    {
        $verseContainers = [];
        foreach ($sortedVerses as $verse) {
            $book = $verse->book;
            $key = !$translation ?
                $book->translation_id . '/' . $book->abbrev :
                $book->abbrev;
            if (!array_key_exists($key, $verseContainers)) {
                $verseContainers[$key] = new VerseContainer($book);
            }
            $verseContainer = $verseContainers[$key];
            $verseContainer->addVerse($verse);
        }
        return $verseContainers;
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
            $searcher = $this->searcherFactory->createSearcherFor($params);
            $searchHits = $searcher->get();
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
    private function findTranslatedRef($refToSearch, $translation = false)
    {
        try {
            $ref = CanonicalReference::fromString($refToSearch);
            $storedBookRef = $this->referenceService->getExistingBookRef($ref);
            if ($storedBookRef) {
                $translation = $translation ? $translation : Translation::getDefaultTranslation();
                return $this->referenceService->translateBookRef($storedBookRef, $translation->id);
            }
        } catch (ParsingException $e) {
        }
    }

}
