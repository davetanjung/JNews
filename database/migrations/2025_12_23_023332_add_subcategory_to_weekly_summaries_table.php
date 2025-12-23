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
        Schema::table('weekly_summaries', function (Blueprint $table) {
            $table->string('subcategory')->nullable()->after('category');
            
            // Update unique constraint to include subcategory
            $table->dropUnique(['year', 'week_number', 'category']);
            $table->unique(['year', 'week_number', 'category', 'subcategory']);
            
            // Make summary_content nullable (for subcategory placeholders)
            $table->text('summary_content')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('weekly_summaries', function (Blueprint $table) {
            $table->dropUnique(['year', 'week_number', 'category', 'subcategory']);
            $table->unique(['year', 'week_number', 'category']);
            $table->dropColumn('subcategory');
        });
    }
};
