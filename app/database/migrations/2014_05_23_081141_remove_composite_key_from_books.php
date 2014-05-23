<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RemoveCompositeKeyFromBooks extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropPrimary(['id', 'translation_id']);
            $table->renameColumn('id', 'number');
        });

        Schema::table('books', function (Blueprint $table) {
            $table->increments('id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->renameColumn('number', 'id');
        });
        Schema::table('books', function (Blueprint $table) {
            $table->primary(['id', 'translation_id']);
        });

    }

}