<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSirToKG extends Migration {

	public function up()
	{
        DB::table('book_abbrevs')
            ->where('abbrev', 'Sir')
            ->where('books_id', 129)
            ->update(['translation_id' => 4]);
    }

	public function down()
	{
        DB::table('book_abbrevs')
            ->where('abbrev', 'Sir')
            ->where('books_id', 129)
            ->update(['translation_id' => null]);
	}

}
