<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameBookColumnOnTdverse extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('tdverse', function(Blueprint $table)
		{
			$table->renameColumn('book', 'book_number');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('tdverse', function(Blueprint $table)
		{
            $table->renameColumn('book_number', 'book');
		});
	}

}
