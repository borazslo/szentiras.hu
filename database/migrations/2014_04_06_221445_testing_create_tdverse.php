<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TestingCreateTdverse extends Migration {

	/**
	 * Creates a simple legacy tdverse table for integration testing.
	 *
	 * @return void
	 */
	public function up()
	{
        if (App::environment()==='testing') {
            Schema::create('tdverse', function (Blueprint $table) {
                $table->unsignedInteger('did');
                $table->unsignedInteger('trans');
                $table->bigInteger('gepi');
                $table->integer('book');
                $table->integer('chapter');
                $table->string('numv', 4);
                $table->string('hiv', 15);
                $table->integer('old');
                $table->integer('tip');
                $table->string('jelenseg', 50);
                $table->longText('verse');
                $table->longText('versesimple');
                $table->longText('verseroot');
                $table->string('ido', 50);
                $table->index(['did', 'trans'], 'index_did_trans');
            });
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        if (App::environment()==='testing') {
            Schema::drop('tdverse');
        }
	}

}
