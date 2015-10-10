<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class KNBBooksCapitalization extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        DB::table('books')->where('id', 19)->update(array('name' => 'Második Törvénykönyv'));
		DB::table('books')->where('id', 87)->update(array('name' => 'A Prédikátor könyve'));
		DB::table('books')->where('id', 96)->update(array('name' => 'Jézus, Sirák fiának könyve	'));
		DB::table('books')->where('id', 168)->update(array('name' => 'A Makkabeusok első könyve'));
		DB::table('books')->where('id', 170)->update(array('name' => 'A Makkabeusok második könyve'));
		DB::table('books')->where('id', 229)->update(array('name' => 'Első levél Timóteusnak'));
		DB::table('books')->where('id', 233)->update(array('name' => 'Második levél Timóteusnak'));
		DB::table('books')->where('id', 237)->update(array('name' => 'Levél Títusznak'));
		DB::table('books')->where('id', 241)->update(array('name' => 'Levél Filemonnak'));		
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{ 
        DB::table('books')->where('id', 19)->update(array('name' => 'Második törvénykönyv'));
		DB::table('books')->where('id', 87)->update(array('name' => 'A prédikátor könyve'));
		DB::table('books')->where('id', 96)->update(array('name' => 'Jézus, sirák fiának könyve	'));
		DB::table('books')->where('id', 168)->update(array('name' => 'A makkabeusok első könyve'));
		DB::table('books')->where('id', 170)->update(array('name' => 'A makkabeusok második könyve'));
		DB::table('books')->where('id', 229)->update(array('name' => 'Első levél timóteusnak'));
		DB::table('books')->where('id', 233)->update(array('name' => 'Második levél timóteusnak'));
		DB::table('books')->where('id', 237)->update(array('name' => 'Levél títusznak'));
		DB::table('books')->where('id', 241)->update(array('name' => 'Levél filemonnak'));		
	}

}