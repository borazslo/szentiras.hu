<?php

namespace SzentirasHu\Models\Entities;
use Eloquent;

/**
 * Description of Book
 *
 * @property string abbrev
 * @property int id
 * @author berti
 */
class Book extends Eloquent {

    public function abbrevs() {
        return $this->hasMany('SzentirasHu\Models\Entities\BookAbbrev', 'id');
    }

    public function verses() {
        return $this->hasMany('SzentirasHu\Models\Entities\Verse', 'book');
    }

    public function translation() {
        return $this->belongsTo('SzentirasHu\Models\Entities\Translation');
    }

}