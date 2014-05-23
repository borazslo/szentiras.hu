<?php

namespace SzentirasHu\Models\Entities;
use Eloquent;

/**
 * Model for verses.
 *
 * @property string verse
 * @property int tip
 * @property int chapter
 * @property int numv
 * @author berti
 */
class Verse extends Eloquent {

    public $timestamps = false;
    protected $table = 'tdverse';

    private static $typeMap;

    public function book() {
        return $this->belongsTo('SzentirasHu\Models\Entities\Book');
    }

    public function books() {
        return $this->belongsTo('SzentirasHu\Models\Entities\Book', 'book_number');
    }

    public function translation() {
        return $this->belongsTo('SzentirasHu\Models\Entities\Translation','trans');
    }

    public function getType() {
        if (!self::$typeMap) {
            foreach (\Config::get('verseTypes') as $typeDefs) {
                foreach($typeDefs as $typeName => $typeIds) {
                    foreach ($typeIds as $key => $typeId) {
                        if ($typeName == 'heading') {
                            $t = $typeName.$key;
                        } else {
                            $t = $typeName;
                        }
                        self::$typeMap[$typeId] = $t;
                    }
                }
            }
        }
        if (array_key_exists($this->tip, self::$typeMap)) {
            return self::$typeMap[$this->tip];
        } else {
            return 'unknown';
        }

    }

}
