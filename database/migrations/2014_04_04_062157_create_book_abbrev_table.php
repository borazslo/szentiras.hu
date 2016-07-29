<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookAbbrevTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Log::info("Create book abbreviation table...");
		Schema::create('book_abbrev', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string("abbrev");
            $table->unsignedInteger("bookId");
        });
        Log::info("Insert book abbreviation data...");
        $migrationsPath = base_path('database/migrations');
        $file = fopen("{$migrationsPath}/2014_04_04_062157_book_abbrevs.csv", "r");
        while ($data= fgetcsv($file)) {
            DB::table('book_abbrev')->insert(['bookId'=>$data[0], 'abbrev'=>$data[1]]);
        }
        Log::info("done");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('book_abbrev');
	}

}
