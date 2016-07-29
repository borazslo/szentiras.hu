<?php

namespace SzentirasHu\Data\Entity;
use Eloquent;

/**
 * Model for possible book abbreviations. They can represent bad abbreviations as well.
 *
 * @property books_id
 * @property string abbrev
 * @property int id
 * @author berti
 */
class BookAbbrev extends Eloquent {

    public $timestamps=false;

    public function books() {
        return $this->belongsTo('SzentirasHu\\Data\\Entity\\Book', 'books_id', 'number');
    }
    
}
