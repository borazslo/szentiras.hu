<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('book_abbrevs')->insert(
            [
                'abbrev' => 'Sirák',
                'books_id' => 126
                ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('book_abbrevs')->where('abbrev', 'Sirák')->delete();
    }
};
