<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use SzentirasHu\Data\Entity\BookAbbrev;

class AddTerToBookAbbrevs extends Migration {

	public function up()
	{
        $abbrev = new BookAbbrev();
        $abbrev->books_id = 101;
        $abbrev->abbrev = "Ter";
        $abbrev->save();
	}

	public function down()
	{
        BookAbbrev::where('abbrev', 'Ter')->delete();
	}

}