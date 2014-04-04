<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BookAbbrevsChangeBookId extends Migration
{

    private $from = 'bookId';
    private $to = 'books_id';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('book_abbrevs', function ($table) {
            $table->renameColumn($this->from, $this->to);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('book_abbrevs', function ($table) {

            $table->renameColumn($this->to, $this->from);
        });
	}

}
