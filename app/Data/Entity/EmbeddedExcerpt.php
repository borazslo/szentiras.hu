<?php

namespace SzentirasHu\Data\Entity;

use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

/**
 * @property string model
 * @property string reference
 * @property Vector embedding
 * @property integer chapter
 * @property integer verse
 * @property integer to_chapter
 * @property integer to_verse
 * @property integer gepi
 * @property string ucs_code
 * @property string translation_abbrev
 * @property EmbeddedExcerptScope scope
 */
class EmbeddedExcerpt extends Model
{

    use HasNeighbors;

    protected $casts = [
        'embedding' => Vector::class,
        'scope' => EmbeddedExcerptScope::class
    ];

    public $timestamps = false;

}
