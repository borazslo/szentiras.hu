<?php

namespace SzentirasHu\Http\Controllers\Api;

use SzentirasHu\Http\Controllers\Controller;
use SzentirasHu\Service\Reference\ParsingException;
use SzentirasHu\Service\Search\FullTextSearchParams;
use SzentirasHu\Service\Search\SearcherFactory;
use SzentirasHu\Service\Search\SearchService;
use URL;
use Input;
use Response;
use SzentirasHu\Http\Controllers\Home\LectureSelector;
use SzentirasHu\Service\Reference\CanonicalReference;
use SzentirasHu\Service\Reference\ReferenceService;
use SzentirasHu\Service\Text\TextService;
use SzentirasHu\Data\Entity\Translation;
use SzentirasHu\Data\Entity\Verse;
use SzentirasHu\Data\Repository\BookRepository;
use SzentirasHu\Data\Repository\TranslationRepository;
use View;

class ApiController extends Controller
{


    /**
     * @var \SzentirasHu\Service\Text\TextService
     */
    private $textService;
    /**
     * @var \SzentirasHu\Http\Controllers\Home\LectureSelector
     */
    private $lectureSelector;
    /**
     * @var \SzentirasHu\Data\Repository\TranslationRepository
     */
    private $translationRepository;
    /**
     * @var \SzentirasHu\Data\Repository\BookRepository
     */
    private $bookRepository;
    /**
     * @var \SzentirasHu\Service\Reference\ReferenceService
     */
    private $referenceService;
    /**
     * @var \SzentirasHu\Service\Search\SearcherFactory
     */
    private $searcherFactory;
    /**
     * @var SearchService
     */
    private $searchService;

    function __construct(
        TextService $textService,
        LectureSelector $lectureSelector,
        TranslationRepository $translationRepository,
        BookRepository $bookRepository,
        ReferenceService $referenceService,
        SearchService $searchService)
    {
        $this->textService = $textService;
        $this->lectureSelector = $lectureSelector;
        $this->translationRepository = $translationRepository;
        $this->bookRepository = $bookRepository;
        $this->referenceService = $referenceService;
        $this->searchService = $searchService;
        $this->middleware('cors');
    }

    public function getIndex()
    {
        return View::make("api.api");
    }

    public function getIdezet($refString, $translationAbbrev = false)
    {
        if ($translationAbbrev) {
            $translation = $this->translationRepository->getByAbbrev($translationAbbrev);
        } else {
            $translation = $this->translationRepository->getDefault();
        }
        $canonicalRef = CanonicalReference::fromString($refString);
        $verseContainers = $this->textService->getTranslatedVerses(CanonicalReference::fromString($refString), $translation->id);
        $verses = [];
        foreach ($verseContainers as $verseContainer) {
            foreach ($verseContainer->getParsedVerses() as $verse) {
                $jsonVerse["szoveg"] = $verse->text;
                $jsonVerse["hely"] = ["gepi" => $verse->gepi];
                $jsonVerse["hely"]["szep"] = $verse->book->abbrev . " " . $verse->chapter . ',' . $verse->numv;
                $verses[] = $jsonVerse;
            }
        }

        return $this->formatJsonResponse([
            "keres" => ["feladat" => "idezet", "hivatkozas" => $canonicalRef->toString(), "forma" => "json"],
            "valasz" => [
                "versek" => $verses,
                "forditas" => [
                    "nev" => $translation->name,
                    "rov" => $translation->abbrev
                ]]
        ]);
    }

