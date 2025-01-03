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

	public function getDaysByPlanId($id) {
		return Cache::remember("reading_plan_{$id}_days", 60, function () use ($id) {
			return ReadingPlanDay::where('plan_id', $id)->orderBy('day_number')->get();
		});
	}
}