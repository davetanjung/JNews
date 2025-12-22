<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Modify the image column to allow 1024 characters
            $table->string('image', 1024)->nullable()->change(); 
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Revert back to the original length (e.g., 255)
            $table->string('image', 255)->nullable()->change(); 
        });
    }
};