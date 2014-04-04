<?php

namespace SzentirasHu\Models\Entities;
use Eloquent;

/**
 * Model for possible book abbreviations. They can represent bad abbreviations as well.
 *
 * @author berti
 */
class BookAbbrev extends Eloquent {

    public function books() {
        return $this->belongsTo('SzentirasHu\\Models\\Entities\\Book');
    }
    
}
