<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameBooks extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('books')->where('id', '=', '237')->update(array('abbrev' => 'Tít','link'=>'Tit'));
        DB::table('books')->where('id', '=', '273')->update(array('abbrev' => 'Júd','link'=>'Jud'));
        DB::table('books')->where('id', '=', '219')->update(array('abbrev' => '1Tesz','link'=>'1Tesz'));
        DB::table('books')->where('id', '=', '223')->update(array('abbrev' => '2Tesz','link'=>'2Tesz'));
        DB::table('books')->where('id', '=', '92')->update(array('abbrev' => 'Ének.Én','link'=>'EnekEn'));
        DB::table('books')->where('id', '=', '272')->update(array('abbrev' => 'Júd','link'=>'Jud'));
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('books')->where('id', '=', '237')->update(array('abbrev' => 'Tit','link'=>'Tit'));
        DB::table('books')->where('id', '=', '273')->update(array('abbrev' => 'Júdás','link'=>'Judas'));
        DB::table('books')->where('id', '=', '219')->update(array('abbrev' => '1Tessz','link'=>'1Tessz'));
        DB::table('books')->where('id', '=', '223')->update(array('abbrev' => '2Tessz','link'=>'2Tessz'));
        DB::table('books')->where('id', '=', '92')->update(array('abbrev' => 'ÉnekÉn','link'=>'EnekEn'));
        DB::table('books')->where('id', '=', '272')->update(array('abbrev' => 'Jud','link'=>'Jud'));
	}

}
