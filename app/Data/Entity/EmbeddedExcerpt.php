<?php

namespace SzentirasHu\Data\Entity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;
use SzentirasHu\Data\Entity\Book;
use SzentirasHu\Data\Entity\Translation;

/**
 * @property string model
 * @property string reference
 * @property array embedding
 * @property integer chapter
 * @property integer verse
 * @property integer to_chapter
 * @property integer to_verse
 * @property integer gepi
 * @property EmbeddedExcerptScope scope
 */
class EmbeddedExcerpt extends Model
{

    use HasNeighbors;

    protected $casts = [
        'embedding' => Vector::class,
        'scope' => EmbeddedExcerptScope::class
    ];

    public function translation(): BelongsTo {
        return $this->belongsTo(Translation::class);
    }

    public function book(): BelongsTo {
        return $this->belongsTo(Book::class);
    }

    public $timestamps = false;

}
