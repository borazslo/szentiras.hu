<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBooksTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name', 100);
            $table->string('abbrev', 10);
            $table->string('denom', 20);
            $table->string('lang', 10);
            $table->longText('copyright');
            $table->string('publisher', 200);
            $table->string('publisher_url', 200);
            $table->string('reference', 255);

        });

        Schema::create('books', function (Blueprint $table) {
            $table->integer('id')->unsigned();
            $table->timestamps();
            $table->unsignedInteger('translation_id')->foreign('translation_id')->references('id')->on('translations');
            $table->primary(['id', 'translation_id']);
            $table->string('name', 100);
            $table->string('abbrev', 10);
            $table->string('link', 10);
            $table->boolean('old_testament');
        });
	}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('books');
        Schema::drop('translations');
    }

}
