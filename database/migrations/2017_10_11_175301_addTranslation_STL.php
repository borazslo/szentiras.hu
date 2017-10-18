<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranslationSTL extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

		DB::table('translations')->insert(
		    array(
        		'id'=>7,
		    	'created_at' => date('Y-m-d H:i:s'),
		    	'updated_at' => date('Y-m-d H:i:s'),
		    	'name' => 'Simon Tamás László Újszövetség-fordítása',
		    	'abbrev' => 'STL',
		    	'order' => 4,
		    	'denom' => 'katolikus',
		    	'lang' => 'magyar',
		    	'copyright' => 'A Bencés Kiadó engedélyével (2017)',
		    	'publisher' => 'Bencés Kiadó',
		    	'publisher_url' => 'http://benceskiado.hu'
		    	)
		);

		$migrationsPath = base_path('database/migrations');
        $file = fopen("{$migrationsPath}/2017_10_11_175301_addTranslation_STL.csv", "r");
        while ($data= fgetcsv($file)) {
            DB::table('books')->insert(
            	[
            		'number' => $data[0],
        			'created_at' => date('Y-m-d H:i:s'),
		    		'updated_at' => date('Y-m-d H:i:s'),
		    		'translation_id' => 7,
		    		'name' => $data[3],
		    		'abbrev' => $data[1],
		    		'link' => $data[2],
		    		'old_testament'=>$data[4],
            		]
            );
        }
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::table('tdverse')->where('trans', 7)->delete();
		DB::table('books')->where('translation_id', 7)->delete();
        DB::table('translations')->where('abbrev', 'STL')->delete();
	}
}
