<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
            Schema::create('articles', function(Blueprint $table)
            {
                $table->increments('id');
                $table->timestamps();
                $table->boolean('frontpage');
                $table->text('text');
                $table->string('title', 100);
                $table->timestamp('publish_date');                
            });
            
            if (Schema::hasTable('news')) {
            $prefix= Config::get('database.connections.bible.prefix');
            DB::insert("
            INSERT INTO {$prefix}articles (`id`, `title`, `text`, `publish_date`, `frontpage`, `created_at`, `updated_at`)
            SELECT `id`, `title`, `text`, `date`, `frontpage`, now(), now() FROM {$prefix}news
            ");
        }

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('articles');
	}

}
