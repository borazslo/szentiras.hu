<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReadingPlanTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reading_plans', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name');
            $table->string('description');
		});
        Schema::create('reading_plan_days', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('plan_id');
            $table->integer('day_number');
            $table->string('description');
            $table->string('verses');

            $table->unique(['plan_id', 'day_number']);
		});

        $this->addInitialData();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reading_plans');
        Schema::dropIfExists('reading_plan_days');
    }

    private function addInitialData() {
        $this->addBibleInAYearReadingPlan();
        $this->addBibleInAYearReadingPlanData();
    }

    private function addBibleInAYearReadingPlan() {
        DB::table('reading_plans')->insert(
        	[
        		'id' => 1,
        		'name' => 'AscensionPress 365 napos terv',
        		'description' => 'A Szentírás elolvasása 365 nap alatt.',
        		]
        );
    }

    private function addBibleInAYearReadingPlanData() {
		$migrationsPath = base_path('database/migrations');
        $file = fopen("{$migrationsPath}/2024_12_29_175659_create_reading_plan_tables.csv", "r");
        while ($data = fgetcsv($file)) {
            DB::table('reading_plan_days')->insert(
            	[
            		'plan_id' => 1,
            		'day_number' => $data[0],
            		'description' => $data[1],
            		'verses' => $data[2],
            		]
            );
        }
    }
}
