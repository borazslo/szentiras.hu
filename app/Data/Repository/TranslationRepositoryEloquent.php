<?php
/**

 */

namespace SzentirasHu\Data\Repository;

use SzentirasHu\Data\Entity\Translation;

class TranslationRepositoryEloquent implements TranslationRepository
{

    public function getAll()
    {
        $allTranslations = \Cache::remember(
            'getAllTranslations', 120, function () {
            return Translation::orderBy('order')->orderBy('name')->whereIn('id', \Config::get('settings.enabledTranslations'))->get();
        });
        return $allTranslations;
    }

    public function getByDenom($denom = false)
    {
        $q = $denom ? Translation::where('denom', $denom) : Translation::all();
        return $q->orderBy('denom')->orderBy('order')->orderBy('name')->whereIn('id', \Config::get('settings.enabledTranslations'))->get();
    }


    public function getAllOrderedByDenom()
    {
        $allTranslations = \Cache::remember(
            'translations_allOrderedByDenom', 120, function () {
            return Translation::orderBy('denom')->orderBy('order')->whereIn('id', \Config::get('settings.enabledTranslations'))->get();
        });
        return $allTranslations;

    }

    public function getBooks($translation)
    {
        return $translation->books()->orderBy('id')->get();
    }

    public function getByAbbrev($abbrev)
    {
        return Translation::where('abbrev', $abbrev)->first();
    }

    public function getById($id)
    {
        return \Cache::remember(
            "translations_getById_{$id}", 120, function() use ($id) {
            return Translation::find($id);
        });
    }

    public function getDefault()
    {
        return Translation::find(\Config::get('settings.defaultTranslationId'));
    }

}