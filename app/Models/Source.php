<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    use HasFactory;

    public $incrementing = false; // karena json nya dalam bentuk string, trs model ini tidak menggunakan auto-increment
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'name',
        'url',
        'country',
    ];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }
}
