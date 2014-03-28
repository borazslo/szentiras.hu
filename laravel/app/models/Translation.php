<?php

/**
 * Domain object representing a translation.
 *
 * @author berti
 */
class Translation extends Eloquent {
    protected $table = 'tdtrans';
    
    public static function getByDenom($denom = false) {
        $q  = $denom ? Translation::where('denom', 'katolikus') : Translation::all();
        return $q->orderBy('denom')->orderBy('name')->get();
    }
    
}