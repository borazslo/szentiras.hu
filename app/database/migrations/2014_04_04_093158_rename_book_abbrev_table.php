<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameBookAbbrevTable extends Migration {

    private $from = 'book_abbrev';
    private $to = 'book_abbrevs';
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::rename($this->from, $this->to);
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::rename($this->to, $this->from);
	}

}
