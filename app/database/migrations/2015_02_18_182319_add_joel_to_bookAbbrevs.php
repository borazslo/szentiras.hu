<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use SzentirasHu\Models\Entities\BookAbbrev;

class AddJoelToBookAbbrevs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		$abbrev = new BookAbbrev();
		$abbrev->abbrev = "Joel";
		$abbrev->books_id = 134;
		$abbrev->save();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        BookAbbrev::whereIn('abbrev','Joel')->delete();
	}

}
