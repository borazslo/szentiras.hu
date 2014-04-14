<?php

namespace SzentirasHu\Controllers\Api;

use BaseController;
use Input;
use Redirect;
use Response;
use SzentirasHu\Controllers\Display\TextDisplayController;
use SzentirasHu\Lib\Reference\CanonicalReference;
use SzentirasHu\Models\Entities\Translation;
use View;

class ApiController extends BaseController {

	public function getIndex()
	{
        return View::make("api.index");
	}

	public function getIdezet($refString, $translationAbbrev=false) {
        if ($translationAbbrev) {
            $translation = Translation::byAbbrev($translationAbbrev);
        } else {
            $translation = Translation::getDefaultTranslation();
        }
        $textDisplayController = new TextDisplayController();
        $canonicalRef = CanonicalReference::fromString($refString);
        $verseContainers = $textDisplayController->getTranslatedVerses(CanonicalReference::fromString($refString), $translation);
        $verses = [];
        foreach ($verseContainers as $verseContainer) {
            foreach ($verseContainer->getParsedVerses() as $verse) {
                $verses[] = $verse->text;
            }
        }

        return Response::json([
            "keres" => [ "feladat" => "idezet", "hivatkozas" => $canonicalRef->toString(), "forma" => "json"],
            "valasz" => [ "versek" => $verses]
            ]
        );
    }

    public function getForditasok($gepi) {

    }
}