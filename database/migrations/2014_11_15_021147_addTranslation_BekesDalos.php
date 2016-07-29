<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTranslationBekesDalos extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('translations')->insert(
		    array(
        		'id'=>5,
		    	'created_at' => date('Y-m-d H:i:s'),
		    	'updated_at' => date('Y-m-d H:i:s'),
		    	'name' => 'Békés-Dalos Újszövetségi Szentírás', 
		    	'abbrev' => 'BD',
		    	'denom' => 'katolikus',
		    	'lang' => 'magyar',
		    	'copyright' => 'A <a href="http://www.benceskiado.hu/">Bencés Kiadó</a> engedélyével. 2014. február.',
		    	'publisher' => 'Bencés Kiadó',
		    	'publisher_url' => 'http://www.benceskiado.hu/'
		    	)
		);

		$migrationsPath = base_path('database/migrations');
        $file = fopen("{$migrationsPath}/2014_11_15_021147_addTranslation_BekesDalos.csv", "r");
        while ($data= fgetcsv($file)) {
            DB::table('books')->insert(
            	[
            		'number' => $data[0],
        			'created_at' => date('Y-m-d H:i:s'),
		    		'updated_at' => date('Y-m-d H:i:s'),
		    		'translation_id' => 5,
		    		'name' => $data[3],
		    		'abbrev' => $data[1],
		    		'link' => $data[2],
		    		'old_testament'=>0,
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
		DB::table('books')->where('translation_id', 5)->delete();
        DB::table('translations')->where('abbrev', 'BD')->delete();
	}

}
