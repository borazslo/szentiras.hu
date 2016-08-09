<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use SzentirasHu\Data\Entity\BookAbbrev;

class AddTitToAbbrevs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $abbrev = new BookAbbrev();
        $abbrev->abbrev = "Tít";
        $abbrev->books_id = 217;
        $abbrev->save();
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        BookAbbrev::whereIn('abbrev', [ 'Tit' ])->delete();
	}

}
