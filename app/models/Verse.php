<?php

/**
 * Model for verses.
 *
 * @author berti
 */
class Verse extends Eloquent {
    
    protected $table = 'tdverse';
    
    public function book() {
        return $this->belongsTo('Book', 'book');
    }
    
    public function translation() {
        return $this->belongsTo('Translation','trans');
    }
    
    public function getGepiAttribute($value) {
        return $value;
    }
    
}
