<?php

namespace SzentirasHu\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SzentirasHu\Models\MediaType
 * @property int $id
 * @property string $name
 * @property string|null $website
 * @property string|null $license
 * 
 */
class MediaType extends Model
{
    protected $fillable = ['name', 'website', 'license'];

    public function media()
    {
        return $this->hasMany(Media::class);
    }
}
