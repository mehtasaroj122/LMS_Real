<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class);
    }

    public function bookCopies(): HasMany
    {
        return $this->copies();
    }

    public function scopeWithCirculationAvailability(Builder $query): Builder
    {
        return $query->withCount([
            'copies as circulation_total_copies',
            'copies as circulation_available_copies' => fn ($copies) => $copies->availableForIssue(),
        ]);
    }

    public function scopeAvailableForBorrowing(Builder $query): Builder
    {
        return $query->whereNotIn('books.status', ['inactive', 'withdrawn'])
            ->where(fn ($books) => $books
                ->whereHas('copies', fn ($copies) => $copies->availableForIssue())
                ->orWhere(fn ($legacy) => $legacy->whereDoesntHave('copies')->where('books.available_copies', '>', 0)));
    }

    /** Requires withCirculationAvailability so legacy titles sort by their stored inventory. */
    public function scopeOrderByCirculationAvailability(Builder $query): Builder
    {
        return $query->orderByRaw("CASE WHEN books.status IN ('inactive', 'withdrawn') THEN 0
            WHEN circulation_total_copies = 0 THEN books.available_copies
            ELSE circulation_available_copies END DESC");
    }

    public function availableForBorrowingCount(): int
    {
        if (in_array($this->getRawOriginal('status'), ['inactive', 'withdrawn'], true)) {
            return 0;
        }

        if ($this->getAttribute('circulation_total_copies') === null) {
            $this->loadCount([
                'copies as circulation_total_copies',
                'copies as circulation_available_copies' => fn ($copies) => $copies->availableForIssue(),
            ]);
        }

        return (int) $this->getAttribute('circulation_total_copies') > 0
            ? (int) $this->getAttribute('circulation_available_copies')
            : max(0, (int) $this->available_copies);
    }

    public function requests()
    {
        return $this->hasMany(BookRequest::class);
    }
}