    public function getForditasok($gepi)
    {
        $verses = Verse::where('gepi', $gepi)->get();
        $verseDataList = [];
        foreach ($verses as $verse) {
            $translation = $verse->translation()->first();
            if (in_array($verse->tip, \Config::get("translations.{$translation->abbrev}.verseTypes.text"))) {
                $verseData['hely']['gepi'] = $verse->gepi;
                $book = $verse->book;
                $verseData['hely']['szep'] = "{$book->abbrev} {$verse->chapter},{$verse->numv}";
                $verseData['szoveg'] = $verse->verse;
                $verseDataList[] = $verseData;
                $verseData['forditas']['nev'] = $translation->name;
                $verseData['forditas']['szov'] = $translation->abbrev;
            }
        }
        return $this->formatJsonResponse([
            'keres' => ["feladat" => "forditasok", "hivatkozas" => $gepi, "forma" => "json"],
            "valasz" => [
                "versek" => $verseDataList
            ]
        ])->setCallback(Input::get('callback'));
    }

    public function getLectures()
    {
        $lectureReferences = $this->lectureSelector->getLectures();
        $formattedLectures = [];
        foreach ($lectureReferences as $lectureReference) {
            $text = $this->textService->getPureText($lectureReference->ref, $lectureReference->translationId);
            $formattedLecture = ['text' => $text, 'ref' => $lectureReference->ref];
            $formattedLectures[] = $formattedLecture;
        }
        return $this->formatJsonResponse(['lectures' => $formattedLectures]);
    }

    public function getBooks($translationAbbrev = false) {
        $translation = $this->findTranslation($translationAbbrev);
        foreach ($this->bookRepository->getBooksByTranslation($translation->id) as $book) {
            $bookData[] = [
                'abbrev' => $book->abbrev,
                'name' => $book->name,
                'number' => $book->number
            ];
        }
        $data = [
            'translation' => ['abbrev' => $translation->abbrev, 'id'=>$translation->id],
            'books' => $bookData
        ];
        return $this->formatJsonResponse($data);
    }

    public function getRef($ref, $translationAbbrev = false)
    {
        $results = $this->searchRef($ref, $translationAbbrev);
        if (empty($results)) {
            \App::abort(404, "Nincs ilyen hivatkozás");
        } else {
            return $this->formatJsonResponse(count($results)<=1 ? $results[0] : $results);
        }


    }

    public function getSearch($text)
    {
        $params = new FullTextSearchParams();
        $params->text = $text;
        $results = $this->searchService->getDetailedResults($params);
        $refResult = $this->searchRef($text, false);
        return $this->formatJsonResponse(["refResult" => $refResult, "fullTextResult" => $results]);
    }

    private function formatJsonResponse($data)
    {
        $flags = \Config::get('app.debug') ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE : 0;
        return Response::json($data, 200, [
            'Content-Type' => 'application/json; charset=UTF-8'
        ], $flags)->setCallback(Input::get('callback'));
    }

    /**
     * @param $translationAbbrev
     * @return mixed
     */
    private function findTranslation($translationAbbrev = false)
    {
        if ($translationAbbrev) {
            $translation = $this->translationRepository->getByAbbrev($translationAbbrev);
            return $translation;
        } else {
            $translation = $this->translationRepository->getDefault();
            return $translation;
        }
    }

    /**
     * @param $ref
     * @param $translationAbbrev
     * @return array
     */
    private function searchRef($ref, $translationAbbrev)
    {
        if ($translationAbbrev == "*") {
            $translations = $this->translationRepository->getAllOrderedByDenom();
        } else {
            $translations = [$this->findTranslation($translationAbbrev)];
        }
        $results = [];
        foreach ($translations as $translation) {
            try {
                $canonicalRef = $this->referenceService->translateReference(CanonicalReference::fromString($ref), $translation->id);
                $text = $this->textService->getPureText($canonicalRef, $translation->id);
                $result = [];
                if (!empty($text)) {
                    $result['canonicalRef'] = $canonicalRef->toString();
                    $result['canonicalUrl'] = URL::to($this->referenceService->getCanonicalUrl($canonicalRef, $translation->id));
                    $result['text'] = $this->textService->getPureText($canonicalRef, $translation->id);
                    $result['translationAbbrev'] = $translation->abbrev;
                    $result['translationName'] = $translation->name;
                    $results[] = $result;
                }
            } catch (ParsingException $parsingException) {

            }
        }
        return $results;
    }
}