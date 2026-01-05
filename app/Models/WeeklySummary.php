<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklySummary extends Model
{
    protected $fillable = ['year', 'week_number', 'category', 'summary_content', 'article_ids' => 'array'];
}