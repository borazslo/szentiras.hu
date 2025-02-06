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
            $table->string("reference");
            $table->integer("chapter")->nullable();
            $table->integer("verse")->nullable();
            $table->integer("translation_id");
            $table->foreign("translation_id")->references("id")->on("translations");
            $table->bigInteger("gepi")->nullable();
            $table->integer("book_id");
            $table->foreign("book_id")->references("id")->on("books");
            $table->string("scope")->default("verse");
            $table->index(["reference", "translation_id"]);
            $table->index("reference");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("embedded_excerpts");
    }
};
