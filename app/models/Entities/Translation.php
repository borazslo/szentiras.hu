<?php

namespace SzentirasHu\Models\Entities;
use Eloquent;

/**
 * Domain object representing a translation.
 *
 * @author berti
 */
class Translation extends Eloquent {

    public static function getByDenom($denom = false) {
        $q  = $denom ? Translation::where('denom', $denom) : Translation::all();
        return $q->orderBy('denom')->orderBy('name')->get();
    }

    public static function byAbbrev($translationAbbrev)
    {
        return self::where('abbrev', $translationAbbrev)->first();
    }

    public static function getDefaultTranslation() {
        return self::find(\Config::get('settings.defaultTranslationId'));
    }

    public function books() {
        return $this->hasMany('SzentirasHu\Models\Entities\Book');
    }
}