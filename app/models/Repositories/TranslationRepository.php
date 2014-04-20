<?php

namespace SzentirasHu\Models\Repositories;


interface TranslationRepository {

    public function getAll();

    public function getAllOrderedByDenom();

    public function getByDenom($denom = false);

    public function getBooks($translation);

    public function getByAbbrev($abbrev);

    public function getById($id);


} 