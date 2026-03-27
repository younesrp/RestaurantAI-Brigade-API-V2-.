<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function plates(): BelongsToMany
    {
        return $this->belongsToMany(Plate::class);
    }
}
