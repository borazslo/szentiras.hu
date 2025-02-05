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
        Schema::create("embedded_verses", function (Blueprint $table) {
            $table->id();
            $table->vector("embedding", \Config::get("settings.ai.embeddingDimensions"));
            $table->string("model");
            $table->text("content");
            $table->string("reference");
            $table->integer("translation_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("embedded_verses");
    }
};
