<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'author',
        'publisher',
        'isbn',
        'total_copies',
        'available_copies',
        'condition',
        'description',
        'cover_image',
        'shelf_no',
        'status',
    ];

    /**
     * Get the book status based on available copies
     * Auto-calculates: if available_copies > 0 then "available", else "unavailable"
     */
    public function getStatusAttribute($value)
    {
        // Override with calculated status based on available copies
        return $this->available_copies > 0 ? 'available' : 'unavailable';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function issuedBooks()
    {
        return $this->hasMany(IssuedBook::class);
    }

    public function requests()
    {
        return $this->hasMany(BookRequest::class);
    }
}
