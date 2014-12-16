<?php
/**

 */

namespace SzentirasHu\Models\Repositories;

use SzentirasHu\Models\Entities\Translation;

class TranslationRepositoryEloquent implements TranslationRepository
{

    public function getAll()
    {
        $allTranslations = Translation::remember(120)->orderBy('order')->orderBy('name');
        return $allTranslations->whereIn('id', \Config::get('settings.enabledTranslations'))->get();
    }

    public function getByDenom($denom = false)
    {
        $q = $denom ? Translation::where('denom', $denom) : Translation::all();
        return $q->orderBy('denom')->orderBy('order')->orderBy('name')->whereIn('id', \Config::get('settings.enabledTranslations'))->remember(120)->get();
    }


    public function getAllOrderedByDenom()
    {
        return Translation::orderBy('denom')->orderBy('order')->whereIn('id', \Config::get('settings.enabledTranslations'))->get();
    }

    public function getBooks($translation)
    {
        return $translation->books()->orderBy('id')->remember(120)->get();
    }

    public function getByAbbrev($abbrev)
    {
        return Translation::where('abbrev', $abbrev)->remember(120)->first();
    }

    public function getById($id)
    {
        return Translation::remember(120)->find($id);
    }

    public function getDefault()
    {
        return Translation::remember(120)->find(\Config::get('settings.defaultTranslationId'));
    }

}