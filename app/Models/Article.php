<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
     use HasFactory;

    public $incrementing = false; // karena json nya dalam bentuk string, trs model ini tidak menggunakan auto-increment
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'description',
        'content',
        'image',
        'publishedAt',
        'lang',
        'source_id',
        'category'
    ];

    protected $casts = [
        'publishedAt' => 'datetime',
    ];

     public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
