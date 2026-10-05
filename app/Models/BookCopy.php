<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookCopy extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'accession_number',
        'entry_date',
        'book_type',
        'status',
        'shelf_location',
        'condition',
        'price',
        'remarks',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function issuedBooks(): HasMany
    {
        return $this->hasMany(IssuedBook::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function isBorrowable(): bool
    {
        return strtolower((string) $this->book_type) !== 'reference';
    }
}
