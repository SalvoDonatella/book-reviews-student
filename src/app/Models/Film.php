<?php

namespace App\Models;

use Database\Factories\FilmsFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Film extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'release_year',
        'genre',
    ];

    public function directors(): BelongsToMany
    {
        return $this->belongsToMany(Director::class);
    }

    public function review(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term) {
            $query->where('title', 'like', '%'.$term.'%')
                ->orWhereHas('directors', fn (Builder $query) => $query->where('name', 'like', '%'.$term.'%'));

            if (is_numeric($term)) {
                $query->orWhere('year', $term);
            }
        });
    }
}
