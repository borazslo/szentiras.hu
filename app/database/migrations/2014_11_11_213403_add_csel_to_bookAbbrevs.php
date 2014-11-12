<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use SzentirasHu\Models\Entities\BookAbbrev;

class AddCselToBookAbbrevs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
 		$abbrev = new BookAbbrev();
        $abbrev->abbrev = "Acs";
        $abbrev->books_id = 205;
        $abbrev->save();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        BookAbbrev::whereIn('abbrev','Acs')->delete();
	}

}
