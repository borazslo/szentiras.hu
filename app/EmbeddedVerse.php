<?php

namespace SzentirasHu;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;
use SzentirasHu\Data\Entity\Translation;
use SzentirasHu\Data\Entity\Verse;

/**
 * @property string model
 * @property string content
 * @property string reference
 * @property array embedding
 */
class EmbeddedVerse extends Model
{

    use HasNeighbors;

    protected $casts = ['embedding' => Vector::class];

    public function translation(): BelongsTo {
        return $this->belongsTo(Translation::class);
    }


}
