<?php

namespace SzentirasHu\Data\Entity;
use Eloquent;

/**
 * Reading plan.
 *
 * @author Gabor Hosszu
 */
class ReadingPlan extends Eloquent {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'description'];

    public function days() {
        return $this->hasMany('SzentirasHu\\Data\\Entity\\ReadingPlanDay', 'plan_id');
    }

}
