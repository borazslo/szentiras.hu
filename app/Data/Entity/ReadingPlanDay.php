<?php

namespace SzentirasHu\Data\Entity;
use Eloquent;

/**
 * A day of a reading plan.
 *
 * @author Gabor Hosszu
 */
class ReadingPlanDay extends Eloquent {

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['plan_id', 'day_number', 'description', 'verses'];

    public function plan() {
        return $this->belongsTo('SzentirasHu\\Data\\Entity\\ReadingPlan', 'plan_id');
    }

}
