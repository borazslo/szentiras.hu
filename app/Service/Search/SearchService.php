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
        $searchParams->grouping = 'verse';
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
            return $parsedVerses[0]->getText();
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
        //echo "<pre>".print_r($sphinxResults ,1)."</pre>";
        
        
        $defaultTranslation = $this->translationRepository->getDefault();
                
        /* beginning of new part */                
        $results = [];
        $translations = [];
        foreach($sphinxResults->verses as $id => $verse) {
            switch ($params->grouping) {
                case 'book':
                    $limit = 3;
                    break;
                case 'chapter':
                    $limit = 6;
                    break;
                case 'verse':
                    $limit = 9;
                    break;                
                default:
                    $limit = 9;
                    break;
            }
            $key = substr($verse['attrs']['gepi'],0,$limit);
            if(!array_key_exists($key, $results)) {
                $results[$key] = ['weights' => [], 'translations' => [$defaultTranslation->abbrev => [] ] ];                
            }
            if(!array_key_exists($verse['attrs']['trans'], $translations)) {
                $translations[$verse['attrs']['trans']] = $this->translationRepository->getById($verse['attrs']['trans']); 
            }          
            $trans = $translations[$verse['attrs']['trans']];
            if(!array_key_exists($trans['abbrev'], $results[$key]['translations']) or $results[$key]['translations'][$trans['abbrev']] == array() ) {
                //$book = $this->bookRepository->getByNumberForTranslation($verse['attrs']['book_number'],$verse['attrs']['trans']);
                $results[$key]['translations'][$trans['abbrev']] = [                       
                       'verseIds' => [],
                       'verses' => [],
                       'trans' => $trans,
                       'book' => $sortedVerses[$id]->book
                    ];                
            }
            //echo "<pre>"; print_r($sortedVerses[$id]); exit;
            $results[$key]['weights'][] = $verse['weight'];
            $results[$key]['translations'][$trans['abbrev']]['verseIds'][] = $id;
            $results[$key]['translations'][$trans['abbrev']]['verses'][] = $sortedVerses[$id];
        }

        // echo "<pre> ".print_r($results,1)."</pre>";/**/
        
        foreach($results as $key => $result) {
            rsort($result['weights']);
            //echo "<pre>"; print_R($result['weights']); echo "</pre>";
             $results[$key]['weight'] = reset($result['weights']) + sqrt(array_sum($result['weights'])); // log10()

             foreach($result['translations'] as $abbrev => $group ) {
                 if($group == []) unset($results[$key]['translations'][$abbrev]);
                 else {
                    $gepis = array_column($group['verses'],'gepi');
                    array_multisort($gepis, SORT_ASC, $results[$key]['translations'][$abbrev]['verses']);
                 
                $currentNumv = false;
                $currentChapter = false;
                foreach ($group['verses'] as $k => $verse) {
                    $verseData = [];
                    $verseData['chapter'] = $verse->chapter;
                    $verseData['numv'] = $verse->numv;
                    $verseData['text'] = preg_replace('/<[^>]*>/', ' ', $verse->verse);
                    if ($verse->headings) { // Ez nem üzemel, mert nem volt getParsedVerse mert nem volt VerseContainer, mert book-onként kall azt csináni.
                        echo "bizony";
                        foreach ($verse->headings as $heading) {
                            $verseData['text'] .= "<small>".$heading . '</small> ';
                        }
                    }
                    
                    if($verse->chapter > $currentChapter ) {                        
                        $verseData['chapterStart'] = true;
                        $currentNumv = $verse->numv;
                    }
                    $currentChapter = $verse->chapter;
                    
                    if($verse->numv > $currentNumv + 1 AND $currentNumv = $verse->numv) {
                        $verseData['ellipseBefore'] = true;
                    } 
                    $currentNumv = $verse->numv;
                    
                    $results[$key]['translations'][$abbrev]['verses'][$k] = $verseData;
                    
                }
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                 }
             }
        }
        $weights  = array_column($results, 'weight');
        array_multisort($weights, SORT_DESC, $results);
        
        //echo "<pre> ".print_r($results,1)."</pre>";/**/
                
        /* end of new part */                
        
        $resultsByBookNumber = $results;
        
        
        /* original */

        $verseContainers = $this->groupVersesByBook($sortedVerses, $params->translationId);
        //echo "<pre>".print_r($verseContainers,1)."</pre>";
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
                        $verseData['text'] .= "<small>".$heading . '</small> ';
                    }
                }
                if ($verse->getText()) {
                    $verseData['text'] .= preg_replace('/<[^>]*>/', ' ', $verse->getText());
                }
                $result['chapters'][$verse->chapter][] = $verseData;
                $result['verses'][] = $verseData;
                $verseCount++;
            }
            $chapterCount += count($result['chapters']);
            if ($params->grouping == 'chapter') { 
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
        //echo "<pre>".print_r($results,1)."</pre>";
        if($params->grouping == 'verse') $hitCount = $verseCount;
        else $hitCount = $chapterCount;
        return ['resultsByBookNumber' => $resultsByBookNumber,'results' => $results, 'hitCount' => $hitCount ];
    }

    private function groupVersesByBookNumber($sortedVerses, $groupByVerse = false)
    {
        $verseContainers = [];
        foreach ($sortedVerses as $verse) {
            $book = $verse->book;
            if($groupByVerse OR 4==4) $key = $verse->gepi;
            else $key = $book->number."_".$verse->chapter;
            
            if (!array_key_exists($key, $verseContainers)) {
                $verseContainers[$key] = [];
            }
            if (!array_key_exists($book->translation_id, $verseContainers[$key])) {
                $verseContainers[$key][$book->translation_id] = [
                    'translation' => $verse->translation,
                    'book' => $book,
                    'chapter' => $verse->chapter,
                    'numv' => $verse->numv,                    
                    'verses' => new VerseContainer($book)
                ];
            }
                     
            $book->translation_id . '/' . $book->abbrev ;
            
            $verseContainer = $verseContainers[$key][$book->translation_id]['verses'];
            $verseContainer->addVerse($verse);
        }
        return $verseContainers;
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
    public function findTranslatedRefs($refToSearch, $translation = null)
    {
        try {
            $ref = CanonicalReference::fromString($refToSearch);
            $storedBookRefs = $this->referenceService->getExistingBookRefs($ref);
            $translatedBookRefs = [];
            foreach ($storedBookRefs as $storedBookRef) {
                $bookRef = $this->referenceService->translateBookRef($storedBookRef, $translation === null ? null : $translation->id);
                if ($bookRef !== null) {
                    $translatedBookRefs[] = $bookRef;
                }
            }
            return $translatedBookRefs;
        } catch (ParsingException $e) {
        }
    }


}