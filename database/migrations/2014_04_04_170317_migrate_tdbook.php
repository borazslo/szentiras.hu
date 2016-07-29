<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrateTdbook extends Migration
{

    public function up()
    {

        // don't do this on testing database
        if (Schema::hasTable('tdbook')) {
            $prefix= Config::get('database.connections.bible.prefix');
            DB::insert("
            INSERT INTO {$prefix}books (`id`, `translation_id`, `name`, `abbrev`, `link`, `old_testament`, `created_at`, `updated_at`)
            SELECT `id`, `trans`, `name`, `abbrev`, `url`, `oldtest`, now(), now() FROM {$prefix}tdbook
            ");
        } else {
            Log::info("tdbook table doesn't exist, so no need to migrate");

        }
    }

    public function down()
    {
        // not feasible to roll back, only to delete all rows
        if (Schema::hasTable('tdbook')) {
            \SzentirasHu\Data\Entity\Book::truncate();
        }
    }

}
