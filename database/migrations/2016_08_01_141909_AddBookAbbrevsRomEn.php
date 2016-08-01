<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBookAbbrevsRomEn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('book_abbrevs')->insert([
            'abbrev'=>'Rom',
            'books_id'=>'206'
        ]);
        DB::table('book_abbrevs')->insert([
            'abbrev'=>'En',
            'books_id'=>'124'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('book_abbrevs')->whereIn('abbrev', ['Rom', 'En'])->delete();
    }
}
