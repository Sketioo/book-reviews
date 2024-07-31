<?php

namespace App\Models;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    protected static function booted()
    {
        static::updated(fn(Book $book) => cache()->forget("book:{$book->id}"));
        static::deleted(fn(Review $review) => cache()->forget("book:{$review->book_id}"));
    }
}
