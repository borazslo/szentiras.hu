<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use SzentirasHu\Data\Entity\BookAbbrev;

class AddCselToBookAbbrevs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        DB::table('book_abbrevs')->insert(
            [
                'abbrev' => 'Acs',
                'books_id' => 205]
        );
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        DB::table('book_abbrevs')->where('abbrev', 'Acs')->delete();
	}

}
