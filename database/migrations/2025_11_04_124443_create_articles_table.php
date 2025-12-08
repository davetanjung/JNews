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
        Schema::create('articles', function (Blueprint $table) {
           $table->string('id')->primary();
            
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->longText('content')->nullable();
            $table->longText('extended_content')->nullable();
            $table->string('url')->unique();
            $table->string('image')->nullable();
            
            $table->dateTime('publishedAt');
            
            $table->string('lang', 5)->nullable();
            
            $table->string('source_id');
            $table->foreign('source_id')
                  ->references('id')
                  ->on('sources')
                  ->onDelete('cascade');

            $table->string('category', 50)->nullable();
                  
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
