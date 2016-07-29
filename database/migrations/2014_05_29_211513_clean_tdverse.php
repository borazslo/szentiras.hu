<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CleanTdverse extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        Schema::table('tdverse', function(Blueprint $table)
        {
            $table->dropUnique('index_did_trans');
            $table->dropColumn('did');
            $table->dropColumn('hiv');
            $table->dropColumn('old');
            $table->dropColumn('jelenseg');
            $table->dropColumn('versesimple');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tdverse', function(Blueprint $table)
        {
            $table->string('versesimple');
            $table->string('jelenseg');
            $table->integer('old');
            $table->string('hiv',15);
            $table->integer('did');  
        });
        // Sajnos csak tök kama adatokkal tölti fel. 
        $verses = DB::table('tdverse')->get(); 
        foreach($verses as $verse) {
            DB::table('tdverse')->where('id', '=', $verse->id)
                ->update(array('versesimple' => $verse->verse,'jelenseg'=>"jelenség: ".$verse->tip,'old'=>0,'hiv'=>"",'did'=>$verse->id));        
        }
        
        Schema::table('tdverse', function(Blueprint $table)
        {     
            $table->unique(array('did','trans'),'index_did_trans');
        }); 
    }

}
