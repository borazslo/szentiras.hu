<?php
/**

 */

namespace SzentirasHu\Data\Repository;


use Cache;
use SzentirasHu\Data\Entity\ReadingPlan;
use SzentirasHu\Data\Entity\ReadingPlanDay;

class ReadingPlanRepositoryEloquent implements ReadingPlanRepository {
	public function getAll() {
		return Cache::remember('reading_plans', 60, function () {
			return ReadingPlan::orderBy('name')->get();
		});
	}

	public function getDaysByPlan(ReadingPlan $plan) {
		return $plan->days()->orderBy('day_number')->get();
	}
}