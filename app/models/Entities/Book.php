<?php

namespace SzentirasHu\Models\Entities;
use Eloquent;

/**
 * Description of Book
 *
 * @author berti
 */
class Book extends Eloquent {

    public function abbrevs() {
        return $this->hasMany('BookAbbrev', 'id');
    }

    public function verses() {
        return $this->hasMany('Verse', 'book');
    }

    public function translation() {
        return $this->belongsTo('SzentirasHu\Models\Entities\Translation');
    }

}
