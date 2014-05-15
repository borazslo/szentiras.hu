<?php

namespace SzentirasHu\Models\Entities;
use Eloquent;

/**
 * Domain object representing a translation.
 *
 * @property mixed id
 * @property string name
 * @property string abbrev
 * @author berti
 */
class Translation extends Eloquent {

    public static function byAbbrev($translationAbbrev)
    {
        return self::where('abbrev', $translationAbbrev)->first();
    }

    /**
     * @return Translation
     */
    public static function getDefaultTranslation() {
        return self::find(\Config::get('settings.defaultTranslationId'));
    }

    public function books() {
        return $this->hasMany('SzentirasHu\Models\Entities\Book');
    }
}