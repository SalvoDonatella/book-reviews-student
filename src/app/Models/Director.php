<?php

namespace App\Models;

use Database\Factories\DirectorsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Director extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'age',
    ];

    public function films(): BelongsToMany
    {
        return $this->belongsToMany(Film::class);
    }
}
