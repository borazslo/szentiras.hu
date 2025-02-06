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
        Schema::create("embedded_excerpts", function (Blueprint $table) {
            $table->id();
            $table->vector("embedding", \Config::get("settings.ai.embeddingDimensions"));
            $table->string("model");
            $table->text("content");
            $table->string("reference");
            $table->integer("translation_id");
            $table->bigInteger("gepi")->nullable();
            $table->integer("book_id");
            $table->string("scope")->default("verse");
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
