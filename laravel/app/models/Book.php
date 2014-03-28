<?php

/**
 * Description of Book
 *
 * @author berti
 */
class Book extends Eloquent {
    
    protected $table = 'tdbook';
    
    public function abbrevs() {
        return $this->hasMany('BookAbbrev', 'id');
    }
    
    public function verses() {
        return $this->hasMany('Verse', 'book');
    }

}
