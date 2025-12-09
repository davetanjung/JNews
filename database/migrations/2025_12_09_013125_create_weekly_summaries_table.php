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
        Schema::create('weekly_summaries', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('week_number');
            $table->string('category')->nullable(); //nullable for all categories
            $table->longText('summary_content');
            $table->timestamps();

            // unique biar ga ada duplicate summary for the same week and category
            $table->unique(['year', 'week_number', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_summaries');
    }
};
