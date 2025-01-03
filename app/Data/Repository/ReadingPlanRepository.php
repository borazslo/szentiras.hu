<?php

namespace SzentirasHu\Data\Repository;


use SzentirasHu\Data\Entity\ReadingPlan;
use SzentirasHu\Data\Entity\ReadingPlanDay;

interface ReadingPlanRepository {
    /**
     * List all the reading plans
     *
     * @return ReadingPlan[]
     */
    public function getAll();

    /**
     * List the days in a reading plan
     *
     * @param int $id
     * @return ReadingPlanDay[]
     */
    public function getDaysByPlanId($id);

}
