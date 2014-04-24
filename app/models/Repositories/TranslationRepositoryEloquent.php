<?php
/**

 */

namespace SzentirasHu\Models\Repositories;

use SzentirasHu\Models\Entities\Translation;

class TranslationRepositoryEloquent implements TranslationRepository {

    public function getAll()
    {
        return Translation::remember(120)->orderBy('name')->get();
    }

    public function getByDenom($denom = false) {
        $q  = $denom ? Translation::where('denom', $denom) : Translation::all();
        return $q->orderBy('denom')->orderBy('name')->remember(120)->get();
    }


    public function getAllOrderedByDenom()
    {
        return Translation::orderBy('denom')->get();
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
}