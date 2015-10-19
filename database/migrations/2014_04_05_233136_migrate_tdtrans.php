<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateTdtrans extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tdtrans')) {
            $prefix= Config::get('database.connections.bible.prefix');
            DB::insert("
            INSERT INTO {$prefix}translations (`id`, `name`, `abbrev`, `denom`, `lang`, `copyright`, `publisher`, `publisher_url`, `reference`, `created_at`, `updated_at`)
            SELECT `id`, `name`, `abbrev`, `denom`, `lang`, `copyright`, `publisher`, `publisherurl`, `reference`, now(), now() FROM {$prefix}tdtrans
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
        if (Schema::hasTable('tdtrans')) {
            \SzentirasHu\Data\Entity\Translation::truncate();
        }
    }

}
