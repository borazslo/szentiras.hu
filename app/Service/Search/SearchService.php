<?php
/**

 */

namespace SzentirasHu\Service\Search;

use SzentirasHu\Service\Reference\CanonicalReference;
use SzentirasHu\Service\Reference\ParsingException;
use SzentirasHu\Service\Reference\ReferenceService;
use SzentirasHu\Service\VerseContainer;
use SzentirasHu\Data\Entity\Verse;
use SzentirasHu\Data\Repository\TranslationRepository;
use SzentirasHu\Data\Repository\VerseRepository;

class SearchService {


    /**
     * @var SearcherFactory
     */
    private $searcherFactory;
    /**
     * @var \SzentirasHu\Data\Repository\VerseRepository
     */
    private $verseRepository;
    /**
     * @var \SzentirasHu\Data\Repository\TranslationRepository
     */
    private $translationRepository;
    /**
     * @var \SzentirasHu\Service\Reference\ReferenceService
     */
    private $referenceService;

    function __construct(SearcherFactory $searcherFactory, VerseRepository $verseRepository, TranslationRepository $translationRepository, ReferenceService $referenceService)
    {
        $this->searcherFactory = $searcherFactory;
        $this->verseRepository = $verseRepository;
        $this->translationRepository = $translationRepository;
        $this->referenceService = $referenceService;
    }

    function getSuggestionsFor($term)
    {
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
            return $result;
        }
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

    public function getDetailedResults($searchParams)
    {
        $searcher = $this->searcherFactory->createSearcherFor($searchParams);
        $results = $searcher->get();
        if ($results) {
            return $this->handleFullTextResults($results, $searchParams);
        } else {
            return null;
        }
    }

    private function handleFullTextResults($sphinxResults, FullTextSearchParams $params)
    {
        $sortedVerses = $this->verseRepository->getVersesInOrder($sphinxResults->verseIds);
        $verseContainers = $this->groupVersesByBook($sortedVerses, $params->translationId);
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
            if (!$params->groupByVerse) {
                foreach ($result['chapters'] as $chapterNumber => $verses) {
                    usort($verses, function ($verseData1, $verseData2) {
                        if ($verseData1['numv'] == $verseData2['numv']) {
                            return 0;
                        }
                        return ($verseData1['numv'] < $verseData2['numv']) ? -1 : 1;
                    });
                    $currentNumv = 1;
                    $result['chapters'][$chapterNumber] = $verses;
                    foreach ($verses as $key => $verse) {
                        if ($verse['numv'] > $currentNumv) {
                            $result['chapters'][$chapterNumber][$key]['ellipseBefore'] = true;
                        }
                    }
                }
            }
            if (array_key_exists('verses', $result)) {
                $results[] = $result;
            }
        }
        return ['results' => $results, 'hitCount' => $params->groupByVerse ?  $verseCount : $chapterCount];
    }

    private function groupVersesByBook($sortedVerses, $translationId)
    {
        $verseContainers = [];
        foreach ($sortedVerses as $verse) {
            $book = $verse->book;
            $key = !$translationId ?
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

    public function getSimpleResults($params)
    {
        $searcher = $this->searcherFactory->createSearcherFor($params);
        return $searcher->get();
    }

    /**
     * @param $refToSearch
     * @param $translation
     */
    public function findTranslatedRef($refToSearch, $translation = null)
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