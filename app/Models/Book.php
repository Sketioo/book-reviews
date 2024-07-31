<?php

namespace App\Models;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// use Illuminate\Database\Query\Builder;

class Book extends Model
{
    use HasFactory;

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    //* Local Scope Query
    public function scopeTitle(Builder $query, string $title): Builder
    {
        return $query->where('title', 'like', '%' . $title . '%');
    }

    public function scopeWithReviewsCount(Builder $query, $from = null, $to = null): Builder
    {
        return $query->withCount(['reviews' => function (Builder $q) use ($from, $to) {
            return $this->dateRangeFilter($q, $from, $to);
        }]);
    }

    public function scopeWithAvgRating(Builder $query, $from = null, $to = null): Builder
    {
        return $query->withAvg(['reviews' => function (Builder $q) use ($from, $to) {
            return $this->dateRangeFilter($q, $from, $to);
        }], 'rating');
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->withReviewsCount()
            ->orderBy('reviews_count', 'desc');
    }

    public function scopeHighestRated(Builder $query)
    {
        return $query->withAvgRating()
            ->orderBy('reviews_avg_rating', 'desc');
    }

    public function scopeMinReviews(Builder $query, int $minReviews)
    {
        return $query->having('reviews_count', '>', $minReviews);
    }

    private function dateRangeFilter(Builder $query, $from = null, $to = null): Builder
{
    if ($from && !$to) {
        $query->where('created_at', '>=', $from);
    } elseif (!$from && $to) {
        $query->where('created_at', '<=', $to);
    } elseif ($from && $to) {
        $query->whereBetween('created_at', [$from, $to]);
    }

    return $query;
}


    public function scopePopularLastMonth(Builder $query)
    {
        return $query->popular(now()->subMonth(), now())
            ->highestRated()->minReviews(5);
    }

    public function scopePopularLast6Months(Builder $query)
    {
        return $query->popular(now()->subMonths(6), now())
            ->highestRated()->minReviews(5);
    }

    public function scopeHighestRatedLastMonth(Builder $query)
    {
        return $query->highestRated(now()->subMonth(), now())
            ->popular(now()->subMonth(), now())->minReviews(3);
    }

    public function scopeHighestRated6Months(Builder $query)
    {
        return $query->highestRated(now()->subMonths(6))
            ->popular(now()->subMonths(6))->minReviews(3);
    }
}
