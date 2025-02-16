<?php

namespace SzentirasHu\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = ['filename', 'mime_type', 'media_type_id', 'usx_code', 'chapter', 'verse', 'uuid'];

    public function mediaType()
    {
        return $this->belongsTo('SzentirasHu\Models\MediaType');
    }
}
