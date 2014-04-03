<?php

namespace SzentirasHu\Models\Entities;
use Eloquent;

/**
 * Model for possible book abbreviations. They can represent bad abbreviations as well.
 *
 * @author berti
 */
class BookAbbrev extends Eloquent {
    
    protected $table = "tdbook_hibas";

    public function book() {
        return $this->belongsTo('Book','id');
    }
    
}
