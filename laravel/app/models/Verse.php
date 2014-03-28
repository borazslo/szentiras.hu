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
    
}
