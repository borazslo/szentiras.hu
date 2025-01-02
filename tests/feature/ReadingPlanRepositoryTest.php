<?php

namespace SzentirasHu\Test;
use App;
use SzentirasHu\Test\Common\TestCase;


/**

 */

class ReadingPlanRepositoryTest extends TestCase {

    public function testReadingPlans() {
        $repo = App::make(\SzentirasHu\Data\Repository\ReadingPlanRepositoryEloquent::class);
        $plans = $repo->getAll();

        $this->assertEquals(1, $plans->count());
        $daysOfFirstPlan = $repo->getDaysByPlan($plans->first());
        $this->assertEquals(365, $daysOfFirstPlan->count());
    }

}
