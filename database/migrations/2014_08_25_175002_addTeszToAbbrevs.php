<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use SzentirasHu\Data\Entity\BookAbbrev;

class AddTeszToAbbrevs extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        $abbrev = new BookAbbrev();
        $abbrev->abbrev = "1Tesz";
        $abbrev->books_id = 213;
        $abbrev->save();
        $abbrev = new BookAbbrev();
        $abbrev->abbrev = "2Tesz";
        $abbrev->books_id = 214;
        $abbrev->save();
    }

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        BookAbbrev::whereIn('abbrev', [ '1Tesz', '2Tesz'])->delete();
	}

}
