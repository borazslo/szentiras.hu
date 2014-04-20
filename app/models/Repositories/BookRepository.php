<?php

namespace SzentirasHu\Models\Repositories;


interface BookRepository {

    public function getBooksByTranslation($translationId);

} 