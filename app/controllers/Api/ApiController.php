<?php

namespace SzentirasHu\Controllers\Api;

use BaseController;
use Illuminate\Support\Facades\Config;
use Input;
use Response;
use SzentirasHu\Controllers\Home\LectureSelector;
use SzentirasHu\Lib\Reference\CanonicalReference;
use SzentirasHu\Lib\Text\TextService;
use SzentirasHu\Models\Entities\Translation;
use SzentirasHu\Models\Entities\Verse;
use View;

class ApiController extends BaseController
{


    /**
     * @var \SzentirasHu\Lib\Text\TextService
     */
    private $textService;
    /**
     * @var \SzentirasHu\Controllers\Home\LectureSelector
     */
    private $lectureSelector;

    function __construct(TextService $textService, LectureSelector $lectureSelector)
    {
        $this->textService = $textService;
        $this->lectureSelector = $lectureSelector;
    }

    public function getIndex()
    {
        return View::make("api.api");
    }

    public function getIdezet($refString, $translationAbbrev = false)
    {
        if ($translationAbbrev) {
            $translation = Translation::byAbbrev($translationAbbrev);
        } else {
            $translation = Translation::getDefaultTranslation();
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
            if (in_array($verse->tip, \Config::get("verseTypes.{$translation->abbrev}.text"))) {
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

    private function formatJsonResponse($data)
    {
        $flags = Config::get('app.debug') ? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE : 0;
        return Response::json($data, 200, [
            'Content-Type' => 'application/json; charset=UTF-8'
        ], $flags)->setCallback(Input::get('callback'));
    }
}