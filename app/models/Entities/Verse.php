<?php

namespace SzentirasHu\Models\Entities;
use Eloquent;

/**
 * Model for verses.
 *
 * @author berti
 */
class Verse extends Eloquent {
    
    protected $table = 'tdverse';
    public $timestamps = false;
    
    public function books() {
        return $this->belongsTo('SzentirasHu\Models\Entities\Book', 'book');
    }
    
    public function translation() {
        return $this->belongsTo('SzentirasHu\Models\Entities\Translation','trans');
    }

}
