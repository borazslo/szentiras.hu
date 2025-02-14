<?php

namespace SzentirasHu\Data\Entity;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $token
 * @property datetime $last_login
 */
class AnonymousId extends Model
{
    protected $fillable = ['token', 'last_login'];
}
