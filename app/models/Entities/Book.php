<?php

namespace SzentirasHu\Models\Entities;
use Eloquent;

/**
 * Description of Book
 *
 * @property string abbrev
 * @property int id
 * @property  int number
 * @author berti
 */
class Book extends Eloquent {

    public function abbrevs() {
        return $this->hasMany('SzentirasHu\Models\Entities\BookAbbrev', 'number');
    }

    public function verses() {
        return $this->hasMany('SzentirasHu\Models\Entities\Verse', 'book_id');
    }

    public function translation() {
        return $this->belongsTo('SzentirasHu\Models\Entities\Translation');
    }

}
